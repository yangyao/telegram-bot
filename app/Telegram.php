<?php


namespace Yangyao\TelegramBot;

use Yangyao\TelegramBot\Entities\Update;
use Yangyao\TelegramBot\Exception\TelegramException;
use GuzzleHttp\ClientInterface;
use Yangyao\TelegramBot\Database\Handler\HandlerInterface;
use Yangyao\TelegramBot\Commands\Schedule;

class Telegram
{
    /**
     * Version
     *
     * @var string
     */
    protected $version = '0.48.0';

    /**
     * Telegram API key
     *
     * @var string
     */
    protected $api_key = '';

    /**
     * Telegram Bot username
     *
     * @var string
     */
    protected $bot_username = '';

    /**
     * Telegram Bot id
     *
     * @var string
     */
    protected $bot_id = '';

    /**
     * Raw request data (json) for webhook methods
     *
     * @var string
     */
    protected $input;

    /**
     * Upload path
     *
     * @var string
     */
    protected $upload_path;

    /**
     * Download path
     *
     * @var string
     */
    protected $download_path;


    /**
     * The telegram api link
     *
     * @var string
     */
    public static $api_base_uri = 'https://api.telegram.org';


    /**@var Schedule $schedule*/
    private  $schedule = null;

    /**@var HandlerInterface $handler*/
    private $handler = null;

    /**
     * Telegram constructor.
     *
     * @param string $api_key
     * @param string $bot_username
     * @param ClientInterface $client
     * @param Schedule $schedule
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function __construct($api_key, $bot_username, ClientInterface $client, Schedule $schedule)
    {
        if (empty($api_key)) {
            throw new TelegramException('API KEY not defined!');
        }
        if (empty($bot_username)) {
            throw new TelegramException('BOT username not defined!');
        }
        preg_match('/(\d+)\:[\w\-]+/', $api_key, $matches);
        if (!isset($matches[1])) {
            throw new TelegramException('Invalid API KEY defined!');
        }
        $this->bot_id  = $matches[1];
        $this->api_key = $api_key;
        $this->bot_username = $bot_username;
        $this->schedule = $schedule;

        Request::initialize($this,$client);

    }

    public function getSchedule(){
        return $this->schedule;
    }

    public function setDatabaseHandler(HandlerInterface $handler){
        $this->handler = $handler;
    }

    /**
     * @return HandlerInterface
     */
    public function getDatabaseHandler(){
        return $this->handler;
    }

    /**
     * Handle getUpdates method
     *
     * @param int|null $limit
     * @param int|null $timeout
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function handleUpdates($limit = null, $timeout = null)
    {
        $id = $this->handler->getLastTelegramUpdateId();
        $offset = !is_null($id) ? $id + 1 : null;
        $response = Request::getUpdates(
            [
                'offset'  => $offset,
                'limit'   => $limit,
                'timeout' => $timeout,
            ]
        );
        if ($response->isOk()) {
            //Process all updates
            /** @var Update $result */
            foreach ((array) $response->getResult() as $result) {
                $this->processUpdate($result);
            }
        }

        return $response;
    }

    /**
     * Handle bot request from webhook
     *
     * @return bool
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function handleWebhook()
    {
        $this->input = Request::getInput();

        if (empty($this->input)) {
            throw new TelegramException('Input is empty!');
        }

        $post = json_decode($this->input, true);
        if (empty($post)) {
            throw new TelegramException('Invalid JSON!');
        }

        if ($response = $this->processUpdate(new Update($post, $this->bot_username))) {
            return $response->isOk();
        }

        return false;
    }

    /**
     * Process bot Update request
     *
     * @param \Yangyao\TelegramBot\Entities\Update $update
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function processUpdate(Update $update)
    {
        $this->handler->insertRequest($update);
        return $this->schedule->run($this,$update);
    }

    /**
     * Set custom upload path
     *
     * @param string $path Custom upload path
     *
     * @return \Yangyao\TelegramBot\Telegram
     */
    public function setUploadPath($path)
    {
        $this->upload_path = $path;

        return $this;
    }

    /**
     * Get custom upload path
     *
     * @return string
     */
    public function getUploadPath()
    {
        return $this->upload_path;
    }

    /**
     * Set custom download path
     *
     * @param string $path Custom download path
     *
     * @return \Yangyao\TelegramBot\Telegram
     */
    public function setDownloadPath($path)
    {
        $this->download_path = $path;

        return $this;
    }

    /**
     * Get custom download path
     *
     * @return string
     */
    public function getDownloadPath()
    {
        return $this->download_path;
    }



    /**
     * Get API key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * Get Bot name
     *
     * @return string
     */
    public function getBotUsername()
    {
        return $this->bot_username;
    }

    /**
     * Get Bot Id
     *
     * @return string
     */
    public function getBotId()
    {
        return $this->bot_id;
    }

    /**
     * Get Version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set Webhook for bot
     *
     * @param string $url
     * @param array  $data Optional parameters.
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function setWebhook($url, array $data = [])
    {
        if (empty($url)) {
            throw new TelegramException('Hook url is empty!');
        }

        $data        = array_intersect_key($data, array_flip([
            'certificate',
            'max_connections',
            'allowed_updates',
        ]));
        $data['url'] = $url;

        // If the certificate is passed as a path, encode and add the file to the data array.
        if (!empty($data['certificate']) && is_string($data['certificate'])) {
            $data['certificate'] = Request::encodeFile($data['certificate']);
        }

        $result = Request::setWebhook($data);

        if (!$result->isOk()) {
            throw new TelegramException(
                'Webhook was not set! Error: ' . $result->getErrorCode() . ' ' . $result->getDescription()
            );
        }

        return $result;
    }

    /**
     * Delete any assigned webhook
     *
     * @return mixed
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function deleteWebhook()
    {
        $result = Request::deleteWebhook();

        if (!$result->isOk()) {
            throw new TelegramException(
                'Webhook was not deleted! Error: ' . $result->getErrorCode() . ' ' . $result->getDescription()
            );
        }

        return $result;
    }


}
