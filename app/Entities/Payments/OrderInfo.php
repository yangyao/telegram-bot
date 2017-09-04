<?php


namespace Yangyao\TelegramBot\Entities\Payments;

use Yangyao\TelegramBot\Entities\Entity;

/**
 * Class OrderInfo
 *
 * This object represents information about an order.
 *
 * @link https://core.telegram.org/bots/api#orderinfo
 *
 * @method string          getName()            Optional. User name
 * @method string          getPhoneNumber()     Optional. User's phone number
 * @method string          getEmail()           Optional. User email
 * @method ShippingAddress getShippingAddress() Optional. User shipping address
 **/
class OrderInfo extends Entity
{
    /**
     * {@inheritdoc}
     */
    public function subEntities()
    {
        return [
            'shipping_address' => ShippingAddress::class,
        ];
    }
}
