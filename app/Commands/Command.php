<?php


namespace Yangyao\TelegramBot\Commands;

use Yangyao\TelegramBot\Entities\CallbackQuery;
use Yangyao\TelegramBot\Entities\ChosenInlineResult;
use Yangyao\TelegramBot\Entities\InlineQuery;
use Yangyao\TelegramBot\Entities\Message;
use Yangyao\TelegramBot\Entities\Update;
use Yangyao\TelegramBot\Request;
use Yangyao\TelegramBot\Telegram;

/**
 * Class Command
 *
 * Base class for commands. It includes some helper methods that can fetch data directly from the Update object.
 *
 * @method Message             getMessage()            Optional. New incoming message of any kind — text, photo, sticker, etc.
 * @method Message             getEditedMessage()      Optional. New version of a message that is known to the bot and was edited
 * @method Message             getChannelPost()        Optional. New post in the channel, can be any kind — text, photo, sticker, etc.
 * @method Message             getEditedChannelPost()  Optional. New version of a post in the channel that is known to the bot and was edited
 * @method InlineQuery         getInlineQuery()        Optional. New incoming inline query
 * @method ChosenInlineResult  getChosenInlineResult() Optional. The result of an inline query that was chosen by a user and sent to their chat partner.
 * @method CallbackQuery       getCallbackQuery()      Optional. New incoming callback query
 */
abstract class Command
{
    /**
     * Telegram object
     *
     * @var \Yangyao\TelegramBot\Telegram
     */
    protected $telegram;

    /**
     * Update object
     *
     * @var \Yangyao\TelegramBot\Entities\Update
     */
    protected $update;

    /**
     * Name
     *
     * @var string
     */
    protected $name = '';

    /**
     * Description
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Usage
     *
     * @var string
     */
    protected $usage = 'Command usage';

    /**
     * Show in Help
     *
     * @var bool
     */
    protected $show_in_help = true;

    /**
     * Version
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * If this command is enabled
     *
     * @var boolean
     */
    protected $enabled = true;

    /**
     * If this command needs mysql
     *
     * @var boolean
     */
    protected $need_mysql = false;

    /*
    * Make sure this command only executes on a private chat.
    *
    * @var bool
    */
    protected $private_only = false;

    /**
     * Command config
     *
     * @var array
     */
    protected $config = [];

    /**
     * Constructor
     *
     * @param \Yangyao\TelegramBot\Telegram        $telegram
     * @param \Yangyao\TelegramBot\Entities\Update $update
     */
    public function __construct(Telegram $telegram, Update $update = null)
    {
        $this->telegram = $telegram;
        $this->setUpdate($update);
        $this->config = $telegram->getSchedule()->getCommandConfig($this->name);
    }

    /**
     * Set update object
     *
     * @param \Yangyao\TelegramBot\Entities\Update $update
     *
     * @return \Yangyao\TelegramBot\Commands\Command
     */
    public function setUpdate(Update $update = null)
    {
        if ($update !== null) {
            $this->update = $update;
        }

        return $this;
    }

    /**
     * Pre-execute command
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function preExecute()
    {
        if ($this->isPrivateOnly() && $this->removeNonPrivateMessage()) {
            $message = $this->getMessage();

            if ($user = $message->getFrom()) {
                return Request::sendMessage([
                    'chat_id'    => $user->getId(),
                    'parse_mode' => 'Markdown',
                    'text'       => sprintf(
                        "/%s command is only available in a private chat.\n(`%s`)",
                        $this->getName(),
                        $message->getText()
                    ),
                ]);
            }

            return Request::emptyResponse();
        }

        return $this->execute();
    }

    /**
     * Execute command
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    abstract public function execute();


    /**
     * Get update object
     *
     * @return \Yangyao\TelegramBot\Entities\Update
     */
    public function getUpdate()
    {
        return $this->update;
    }

    /**
     * Relay any non-existing function calls to Update object.
     *
     * This is purely a helper method to make requests from within execute() method easier.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return Command
     */
    public function __call($name, array $arguments)
    {
        if ($this->update === null) {
            return null;
        }
        return call_user_func_array([$this->update, $name], $arguments);
    }

    /**
     * Get command config
     *
     * Look for config $name if found return it, if not return null.
     * If $name is not set return all set config.
     *
     * @param string|null $name
     *
     * @return array|mixed|null
     */
    public function getConfig($name = null)
    {
        if ($name === null) {
            return $this->config;
        }
        if (isset($this->config[$name])) {
            return $this->config[$name];
        }

        return null;
    }

    /**
     * Get telegram object
     *
     * @return \Yangyao\TelegramBot\Telegram
     */
    public function getTelegram()
    {
        return $this->telegram;
    }

    /**
     * Get usage
     *
     * @return string
     */
    public function getUsage()
    {
        return $this->usage;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get Show in Help
     *
     * @return bool
     */
    public function showInHelp()
    {
        return $this->show_in_help;
    }

    /**
     * Check if command is enabled
     *
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * If this command is intended for private chats only.
     *
     * @return bool
     */
    public function isPrivateOnly()
    {
        return $this->private_only;
    }

    /**
     * If this is a SystemCommand
     *
     * @return bool
     */
    public function isSystemCommand()
    {
        return ($this instanceof SystemCommand);
    }

    /**
     * If this is an AdminCommand
     *
     * @return bool
     */
    public function isAdminCommand()
    {
        return ($this instanceof AdminCommand);
    }

    /**
     * If this is a UserCommand
     *
     * @return bool
     */
    public function isUserCommand()
    {
        return ($this instanceof UserCommand);
    }

    /**
     * Delete the current message if it has been called in a non-private chat.
     *
     * @return bool
     */
    protected function removeNonPrivateMessage()
    {
        $message = $this->getMessage() ?: $this->getEditedMessage();

        if ($message) {
            $chat = $message->getChat();

            if (!$chat->isPrivateChat()) {
                // Delete the falsely called command message.
                Request::deleteMessage([
                    'chat_id'    => $chat->getId(),
                    'message_id' => $message->getMessageId(),
                ]);

                return true;
            }
        }

        return false;
    }
}
