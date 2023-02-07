<?php
namespace Bluethinkinc\ImportWishlist\Block;

use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class Index extends \Magento\Framework\View\Element\Template
{

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Block constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storemanager
     * @param ProductRepositoryInterface $productRepository
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        ProductRepositoryInterface $productRepository,
        TransportBuilder $transportBuilder
    ) {
        parent::__construct($context);
        $this->_storeManager = $storemanager;
        $this->productRepository = $productRepository;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Send Email
     *
     * @param var $validCustomer
     * @param var $emailiTemplateId
     * @param var $validSku
     * @return void
     */
    public function sendEmail($validCustomer, $emailiTemplateId, $validSku)
    {
        $productData = [];
        $productDetail = $this->getWishlistProductDetail($validSku);
        $data = [];
        foreach ($validSku as $sku) {
            $store = $this->_storeManager->getStore();
            $product = $this->productRepository->get($sku);
            array_push($data, [
                "sku" => $product->getSku(),
                "name" => $product->getName(),
                "image" => $product->getImage(),
            ]);
        }
        $productNameData = [];
        foreach ($data as $key => $single) {
            $productNameData[] = $single["sku"];
        }
        $validProductName = implode(",", $productNameData);
        $senderEmail = $validCustomer["email"];
        $senderName = "test@test.com";
        $identifier = $emailiTemplateId;
        $templateVars = [
            "email" => $senderEmail,
            "name" => $productNameData,
        ];
        $recipientEmail = $senderEmail;
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($identifier)
            ->setTemplateOptions([
                "area" => \Magento\Framework\App\Area::AREA_FRONTEND,
                "store" => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
            ])
            ->setTemplateVars($templateVars)
            ->setFrom([
                "namefrom" => $senderName,
                "email" => $senderEmail,
                "name" => $validProductName,
            ])
            ->addTo([$recipientEmail])
            ->getTransport();
        $transport->sendMessage();
    }

    /**
     * Get wishlist product detail
     *
     * @param var $validSku
     * @return void
     */
    public function getWishlistProductDetail($validSku)
    {
        $data = [];
        foreach ($validSku as $sku) {
            $store = $this->_storeManager->getStore();
            $product = $this->productRepository->get($sku);
            array_push($data, [
                "sku" => $product->getSku(),
                "name" => $product->getName(),
                "image" => $product->getImage(),
            ]);
        }
    }
    /**
     * Get product details
     *
     * @return array
     */
    public function productName()
    {
        $data = [];
        $pdata = $this->getData("name");
        foreach ($pdata as $sku) {
            $product = $this->productRepository->get($sku);
            array_push($data, [
                "sku" => $product->getSku(),
                "name" => $product->getName(),
                "image" => $product->getImage(),
                "price" => $product->getPrice(),
                "producturl" => $product->getProductUrl(),
                "entity" => $product->getEntityId(),
                "urlkey" => $product->getUrlKey(),
            ]);
        }
        return $data;
    }
}
