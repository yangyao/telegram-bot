<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;

/**
 * Migrate from chat id command
 */
class MigratefromchatidCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'migratefromchatid';

    /**
     * @var string
     */
    protected $description = 'Migrate from chat id';

    /**
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * Command execute method
     *
     * @return mixed
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        //$message = $this->getMessage();
        //$migrate_from_chat_id = $message->getMigrateFromChatId();

        return parent::execute();
    }
}
