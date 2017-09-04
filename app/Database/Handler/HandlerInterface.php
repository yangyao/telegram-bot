<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Database\Handler;


use Yangyao\TelegramBot\Entities\Update;
use Yangyao\TelegramBot\Exception\TelegramException;

interface  HandlerInterface
{

    /**
     * @return null | int
     */
    public function getLastTelegramUpdateId();

    /**
     * Insert request into database
     *
     * @param \Yangyao\TelegramBot\Entities\Update $update
     *
     * @return bool
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertRequest(Update $update);

    /**
     * @param $chat_id
     * @param $inline_message_id
     * @return mixed
     */
    public function getTelegramRequestCount($chat_id, $inline_message_id);

}