<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Csv;
use Magento\Store\Model\StoreManager;
use Bluethinkinc\ImportWishlist\Block\Index;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Wishlist\Model\ResourceModel\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Magento\Framework\Filesystem\Io\File;
use Bluethinkinc\ImportWishlist\Model\ConfigInterface;

class Csvsave extends \Magento\Backend\App\Action
{
    private const TEMPLATE_CODE = "product_added_to_your_wishlist";
    /**
     * @var Index
     */
    protected $block;
    /**
     * Page
     *
     * @var Pages
     */
    protected $_pageFactory;
    /**
     * @var WishlistFactory
     */
    protected $_wishlistFactory;
    /**
     * @var Wishlist
     */
    protected $_wishlistResource;
    /**
     * @var Product
     */
    protected $_productRepository;
    /**
     * @var CustomerFactory
     */
    protected $_customerFactory;
    /**
     * @var StoreManager
     */
    protected $_storeManager;
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    protected $varDirectory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var int
     */
    protected $storeID;

    /**
     * @var Ratings
     */
    protected $ratings;

    /**
     * @var File
     */
    protected $file;
    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $configInterface;

    /**
     * @param Index $block
     * @param CollectionFactory $collectionFactory
     * @param CustomerFactory $customerFactory
     * @param Product $productRepository
     * @param WishlistFactory $wishlistFactory
     * @param Wishlist $wishlistResource
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param UploaderFactory $uploaderFactory
     * @param Filesystem $filesystem
     * @param Csv $csvProcessor
     * @param StoreManager $storeManager
     * @param File $file
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        Index $block,
        CollectionFactory $collectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\Product $productRepository,
        \Magento\Wishlist\Model\WishlistFactory $wishlistFactory,
        \Magento\Wishlist\Model\ResourceModel\Wishlist $wishlistResource,
        Context $context,
        PageFactory $resultPageFactory,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        Csv $csvProcessor,
        StoreManager $storeManager,
        File $file,
        ConfigInterface $configInterface
    ) {
        parent::__construct($context);
        $this->collectionFactory = $collectionFactory;
        $this->block = $block;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_wishlistResource = $wishlistResource;
        $this->_productRepository = $productRepository;
        $this->resultPageFactory = $resultPageFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->varDirectory = $filesystem->getDirectoryWrite(
            DirectoryList::VAR_DIR
        );
        $this->csvProcessor = $csvProcessor;
        $this->storeID = $storeManager->getStore()->getId();
        $this->file = $file;
        $this->configInterface = $configInterface;
    }

    /**
     * Return execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(
            ResultFactory::TYPE_REDIRECT
        );
        $resultRedirect->setPath("csvdata/index/importcsv");

        try {
            $uploader = $this->uploaderFactory->create([
                "fileId" => "upload_file",
            ]);
            $uploader->checkAllowedExtension("csv");
            $uploader->skipDbProcessing(true);
            $result = $uploader->save($this->getWorkingDir());

            $this->validateIfHasExtension($result);
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            return $resultRedirect;
        }

        $this->processUpload($result);
        return $resultRedirect;
    }

    /**
     * Validate IfHasExtension
     *
     * @param var $result
     * @return void
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function validateIfHasExtension($result)
    {
        $extension = $this->file->getPathInfo(
            $result["file"],
            PATHINFO_EXTENSION
        );

        $uploadedFile = $result["path"] . $result["file"];
        if (!$extension) {
            $this->varDirectory->delete($uploadedFile);
            throw new \FileSystemException(
                __("The file you uploaded has no extension.")
            );
        }
    }

    /**
     * Get WorkingDir
     *
     * @return string
     */
    public function getWorkingDir()
    {
        return $this->varDirectory->getAbsolutePath("importexportwishlist/");
    }

    /**
     * Process Upload
     *
     * @param var $result
     * @return void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Zend_Validate_Exception
     */
    public function processUpload($result)
    {
        $sendMailEnable = $this->configInterface->isEnabled();
        $templateData = $this->collectionFactory
            ->create()
            ->addFieldToSelect("template_id")
            ->addFieldToFilter(
                "template_code",
                self::TEMPLATE_CODE
            )
            ->load();
        $templateId = $templateData->getData();
        foreach ($templateId as $template) {
            $emailTemplate = $template;
            foreach ($emailTemplate as $emailiddata) {
                $emailiddata;
            }
        }
        $validSku = [];
        $validCustomer = [];
        $invalidCustomer = [];
        $invalidSku = [];
        $sourceFile = $this->getWorkingDir() . $result["file"];

        $rows = $this->csvProcessor->getData($sourceFile);
        $header = array_shift($rows);

        foreach ($rows as $row) {
            $data = [];
            foreach ($row as $key => $value) {
                $data[$header[$key]] = $value;
            }
            $skuList = $data["sku"];
            $email = $data["email"];
            $explodeskuList = explode(",", $skuList);
            $explodemailList = explode(",", $email);
            foreach ($explodemailList as $emailId) {
                $customerId = $this->getCustomerIdByEmail($emailId);
                if (!empty($customerId)) {
                    $validCustomer["email"] = $emailId;
                    $allSku = $this->getCustomerWishListItem($customerId);
                    $skus = $this->getSkus(
                        $explodeskuList,
                        $allSku,
                        $customerId
                    );
                    $skuArr = json_decode($skus, true);
                    if (!empty($sendMailEnable) && !empty($skuArr["validSku"])) {
                        $this->block->sendEmail(
                            $validCustomer,
                            $emailiddata,
                            $skuArr["validSku"]
                        );
                    }
                } else {
                    $invalidCustomer[] = $emailId;
                }
            }
        }
        if (!empty($invalidCustomer)) {
            $this->messageManager->addErrorMessage(
                __(
                    "Customer Email " .
                        implode(",", $invalidCustomer) .
                        " Doesn't Exists"
                )
            );
        }
        if (!empty($skuArr["invalidSku"])) {
            $uniqueSku = array_unique($skuArr["invalidSku"]);
            $this->messageManager->addErrorMessage(
                __("SKU " . implode(",", $uniqueSku) . " Doesn't Exists")
            );
        }
        if (!empty($validCustomer) && $skuArr["validSku"]) {
            $this->messageManager->addSuccessMessage(__("Import Successfully"));
        }
        return $this->_redirect("csvdata/index/import");
    }

    /**
     * Get CustomerId By Email
     *
     * @param var $emailId
     * @return mixed
     */
    public function getCustomerIdByEmail($emailId)
    {
        $websiteId = (int) $this->_storeManager->getStore()->getWebsiteId();
        $customer = $this->_customerFactory
            ->create()
            ->setWebsiteId($websiteId)
            ->loadByEmail($emailId);
        return $customer->getId();
    }

    /**
     * Get Customer WishList Item
     *
     * @param var $customerId
     * @return array
     */
    public function getCustomerWishListItem($customerId)
    {
        $wishlist_collection = $this->_wishlistFactory
            ->create()
            ->loadByCustomerId($customerId, true)
            ->getItemCollection();
        $skuAll = [];
        foreach ($wishlist_collection as $item) {
            $skuAll[] = $item->getProduct()->getSku();
        }
        return $skuAll;
    }

    /**
     * Check Product Exists
     *
     * @param var $sku
     * @return mixed
     */
    public function checkProductExists($sku)
    {
        return $this->_productRepository->getIdBySku($sku);
    }

    /**
     * Load Wishlist By Customer
     *
     * @param var $customerId
     * @return mixed
     */
    public function loadWishlistByCustomer($customerId)
    {
        return $this->_wishlistFactory
            ->create()
            ->loadByCustomerId($customerId, true);
    }

    /**
     * Get sku List
     *
     * @param var $explodeskuList
     * @param var $allSku
     * @param var $customerId
     * @return mixed
     */

    public function getSkus($explodeskuList, $allSku, $customerId)
    {
        $invalidSku = [];
        $validSku = [];
        foreach ($explodeskuList as $sku) {
            if (!in_array($sku, $allSku)) {
                $productId = $this->checkProductExists($sku);
                if (!empty($productId)) {
                    $validSku[] = $sku;
                    $wishlist = $this->loadWishlistByCustomer($customerId);
                    $wishlist->addNewItem($productId);
                    $this->_wishlistResource->save($wishlist);
                } else {
                    $invalidSku[] = $sku;
                }
            }
        }
        return json_encode([
            "invalidSku" => $invalidSku,
            "validSku" => $validSku,
        ]);
    }
}
