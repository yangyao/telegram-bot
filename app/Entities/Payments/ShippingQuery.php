<?php


namespace Yangyao\TelegramBot\Entities\Payments;

use Yangyao\TelegramBot\Entities\Entity;
use Yangyao\TelegramBot\Entities\User;

/**
 * Class ShippingQuery
 *
 * This object contains information about an incoming shipping query.
 *
 * @link https://core.telegram.org/bots/api#shippingquery
 *
 * @method string          getId()              Unique query identifier
 * @method User            getFrom()            User who sent the query
 * @method string          getInvoicePayload()  Bot specified invoice payload
 * @method ShippingAddress getShippingAddress() User specified shipping address
 **/
class ShippingQuery extends Entity
{
    /**
     * {@inheritdoc}
     */
    public function subEntities()
    {
        return [
            'user'             => User::class,
            'shipping_address' => ShippingAddress::class,
        ];
    }
}
