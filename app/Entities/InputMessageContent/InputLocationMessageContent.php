<?php


namespace Yangyao\TelegramBot\Entities\InputMessageContent;

use Yangyao\TelegramBot\Entities\InlineQuery\InlineEntity;

/**
 * Class InputLocationMessageContent
 *
 * @link https://core.telegram.org/bots/api#inputlocationmessagecontent
 *
 * <code>
 * $data = [
 *   'latitude'  => 36.0338,
 *   'longitude' => 71.8601,
 * ];
 *
 * @method float getLatitude()  Latitude of the location in degrees
 * @method float getLongitude() Longitude of the location in degrees
 *
 * @method $this setLatitude(float $latitude)   Latitude of the location in degrees
 * @method $this setLongitude(float $longitude) Longitude of the location in degrees
 */
class InputLocationMessageContent extends InlineEntity implements InputMessageContent
{

}
