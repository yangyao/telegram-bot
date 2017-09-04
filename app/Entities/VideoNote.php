<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class VideoNote
 *
 * @link https://core.telegram.org/bots/api#videonote
 *
 * @method string    getFileId()   Unique identifier for this file
 * @method int       getLength()   Video width and height as defined by sender
 * @method int       getDuration() Duration of the audio in seconds as defined by sender
 * @method PhotoSize getThumb()    Optional. Video thumbnail as defined by sender
 * @method int       getFileSize() Optional. File size
 */
class VideoNote extends Entity
{
    /**
     * {@inheritdoc}
     */
    protected function subEntities()
    {
        return [
            'thumb' => PhotoSize::class,
        ];
    }
}
