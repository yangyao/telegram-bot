<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class Document
 *
 * @link https://core.telegram.org/bots/api#document
 *
 * @method string    getFileId()   Unique file identifier
 * @method PhotoSize getThumb()    Optional. Document thumbnail as defined by sender
 * @method string    getFileName() Optional. Original filename as defined by sender
 * @method string    getMimeType() Optional. MIME type of the file as defined by sender
 * @method int       getFileSize() Optional. File size
 */
class Document extends Entity
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
