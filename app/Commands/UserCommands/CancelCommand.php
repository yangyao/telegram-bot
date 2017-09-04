<?php


namespace Yangyao\TelegramBot\Commands\UserCommands;

use Yangyao\TelegramBot\Commands\UserCommand;
use Yangyao\TelegramBot\Conversation;
use Yangyao\TelegramBot\Entities\Keyboard;
use Yangyao\TelegramBot\Request;

/**
 * User "/cancel" command
 *
 * This command cancels the currently active conversation and
 * returns a message to let the user know which conversation it was.
 * If no conversation is active, the returned message says so.
 */
class CancelCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'cancel';

    /**
     * @var string
     */
    protected $description = 'Cancel the currently active conversation';

    /**
     * @var string
     */
    protected $usage = '/cancel';

    /**
     * @var string
     */
    protected $version = '0.2.1';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Command execute method
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $text = 'No active conversation!';

        //Cancel current conversation if any
        $conversation = new Conversation(
            $this->telegram,
            $this->getMessage()->getFrom()->getId(),
            $this->getMessage()->getChat()->getId()
        );

        if ($conversation_command = $conversation->getCommand()) {
            $conversation->cancel();
            $text = 'Conversation "' . $conversation_command . '" cancelled!';
        }

        return $this->removeKeyboard($text);
    }

    /**
     * Remove the keyboard and output a text
     *
     * @param string $text
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    private function removeKeyboard($text)
    {
        return Request::sendMessage([
            'reply_markup' => Keyboard::remove(['selective' => true]),
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => $text,
        ]);
    }

    /**
     * Command execute method if MySQL is required but not available
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function executeNoDb()
    {
        return $this->removeKeyboard('Nothing to cancel.');
    }
}
