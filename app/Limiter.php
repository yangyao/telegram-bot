<?php
/**
 * Created by PhpStorm.
 * User: yangy
 * Date: 2017/9/3
 * Time: 22:53
 */

namespace Yangyao\TelegramBot;

use Yangyao\TelegramBot\Exception\TelegramException;
use Yangyao\TelegramBot\Database\Handler\HandlerInterface;


class Limiter {


    private static $limited_methods = [
        'sendMessage',
        'forwardMessage',
        'sendPhoto',
        'sendAudio',
        'sendDocument',
        'sendSticker',
        'sendVideo',
        'sendVoice',
        'sendVideoNote',
        'sendLocation',
        'sendVenue',
        'sendContact',
        'sendInvoice',
        'editMessageText',
        'editMessageCaption',
        'editMessageReplyMarkup',
        'setChatTitle',
        'setChatDescription',
    ];

    private $limiter_enabled = false;

    private $limiter_interval = 10;

    /**@var  HandlerInterface $handler */
    private $handler = NULL;


    public function __construct(HandlerInterface $handler){
        $this->handler = $handler;
    }


    /**
     * Enable requests limiter
     *
     * @param  array $options
     *
     * @return \Yangyao\TelegramBot\Telegram
     */
    public function enableLimiter()
    {
        $options = [];
        $options_default = [
            'interval' => 1,
        ];

        $options = array_merge($options_default, $options);

        if (!is_numeric($options['interval']) || $options['interval'] <= 0) {
            throw new TelegramException('Interval must be a number and must be greater than zero!');
        }

        $this->limiter_interval = $options['interval'];
        $this->limiter_enabled  = true;
    }


    /**
     * This functions delays API requests to prevent reaching Telegram API limits
     *  Can be disabled while in execution by 'Request::setLimiter(false)'
     *
     * @link https://core.telegram.org/bots/faq#my-bot-is-hitting-limits-how-do-i-avoid-this
     *
     * @param string $action
     * @param array  $data
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */

    public function limitTelegramRequests($action, $data){
        if (!$this->limiter_enabled) return ;

        $chat_id           = isset($data['chat_id']) ? $data['chat_id'] : null;
        $inline_message_id = isset($data['inline_message_id']) ? $data['inline_message_id'] : null;

        if (($chat_id || $inline_message_id) && in_array($action, self::$limited_methods)) {
            $timeout = 60;
            while (true) {
                if ($timeout <= 0) {
                    throw new TelegramException('Timed out while waiting for a request spot!');
                }
                $requests = $this->handler->getTelegramRequestCount($chat_id, $inline_message_id);

                $chat_per_second   = ($requests['LIMIT_PER_SEC'] == 0); // No more than one message per second inside a particular chat
                $global_per_second = ($requests['LIMIT_PER_SEC_ALL'] < 30);    // No more than 30 messages per second to different chats
                $groups_per_minute = (((is_numeric($chat_id) && $chat_id > 0) || !is_null($inline_message_id)) || ((!is_numeric($chat_id) || $chat_id < 0) && $requests['LIMIT_PER_MINUTE'] < 20));    // No more than 20 messages per minute in groups and channels
                if ($chat_per_second && $global_per_second && $groups_per_minute) {
                    break;
                }
                $timeout--;
                usleep($this->limiter_interval * 1000000);
            }
            $this->handler->insertTelegramRequest($action, $data);
        }
    }

} 