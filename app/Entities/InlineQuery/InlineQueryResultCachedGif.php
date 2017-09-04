<?php


namespace Yangyao\TelegramBot\Entities\InlineQuery;

use Yangyao\TelegramBot\Entities\InlineKeyboard;
use Yangyao\TelegramBot\Entities\InputMessageContent\InputMessageContent;

/**
 * Class InlineQueryResultCachedGif
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultcachedgif
 *
 * <code>
 * $data = [
 *   'id'                    => '',
 *   'gif_file_id'           => '',
 *   'title'                 => '',
 *   'caption'               => '',
 *   'reply_markup'          => <InlineKeyboard>,
 *   'input_message_content' => <InputMessageContent>,
 * ];
 * </code>
 *
 * @method string               getType()                Type of the result, must be gif
 * @method string               getId()                  Unique identifier for this result, 1-64 bytes
 * @method string               getGifFileId()           A valid file identifier for the GIF file
 * @method string               getTitle()               Optional. Title for the result
 * @method string               getCaption()             Optional. Caption of the GIF file to be sent, 0-200 characters
 * @method InlineKeyboard       getReplyMarkup()         Optional. An Inline keyboard attached to the message
 * @method InputMessageContent  getInputMessageContent() Optional. Content of the message to be sent instead of the GIF animation
 *
 * @method $this setId(string $id)                                                  Unique identifier for this result, 1-64 bytes
 * @method $this setGifFileId(string $gif_file_id)                                  A valid file identifier for the GIF file
 * @method $this setTitle(string $title)                                            Optional. Title for the result
 * @method $this setCaption(string $caption)                                        Optional. Caption of the GIF file to be sent, 0-200 characters
 * @method $this setReplyMarkup(InlineKeyboard $reply_markup)                       Optional. An Inline keyboard attached to the message
 * @method $this setInputMessageContent(InputMessageContent $input_message_content) Optional. Content of the message to be sent instead of the GIF animation
 */
class InlineQueryResultCachedGif extends InlineEntity
{
    /**
     * InlineQueryResultCachedGif constructor
     *
     * @param array $data
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function __construct(array $data = [])
    {
        $data['type'] = 'gif';
        parent::__construct($data);
    }
}
