<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Block\Adminhtml\Wishlist\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * Get button Data
     *
     * @return array
     */
    public function getButtonData()
    {
        return [
            "label" => __("Save"),
            "class" => "save primary",
            "data_attribute" => [
                "mage-init" => [
                    "buttonAdapter" => [
                        "actions" => [
                            [
                                "targetName" => "import_listing.import_listing",
                                "actionName" => "save",
                                "params" => [false],
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
