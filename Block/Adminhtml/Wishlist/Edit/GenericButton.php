<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Block\Adminhtml\Wishlist\Edit;

use Magento\Backend\Block\Widget\Context;

/**
 * Class GenericButton use for button
 */

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * Data
     *
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Generate url by route and parameters
     *
     * @param  var $route
     * @param  var $params
     * @return mixed
     */
    public function getUrl($route = "", $params = [])
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
