<?php


namespace Yangyao\TelegramBot\Entities\InputMessageContent;

use Yangyao\TelegramBot\Entities\InlineQuery\InlineEntity;

/**
 * Class InputContactMessageContent
 *
 * @link https://core.telegram.org/bots/api#inputcontactmessagecontent
 *
 * <code>
 * $data = [
 *   'phone_number' => '',
 *   'first_name'   => '',
 *   'last_name'    => '',
 * ];
 * </code>
 *
 * @method string getPhoneNumber() Contact's phone number
 * @method string getFirstName()   Contact's first name
 * @method string getLastName()    Optional. Contact's last name
 *
 * @method $this setPhoneNumber(string $phone_number) Contact's phone number
 * @method $this setFirstName(string $first_name)     Contact's first name
 * @method $this setLastName(string $last_name)       Optional. Contact's last name
 */
class InputContactMessageContent extends InlineEntity implements InputMessageContent
{

}
