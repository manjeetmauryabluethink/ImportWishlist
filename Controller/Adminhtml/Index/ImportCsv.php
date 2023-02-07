<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class ImportCsv extends \Magento\Backend\App\Action
{
    /**
     * Controller construct
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
     * Return execute
     *
     * @return mixed
     */
    public function execute()
    {
        $resultPage = $this->_pageFactory->create();
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__("Import"));
        return $resultPage;
    }
}
