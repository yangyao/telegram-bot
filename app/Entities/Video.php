<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class Video
 *
 * @link https://core.telegram.org/bots/api#video
 *
 * @method string    getFileId()   Unique identifier for this file
 * @method int       getWidth()    Video width as defined by sender
 * @method int       getHeight()   Video height as defined by sender
 * @method int       getDuration() Duration of the video in seconds as defined by sender
 * @method PhotoSize getThumb()    Optional. Video thumbnail
 * @method string    getMimeType() Optional. Mime type of a file as defined by sender
 * @method int       getFileSize() Optional. File size
 */
class Video extends Entity
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
