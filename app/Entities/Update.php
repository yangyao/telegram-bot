<?php


namespace Yangyao\TelegramBot\Entities;

use Yangyao\TelegramBot\Commands\Factory;
use Yangyao\TelegramBot\Entities\Payments\PreCheckoutQuery;
use Yangyao\TelegramBot\Entities\Payments\ShippingQuery;

/**
 * Class Update
 *
 * @link https://core.telegram.org/bots/api#update
 *
 * @method int                 getUpdateId()           The update's unique identifier. Update identifiers start from a certain positive number and increase sequentially. This ID becomes especially handy if you’re using Webhooks, since it allows you to ignore repeated updates or to restore the correct update sequence, should they get out of order.
 * @method Message             getMessage()            Optional. New incoming message of any kind — text, photo, sticker, etc.
 * @method Message             getEditedMessage()      Optional. New version of a message that is known to the bot and was edited
 * @method Message             getChannelPost()        Optional. New post in the channel, can be any kind — text, photo, sticker, etc.
 * @method Message             getEditedChannelPost()  Optional. New version of a post in the channel that is known to the bot and was edited
 * @method InlineQuery         getInlineQuery()        Optional. New incoming inline query
 * @method ChosenInlineResult  getChosenInlineResult() Optional. The result of an inline query that was chosen by a user and sent to their chat partner.
 * @method CallbackQuery       getCallbackQuery()      Optional. New incoming callback query
 * @method ShippingQuery       getShippingQuery()      Optional. New incoming shipping query. Only for invoices with flexible price
 * @method PreCheckoutQuery    getPreCheckoutQuery()   Optional. New incoming pre-checkout query. Contains full information about checkout
 */
class Update extends Entity
{
    public static $update_methods = [
        'getMessage',
        'getEditedMessage',
        'getChannelPost',
        'getEditedChannelPost',
        'getInlineQuery',
        'getChosenInlineResult',
        'getCallbackQuery',
    ];
    /**
     * {@inheritdoc}
     */
    protected function subEntities()
    {
        return [
            'message'              => Message::class,
            'edited_message'       => EditedMessage::class,
            'channel_post'         => ChannelPost::class,
            'edited_channel_post'  => EditedChannelPost::class,
            'inline_query'         => InlineQuery::class,
            'chosen_inline_result' => ChosenInlineResult::class,
            'callback_query'       => CallbackQuery::class,
            'shipping_query'       => ShippingQuery::class,
            'pre_checkout_query'   => PreCheckoutQuery::class,
        ];
    }

    /**
     * Get the update type based on the set properties
     *
     * @return string|null
     */
    public function getUpdateType()
    {
        $types = [
            'message',
            'edited_message',
            'channel_post',
            'edited_channel_post',
            'inline_query',
            'chosen_inline_result',
            'callback_query',
            'shipping_query',
            'pre_checkout_query',
        ];
        foreach ($types as $type) {
            if ($this->getProperty($type)) {
                return $type;
            }
        }

        return null;
    }

    /**
     * Get update content
     *
     * @return \Yangyao\TelegramBot\Entities\CallbackQuery
     *         |\Yangyao\TelegramBot\Entities\ChosenInlineResult
     *         |\Yangyao\TelegramBot\Entities\InlineQuery
     *         |\Yangyao\TelegramBot\Entities\Message
     */
    public function getUpdateContent()
    {
        if ($update_type = $this->getUpdateType()) {
            // Instead of just getting the property as an array,
            // use the __call method to get the correct Entity object.
            $method = 'get' . str_replace('_', '', ucwords($update_type, '_'));
            return $this->$method();
        }

        return null;
    }


    /**
     * @param Update $update
     * @return null | string
     */
    public function getUserId(){
        foreach (self::$update_methods as $update_method) {
            $object = call_user_func([$this, $update_method]);
            if ($object !== null && $from = $object->getFrom()) {
                return  $from->getId();
            }
        }
        return null;
    }
}
