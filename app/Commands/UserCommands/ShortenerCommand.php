<?php


namespace Yangyao\TelegramBot\Commands\UserCommands;

use Yangyao\TelegramBot\Botan;
use Yangyao\TelegramBot\Commands\UserCommand;
use Yangyao\TelegramBot\Request;

/**
 * User "/shortener" command
 *
 * Create a shortened URL using Botan.
 */
class ShortenerCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'shortener';

    /**
     * @var string
     */
    protected $description = 'Botan Shortener example';

    /**
     * @var string
     */
    protected $usage = '/shortener';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execute method
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();
        $chat_id = $message->getChat()->getId();

        $text = file_get_contents('http://suo.im/api.php?url='.urlencode('https://github.com/yangyao'));

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}
