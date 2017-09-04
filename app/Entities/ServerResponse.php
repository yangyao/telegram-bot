<?php


namespace Yangyao\TelegramBot\Entities;

/**
 * Class ServerResponse
 *
 * @link https://core.telegram.org/bots/api#making-requests
 *
 * @method bool   getOk()          If the request was successful
 * @method mixed  getResult()      The result of the query
 * @method int    getErrorCode()   Error code of the unsuccessful request
 * @method string getDescription() Human-readable description of the result / unsuccessful request
 *
 * @todo method ResponseParameters getParameters()  Field which can help to automatically handle the error
 */
class ServerResponse extends Entity
{
    /**
     * ServerResponse constructor.
     *
     * @param array  $data
     * @param string $bot_username
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function __construct(array $data, $bot_username)
    {
        // Make sure we don't double-save the raw_data
        unset($data['raw_data']);
        $data['raw_data'] = $data;

        $is_ok  = isset($data['ok']) ? (bool) $data['ok'] : false;
        $result = isset($data['result']) ? $data['result'] : null;

        if ($is_ok && is_array($result)) {
            if ($this->isAssoc($result)) {
                $data['result'] = $this->createResultObject($result, $bot_username);
            } else {
                $data['result'] = $this->createResultObjects($result, $bot_username);
            }
        }

        parent::__construct($data, $bot_username);
    }

    /**
     * Check if array is associative
     *
     * @link https://stackoverflow.com/a/4254008
     *
     * @param array $array
     *
     * @return bool
     */
    protected function isAssoc(array $array)
    {
        return count(array_filter(array_keys($array), 'is_string')) > 0;
    }

    /**
     * If response is ok
     *
     * @return bool
     */
    public function isOk()
    {
        return (bool) $this->getOk();
    }

    /**
     * Print error
     *
     * @see https://secure.php.net/manual/en/function.print-r.php
     *
     * @param bool $return
     *
     * @return bool|string
     */
    public function printError($return = false)
    {
        $error = sprintf('Error N: %s, Description: %s', $this->getErrorCode(), $this->getDescription());

        if ($return) {
            return $error;
        }

        echo $error;

        return true;
    }

    /**
     * Create and return the object of the received result
     *
     * @param array  $result
     * @param string $bot_username
     *
     * @return \Yangyao\TelegramBot\Entities\Chat|\Yangyao\TelegramBot\Entities\ChatMember|\Yangyao\TelegramBot\Entities\File|\Yangyao\TelegramBot\Entities\Message|\Yangyao\TelegramBot\Entities\User|\Yangyao\TelegramBot\Entities\UserProfilePhotos|\Yangyao\TelegramBot\Entities\WebhookInfo
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    private function createResultObject($result, $bot_username)
    {
        // We don't need to save the raw_data of the response object!
        $result['raw_data'] = null;

        $result_object_types = [
            'total_count' => 'UserProfilePhotos', //Response from getUserProfilePhotos
            'file_id'     => 'File',              //Response from getFile
            'title'       => 'Chat',              //Response from getChat
            'username'    => 'User',              //Response from getMe
            'user'        => 'ChatMember',        //Response from getChatMember
            'url'         => 'WebhookInfo',       //Response from getWebhookInfo
        ];
        foreach ($result_object_types as $type => $object_class) {
            if (isset($result[$type])) {
                $object_class = __NAMESPACE__ . '\\' . $object_class;

                return new $object_class($result);
            }
        }

        //Response from sendMessage
        return new Message($result, $bot_username);
    }

    /**
     * Create and return the objects array of the received result
     *
     * @param array  $result
     * @param string $bot_username
     *
     * @return null|\Yangyao\TelegramBot\Entities\ChatMember[]|\Yangyao\TelegramBot\Entities\Update[]
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    private function createResultObjects($result, $bot_username)
    {
        $results = [];
        if (isset($result[0]['user'])) {
            //Response from getChatAdministrators
            foreach ($result as $user) {
                // We don't need to save the raw_data of the response object!
                $user['raw_data'] = null;

                $results[] = new ChatMember($user);
            }
        } else {
            //Get Update
            foreach ($result as $update) {
                // We don't need to save the raw_data of the response object!
                $update['raw_data'] = null;

                $results[] = new Update($update, $bot_username);
            }
        }

        return $results;
    }
}
