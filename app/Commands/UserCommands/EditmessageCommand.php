<?php


namespace Yangyao\TelegramBot\Commands\UserCommands;

use Yangyao\TelegramBot\Commands\UserCommand;
use Yangyao\TelegramBot\Request;

/**
 * User "/editmessage" command
 *
 * Command to edit a message via bot.
 */
class EditmessageCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'editmessage';

    /**
     * @var string
     */
    protected $description = 'Edit message';

    /**
     * @var string
     */
    protected $usage = '/editmessage';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * Command execute method
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message          = $this->getMessage();
        $chat_id          = $message->getChat()->getId();
        $reply_to_message = $message->getReplyToMessage();
        $text             = $message->getText(true);

        if ($reply_to_message && $message_to_edit = $reply_to_message->getMessageId()) {
            $data_edit = [
                'chat_id'    => $chat_id,
                'message_id' => $message_to_edit,
                'text'       => $text ?: 'Edited message',
            ];

            // Try to edit selected message.
            $result = Request::editMessageText($data_edit);

            if ($result->isOk()) {
                // Delete this editing reply message.
                Request::deleteMessage([
                    'chat_id'    => $chat_id,
                    'message_id' => $message->getMessageId(),
                ]);
            }

            return $result;
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => sprintf("Reply to any bots' message and use /%s <your text> to edit it.", $this->name),
        ];

        return Request::sendMessage($data);
    }
}
