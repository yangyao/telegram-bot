<?php


namespace Yangyao\TelegramBot\Commands\AdminCommands;

use Yangyao\TelegramBot\Commands\AdminCommand;
use Yangyao\TelegramBot\Entities\Message;
use Yangyao\TelegramBot\Entities\ServerResponse;
use Yangyao\TelegramBot\Request;

/**
 * Admin "/sendtoall" command
 */
class SendtoallCommand extends AdminCommand
{
    /**
     * @var string
     */
    protected $name = 'sendtoall';

    /**
     * @var string
     */
    protected $description = 'Send the message to all of the bot\'s users';

    /**
     * @var string
     */
    protected $usage = '/sendtoall <message to send>';

    /**
     * @var string
     */
    protected $version = '1.4.0';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * Execute command
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $text    = $message->getText(true);

        if ($text === '') {
            $text = 'Write the message to send: /sendtoall <message>';
        } else {

            $chats = $this->handler->selectChats( [
                'groups'      => true,
                'supergroups' => true,
                'channels'    => false,
                'users'       => true,
            ]);
            $results = Request::sendToActiveChats(
                $chats,
                'sendMessage', //callback function to execute (see Request.php methods)
                ['text' => $text] //Param to evaluate the request
            );

            $total  = 0;
            $failed = 0;

            $text = 'Message sent to:' . PHP_EOL;

            /** @var ServerResponse $result */
            foreach ($results as $result) {
                $name = '';
                $type = '';
                if ($result->isOk()) {
                    $status = '✔️';

                    /** @var Message $message */
                    $message = $result->getResult();
                    $chat    = $message->getChat();
                    if ($chat->isPrivateChat()) {
                        $name = $chat->getFirstName();
                        $type = 'user';
                    } else {
                        $name = $chat->getTitle();
                        $type = 'chat';
                    }
                } else {
                    $status = '✖️';
                    ++$failed;
                }
                ++$total;

                $text .= $total . ') ' . $status . ' ' . $type . ' ' . $name . PHP_EOL;
            }
            $text .= 'Delivered: ' . ($total - $failed) . '/' . $total . PHP_EOL;

            if ($total === 0) {
                $text = 'No users or chats found..';
            }
        }

        $data = [
            'chat_id' => $chat_id,
            'text'    => $text,
        ];

        return Request::sendMessage($data);
    }
}
