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


    /**@var ClientInterface $client*/
    private  $client = null;

    /**@var HandlerInterface $handler*/
    private $handler = null;

    /**
     * Telegram constructor.
     *
     * @param string $api_key
     * @param string $bot_username
     *
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function __construct($api_key, $bot_username)
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
    }

    public function setSchedule(Schedule $schedule){
        $this->schedule = $schedule;
    }

    public function getSchedule(){
        return $this->schedule;
    }

    public function setDatabaseHandler(HandlerInterface $handler){
        $this->handler = $handler;
    }

    public function getDatabaseHandler(){
        return $this->handler;
    }

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

    public function processUpdate(Update $update)
    {
        $this->handler->insertRequest($update);
        return $this->schedule->run($this,$update);
    }

    public function setUploadPath($path)
    {
        $this->upload_path = $path;

        return $this;
    }

    public function getUploadPath()
    {
        return $this->upload_path;
    }

    public function setDownloadPath($path)
    {
        $this->download_path = $path;

        return $this;
    }

    public function getDownloadPath()
    {
        return $this->download_path;
    }

    public function getApiKey()
    {
        return $this->api_key;
    }

    public function getBotUsername()
    {
        return $this->bot_username;
    }

    public function getBotId()
    {
        return $this->bot_id;
    }

    public function getVersion()
    {
        return $this->version;
    }

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
