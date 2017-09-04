<?php


namespace Yangyao\TelegramBot\Entities\InlineQuery;

use Yangyao\TelegramBot\Entities\InlineKeyboard;
use Yangyao\TelegramBot\Entities\InputMessageContent\InputMessageContent;

/**
 * Class InlineQueryResultVoice
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultvoice
 *
 * <code>
 * $data = [
 *   'id'                    => '',
 *   'voice_url'             => '',
 *   'title'                 => '',
 *   'caption'               => '',
 *   'voice_duration'        => 123,
 *   'reply_markup'          => <InlineKeyboard>,
 *   'input_message_content' => <InputMessageContent>,
 * ];
 * </code>
 *
 * @method string               getType()                Type of the result, must be voice
 * @method string               getId()                  Unique identifier for this result, 1-64 bytes
 * @method string               getVoiceUrl()            A valid URL for the voice recording
 * @method string               getTitle()               Recording title
 * @method string               getCaption()             Optional. Caption, 0-200 characters
 * @method int                  getVoiceDuration()       Optional. Recording duration in seconds
 * @method InlineKeyboard       getReplyMarkup()         Optional. Inline keyboard attached to the message
 * @method InputMessageContent  getInputMessageContent() Optional. Content of the message to be sent instead of the voice recording
 *
 * @method $this setId(string $id)                                                  Unique identifier for this result, 1-64 bytes
 * @method $this setVoiceUrl(string $voice_url)                                     A valid URL for the voice recording
 * @method $this setTitle(string $title)                                            Recording title
 * @method $this setCaption(string $caption)                                        Optional. Caption, 0-200 characters
 * @method $this setVoiceDuration(int $voice_duration)                              Optional. Recording duration in seconds
 * @method $this setReplyMarkup(InlineKeyboard $reply_markup)                       Optional. Inline keyboard attached to the message
 * @method $this setInputMessageContent(InputMessageContent $input_message_content) Optional. Content of the message to be sent instead of the voice recording
 */
class InlineQueryResultVoice extends InlineEntity
{
    /**
     * InlineQueryResultVoice constructor
     *
     * @param array $data
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function __construct(array $data = [])
    {
        $data['type'] = 'voice';
        parent::__construct($data);
    }
}
