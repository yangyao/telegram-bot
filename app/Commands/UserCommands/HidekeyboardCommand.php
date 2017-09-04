<?php


namespace Yangyao\TelegramBot\Commands\UserCommands;

use Yangyao\TelegramBot\Commands\UserCommand;
use Yangyao\TelegramBot\Entities\Keyboard;
use Yangyao\TelegramBot\Request;

/**
 * User "/hidekeyboard" command
 *
 * Command to hide the keyboard.
 */
class HidekeyboardCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'hidekeyboard';

    /**
     * @var string
     */
    protected $description = 'Hide the custom keyboard';

    /**
     * @var string
     */
    protected $usage = '/hidekeyboard';

    /**
     * @var string
     */
    protected $version = '0.1.0';

    /**
     * Command execute method
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $chat_id = $this->getMessage()->getChat()->getId();

        $data = [
            'chat_id'      => $chat_id,
            'text'         => 'Keyboard Hidden',
            'reply_markup' => Keyboard::remove(),
        ];

        return Request::sendMessage($data);
    }
}
