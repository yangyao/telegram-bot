<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class ReplyToMessage
 *
 * @todo Is this even required?!
 */
class ReplyToMessage extends Message
{
    /**
     * ReplyToMessage constructor.
     *
     * @param array  $data
     * @param string $bot_username
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function __construct(array $data, $bot_username = '')
    {
        //As explained in the documentation
        //Reply to message can't contain other reply to message entities
        unset($data['reply_to_message']);

        parent::__construct($data, $bot_username);
    }
}
