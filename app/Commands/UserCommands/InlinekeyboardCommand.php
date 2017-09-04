<?php


namespace Yangyao\TelegramBot\Commands\UserCommands;

use Yangyao\TelegramBot\Commands\UserCommand;
use Yangyao\TelegramBot\Entities\InlineKeyboard;
use Yangyao\TelegramBot\Request;

/**
 * User "/inlinekeyboard" command
 *
 * Display an inline keyboard with a few buttons.
 */
class InlinekeyboardCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'inlinekeyboard';

    /**
     * @var string
     */
    protected $description = 'Show inline keyboard';

    /**
     * @var string
     */
    protected $usage = '/inlinekeyboard';

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

        $switch_element = mt_rand(0, 9) < 5 ? 'true' : 'false';

        $inline_keyboard = new InlineKeyboard([
            ['text' => 'inline', 'switch_inline_query' => $switch_element],
            ['text' => 'inline current chat', 'switch_inline_query_current_chat' => $switch_element],
        ], [
            ['text' => 'callback', 'callback_data' => 'identifier'],
            ['text' => 'open url', 'url' => 'https://github.com/php-telegram-bot/core'],
        ]);

        $data = [
            'chat_id'      => $chat_id,
            'text'         => 'inline keyboard',
            'reply_markup' => $inline_keyboard,
        ];

        return Request::sendMessage($data);
    }
}
