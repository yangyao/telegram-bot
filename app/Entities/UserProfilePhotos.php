<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class UserProfilePhotos
 *
 * @link https://core.telegram.org/bots/api#userprofilephotos
 *
 * @method int getTotalCount() Total number of profile pictures the target user has
 */
class UserProfilePhotos extends Entity
{
    /**
     * {@inheritdoc}
     */
    protected function subEntities()
    {
        return [
            'photos' => PhotoSize::class,
        ];
    }

    /**
     * Requested profile pictures (in up to 4 sizes each)
     *
     * This method overrides the default getPhotos method and returns a nice array
     *
     * @return PhotoSize[]
     */
    public function getPhotos()
    {
        $all_photos = [];

        if ($these_photos = $this->getProperty('photos')) {
            foreach ($these_photos as $photos) {
                $new_photos = [];
                foreach ($photos as $photo) {
                    $new_photos[] = new PhotoSize($photo);
                }
                $all_photos[] = $new_photos;
            }
        }

        return $all_photos;
    }
}
