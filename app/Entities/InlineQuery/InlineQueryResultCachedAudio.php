<?php


namespace Yangyao\TelegramBot\Entities\InlineQuery;

use Yangyao\TelegramBot\Entities\InlineKeyboard;
use Yangyao\TelegramBot\Entities\InputMessageContent\InputMessageContent;

/**
 * Class InlineQueryResultCachedAudio
 *
 * @link https://core.telegram.org/bots/api#inlinequeryresultcachedaudio
 *
 * <code>
 * $data = [
 *   'id'                    => '',
 *   'audio_file_id'         => '',
 *   'caption'               => '',
 *   'reply_markup'          => <InlineKeyboard>,
 *   'input_message_content' => <InputMessageContent>,
 * ];
 * </code>
 *
 * @method string               getType()                Type of the result, must be audio
 * @method string               getId()                  Unique identifier for this result, 1-64 bytes
 * @method string               getAudioFileId()         A valid file identifier for the audio file
 * @method string               getCaption()             Optional. Caption, 0-200 characters
 * @method InlineKeyboard       getReplyMarkup()         Optional. An Inline keyboard attached to the message
 * @method InputMessageContent  getInputMessageContent() Optional. Content of the message to be sent instead of the audio
 *
 * @method $this setId(string $id)                                                  Unique identifier for this result, 1-64 bytes
 * @method $this setAudioFileId(string $audio_file_id)                              A valid file identifier for the audio file
 * @method $this setCaption(string $caption)                                        Optional. Caption, 0-200 characters
 * @method $this setReplyMarkup(InlineKeyboard $reply_markup)                       Optional. An Inline keyboard attached to the message
 * @method $this setInputMessageContent(InputMessageContent $input_message_content) Optional. Content of the message to be sent instead of the audio
 */
class InlineQueryResultCachedAudio extends InlineEntity
{
    /**
     * InlineQueryResultCachedAudio constructor
     *
     * @param array $data
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function __construct(array $data = [])
    {
        $data['type'] = 'audio';
        parent::__construct($data);
    }
}
