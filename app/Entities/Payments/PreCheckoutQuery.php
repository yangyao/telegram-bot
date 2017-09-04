<?php


namespace Yangyao\TelegramBot\Entities\Payments;

use Yangyao\TelegramBot\Entities\Entity;
use Yangyao\TelegramBot\Entities\User;

/**
 * Class PreCheckoutQuery
 *
 * This object contains information about an incoming pre-checkout query.
 *
 * @link https://core.telegram.org/bots/api#precheckoutquery
 *
 * @method string    getId()               Unique query identifier
 * @method User      getFrom()             User who sent the query
 * @method string    getCurrency()         Three-letter ISO 4217 currency code
 * @method int       getTotalAmount()      Total price in the smallest units of the currency (integer, not float/double).
 * @method string    getInvoicePayload()   Bot specified invoice payload
 * @method string    getShippingOptionId() Optional. Identifier of the shipping option chosen by the user
 * @method OrderInfo getOrderInfo()        Optional. Order info provided by the user
 **/
class PreCheckoutQuery extends Entity
{
    /**
     * {@inheritdoc}
     */
    public function subEntities()
    {
        return [
            'user'       => User::class,
            'order_info' => OrderInfo::class,
        ];
    }
}
