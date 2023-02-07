<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Controller\Adminhtml\Index;

use Magento\Framework\App\Action\HttpPostActionInterface;

class Delete extends \Bluethinkinc\ImportWishlist\Controller\Adminhtml\Block implements HttpPostActionInterface
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam("wishlist_item_id");
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(
                    \Magento\Wishlist\Model\Item::class
                );
                 $model->load($id);
                // print_r(get_class_methods($data));die;
                // print_R($data->getData());die;
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(
                    __("You deleted wishlist Item.")
                );
                // go to grid
                return $resultRedirect->setPath("csvdata/index/export");
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(
            __('We can\'t find a block to delete.')
        );
        // go to grid
        return $resultRedirect->setPath("*/*/");
    }
}
