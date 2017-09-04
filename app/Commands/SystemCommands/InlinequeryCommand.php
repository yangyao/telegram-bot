<?php


namespace Yangyao\TelegramBot\Commands\SystemCommands;

use Yangyao\TelegramBot\Commands\SystemCommand;
use Yangyao\TelegramBot\Entities\InlineQuery\InlineQueryResultArticle;
use Yangyao\TelegramBot\Entities\InputMessageContent\InputTextMessageContent;
use Yangyao\TelegramBot\Request;

/**
 * Inline query command
 */
class InlinequeryCommand extends SystemCommand
{
    /**
     * @var string
     */
    protected $name = 'inlinequery';

    /**
     * @var string
     */
    protected $description = 'Reply to inline query';

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
        //$inline_query = $this->getUpdate()->getInlineQuery();
        //$user_id      = $inline_query->getFrom()->getId();
        //$query        = $inline_query->getQuery();

        return Request::answerInlineQuery(['inline_query_id' => $this->getUpdate()->getInlineQuery()->getId()]);
    }
}
