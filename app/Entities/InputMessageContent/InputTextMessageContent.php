<?php


namespace Yangyao\TelegramBot\Entities\InputMessageContent;

use Yangyao\TelegramBot\Entities\InlineQuery\InlineEntity;

/**
 * Class InputTextMessageContent
 *
 * @link https://core.telegram.org/bots/api#inputtextmessagecontent
 *
 * <code>
 * $data = [
 *   'message_text'             => '',
 *   'parse_mode'               => '',
 *   'disable_web_page_preview' => true,
 * ];
 * </code>
 *
 * @method string getMessageText()           Text of the message to be sent, 1-4096 characters.
 * @method string getParseMode()             Optional. Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in your bot's message.
 * @method bool   getDisableWebPagePreview() Optional. Disables link previews for links in the sent message
 *
 * @method $this setMessageText(string $message_text)                     Text of the message to be sent, 1-4096 characters.
 * @method $this setParseMode(string $parse_mode)                         Optional. Send Markdown or HTML, if you want Telegram apps to show bold, italic, fixed-width text or inline URLs in your bot's message.
 * @method $this setDisableWebPagePreview(bool $disable_web_page_preview) Optional. Disables link previews for links in the sent message
 */
class InputTextMessageContent extends InlineEntity implements InputMessageContent
{

}
