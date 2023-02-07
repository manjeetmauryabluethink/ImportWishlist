<?php
/**
 * Copyright © BluethinkInc All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Bluethinkinc\ImportWishlist\Model;

/**
 * Wishlist module configuration
 *
 * @api
 * @since 100.2.0
 */
interface ConfigInterface
{
    /**
     * Recipient email config path
     */
    public const XML_PATH_EMAIL_SENDER = 'importwishlist/generalemail/send_notification_mail';

    /**
     * Check if import wishlist module is enabled
     *
     * @return bool
     * @since 100.2.0
     */
    public function isEnabled();
}
