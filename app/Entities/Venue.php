<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class Venue
 *
 * @link https://core.telegram.org/bots/api#venue
 *
 * @method Location getLocation()     Venue location
 * @method string   getTitle()        Name of the venue
 * @method string   getAddress()      Address of the venue
 * @method string   getFoursquareId() Optional. Foursquare identifier of the venue
 */
class Venue extends Entity
{
    /**
     * {@inheritdoc}
     */
    protected function subEntities()
    {
        return [
            'location' => Location::class,
        ];
    }
}
