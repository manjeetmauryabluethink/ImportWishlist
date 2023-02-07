<?php

declare(strict_types=1);

/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Bluethinkinc\ImportWishlist\Block\Index;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Model\Product;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Email\Model\ResourceModel\Template\CollectionFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Wishlist\Model\ResourceModel\Wishlist;
use Magento\Wishlist\Model\WishlistFactory;
use Bluethinkinc\ImportWishlist\Model\ConfigInterface;

class Save extends Action
{
    private const TEMPLATE_CODE = "product_added_to_your_wishlist";
    /**
     * @var Index
     */
    protected Index $block;
    /**
     * @var PageFactory
     */
    protected PageFactory $_pageFactory;
    /**
     * @var WishlistFactory
     */
    protected WishlistFactory $_wishlistFactory;
    /**
     * @var Wishlist
     */
    protected Wishlist $_wishlistResource;

    /**
     * @var Product
     */
    protected Product $_productRepository;
    /**
     * @var CustomerRepositoryInterface
     */
    protected CustomerRepositoryInterface $_customerRepository;
    /**
     * @var CustomerFactory
     */
    protected CustomerFactory $_customerFactory;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $_storeManager;
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $collectionFactory;
    /**
     * @var ConfigInterface
     */
    protected ConfigInterface $configInterface;

    /**
     * @param Index $block
     * @param CollectionFactory $collectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerFactory $customerFactory
     * @param StoreManagerInterface $storeManager
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param Product $productRepository
     * @param WishlistFactory $wishlistFactory
     * @param Wishlist $wishlistResource
     * @param ConfigInterface $configInterface
     */
    public function __construct(
        Index $block,
        CollectionFactory $collectionFactory,
        CustomerRepositoryInterface $customerRepository,
        CustomerFactory $customerFactory,
        StoreManagerInterface $storeManager,
        Context $context,
        PageFactory $pageFactory,
        Product $productRepository,
        WishlistFactory $wishlistFactory,
        Wishlist $wishlistResource,
        ConfigInterface $configInterface
    ) {
        $this->block = $block;
        $this->collectionFactory = $collectionFactory;
        $this->_pageFactory = $pageFactory;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_wishlistFactory = $wishlistFactory;
        $this->_wishlistResource = $wishlistResource;
        $this->_productRepository = $productRepository;
        $this->_customerRepository = $customerRepository;
        $this->configInterface = $configInterface;
        parent::__construct($context);
    }
    /**
     * Get system configuration value
     *
     * @return execute function
     */
    public function execute()
    {
        $validCustomer = [];
        $invalidCustomer = [];
        $invalidSku = [];
        $data = $this->getRequest()->getPostValue();
        $skuList = $data["sku"];
        $email = $data["email"];
        $explodemailList = explode(",", $email);
        $sendMailEnable = $this->configInterface->isEnabled();
        /**
         * Get templateId
         *
         * @return execute function
         */
        $templateData = $this->collectionFactory
            ->create()
            ->addFieldToSelect("template_id")
            ->addFieldToFilter("template_code", self::TEMPLATE_CODE)
            ->load();
        $templateId = $templateData->getData();
        if (!empty($templateId)) {
            foreach ($templateId as $template) {
                $emailTemplate = $template;
                foreach ($emailTemplate as $emailiTemplateId) {
                    $emailiTemplateId;
                }
            }
        }
        try {
            $this->getAllData($explodemailList, $skuList);
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
    }

    /**
     * Get customer id by email
     *
     * @param var $emailId
     * @return string
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
     * Get All Data
     *
     * @param  mixed $explodemailList
     * @param  mixed $skuList
     * @return void
     */
    public function getAllData($explodemailList, $skuList)
    {
        foreach ($explodemailList as $emailId) {
            $customerId = $this->getCustomerIdByEmail($emailId);
            if (!empty($customerId)) {
                $validCustomer["email"] = $emailId;
                $allSku = $this->getCustomerWishListItem($customerId);
                $explodeskuList = explode(",", $skuList);
                $validSku = [];
                foreach ($explodeskuList as $sku) {
                    if (!in_array($sku, $allSku)) {
                        $productId = $this->checkProductExists($sku);
                        if (!empty($productId)) {
                            $validSku[] = $sku;
                            $wishlist = $this->loadWishlistByCustomer(
                                $customerId
                            );
                            $wishlist->addNewItem($productId);
                            $data = $this->_wishlistResource->save($wishlist);
                        } else {
                            $invalidSku[] = $sku;
                        }
                    }
                }
                if (!empty($sendMailEnable) && !empty($validSku)) {
                    $this->block->sendEmail(
                        $validCustomer,
                        $emailiTemplateId,
                        $validSku
                    );
                }
            } else {
                $invalidCustomer[] = $emailId;
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
        if (!empty($invalidSku)) {
            $uniqueSku = array_unique($invalidSku);
            $this->messageManager->addErrorMessage(
                __("SKU " . implode(",", $uniqueSku) . " Doesn't Exists")
            );
        }
        if (!empty($validCustomer) && !empty($validSku)) {
            $this->messageManager->addSuccessMessage(__("Import Successfully"));
        }
        return $this->_redirect("csvdata/index/import");
    }
}
