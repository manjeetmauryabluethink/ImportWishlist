<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml;

abstract class Block extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = "Bluethinkinc_ImportWishlist::wishlist";

    /**
     * Connect to layout
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage
            ->setActiveMenu("Bluethinkinc_ImportWishlist::wishlist")
            ->addBreadcrumb(__("Wishlist"), __("Wishlist"))
            ->addBreadcrumb(__("Static Wishlist"), __("Static Wishlist"));
        return $resultPage;
    }
}
