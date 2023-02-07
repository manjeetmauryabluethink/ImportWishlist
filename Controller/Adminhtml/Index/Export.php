<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Export extends \Magento\Backend\App\Action
{
    /**
     * Return pageFactory
     *
     * @var PageFactory
     */
    public $_pageFactory;

    /**
     * Export constructor
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        $this->_pageFactory = $pageFactory;
        parent::__construct($context);
    }

    /**
     * Execute function
     *
     * @return mixed
     */
    public function execute()
    {
        $resultPage = $this->_pageFactory->create();
        $resultPage->setActiveMenu("Bluethinkinc_ImportWishlist::wishlist");
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__("Import Export Wishlist"));
        return $resultPage;
    }
}
