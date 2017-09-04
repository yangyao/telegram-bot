<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class InlineQuery
 *
 * @link https://core.telegram.org/bots/api#inlinequery
 *
 * @method string   getId()       Unique identifier for this query
 * @method User     getFrom()     Sender
 * @method Location getLocation() Optional. Sender location, only for bots that request user location
 * @method string   getQuery()    Text of the query (up to 512 characters)
 * @method string   getOffset()   Offset of the results to be returned, can be controlled by the bot
 */
class InlineQuery extends Entity
{
    /**
     * {@inheritdoc}
     */
    protected function subEntities()
    {
        return [
            'from'     => User::class,
            'location' => Location::class,
        ];
    }
}
