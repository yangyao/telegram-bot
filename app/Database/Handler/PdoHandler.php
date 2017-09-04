<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Database\Handler;
use PDO;
use PDOException;
use Yangyao\TelegramBot\Exception\TelegramException;
use Yangyao\TelegramBot\Entities\Update;
use Yangyao\TelegramBot\Entities\InlineQuery;
use Exception;
use Yangyao\TelegramBot\Entities\Message;
use Yangyao\TelegramBot\Entities\Chat;
use Yangyao\TelegramBot\Entities\CallbackQuery;
use Yangyao\TelegramBot\Entities\User;
use Yangyao\TelegramBot\Entities\ReplyToMessage;
class PdoHandler implements HandlerInterface
{
    /**@var $db PDO*/
    protected  $db;
    /** @var string Table prefix */
     protected $table_prefix = null;
     protected $encoding = 'utf8mb4';

    const TB_CHAT = 'chat';
    const TB_USER = 'user';
    const TB_MESSAGE = 'message';
    const TB_USER_CHAT = 'user_chat';
    const TB_INLINE_QUERY = 'inline_query';
    const TB_EDITED_MESSAGE = 'edited_message';
    const TB_CALLBACK_QUERY = 'callback_query';
    const TB_REQUEST_LIMITER = 'request_limiter';
    const TB_TELEGRAM_UPDATE = 'telegram_update';
    const TB_CHOSEN_INLINE_RESULT = 'chosen_inline_result';

    public static $tables = [
        self::TB_CALLBACK_QUERY => 'callback_query',
        self::TB_CHAT => 'chat',
        self::TB_CHOSEN_INLINE_RESULT => 'chosen_inline_result',
        self::TB_EDITED_MESSAGE => 'edited_message',
        self::TB_INLINE_QUERY => 'inline_query',
        self::TB_MESSAGE => 'message',
        self::TB_REQUEST_LIMITER => 'request_limiter',
        self::TB_TELEGRAM_UPDATE => 'telegram_update',
        self::TB_USER => 'user',
        self::TB_USER_CHAT => 'user_chat',
    ];

    public function __construct(PDO $db) {
        $this->db = $db;
    }

    /**
     * @return null | int
     */
    public function getLastTelegramUpdateId(){
        $telegram_update = $this->getLastTelegramUpdate();
        if(!$telegram_update) return null;
        return $telegram_update['id'];
    }

    /**
     * Insert request into database
     *
     * @param \Yangyao\TelegramBot\Entities\Update $update
     *
     * @return bool
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertRequest(Update $update)
    {

        $update_id   = $update->getUpdateId();
        $update_type = $update->getUpdateType();

        $telegram_update = $this->findTelegramUpdateById($update_id);
        if ($telegram_update && is_array($telegram_update)) {
            throw new TelegramException('Duplicate update received!');
        }

        switch($update_type){
            case 'message':
                $message = $update->getMessage();
                $this->insertMessageRequest($message);
                $message_id = $message->getMessageId();
                $chat_id    = $message->getChat()->getId();
                return $this->insertTelegramUpdate($update_id, $chat_id, $message_id, null, null, null, null);
            case 'edited_message':
                $edited_message = $update->getEditedMessage();
                $edited_message_local_id =$this->insertEditedMessageRequest($edited_message);
                $chat_id = $edited_message->getChat()->getId();
                return $this->insertTelegramUpdate($update_id,$chat_id,null,null,null,null,$edited_message_local_id);
            case 'channel_post':
                $channel_post = $update->getChannelPost();
                $this->insertMessageRequest($channel_post);
                $message_id = $channel_post->getMessageId();
                $chat_id    = $channel_post->getChat()->getId();
                return $this->insertTelegramUpdate($update_id, $chat_id, $message_id, null, null, null, null);
            case 'edited_channel_post':
                $edited_channel_post = $update->getEditedChannelPost();
                $edited_channel_post_local_id = $this->insertEditedMessageRequest($edited_channel_post);
                $chat_id = $edited_channel_post->getChat()->getId();
                return $this->insertTelegramUpdate($update_id,$chat_id,null,null,null,null,$edited_channel_post_local_id);
            case 'inline_query':
                $inline_query = $update->getInlineQuery();
                $this->insertInlineQueryRequest($inline_query);
                $inline_query_id = $inline_query->getId();
                return $this->insertTelegramUpdate($update_id, null, null, $inline_query_id, null, null, null);
            case 'chosen_inline_result':
                $chosen_inline_result = $update->getChosenInlineResult();
                $chosen_inline_result_local_id = $this->insertChosenInlineResultRequest($chosen_inline_result);
                return $this->insertTelegramUpdate($update_id,null,null,null,$chosen_inline_result_local_id,null,null);
            case 'callback_query':
                $callback_query = $update->getCallbackQuery();
                $this->insertCallbackQueryRequest($callback_query);
                $callback_query_id = $callback_query->getId();
                return $this->insertTelegramUpdate($update_id, null, null, null, null, $callback_query_id, null);
        }

        return false;
    }

    /**
     * @throws TelegramException
     * @return array
     */
    public function getLastTelegramUpdate(){
        $sql = 'SELECT * FROM `' . self::TB_TELEGRAM_UPDATE . '` ORDER BY `id` DESC LIMIT 1';
        try{
            $sth = $this->db->prepare($sql);
            $sth->execute();
            return $sth->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * @param $id int
     * @return array
     * @throws TelegramException
     */
    public function findTelegramUpdateById($id){
        $sql = 'SELECT `id` FROM `' . self::TB_TELEGRAM_UPDATE . '` WHERE `id` = :id';
        try{
            $sth = $this->db->prepare($sql);
            $sth->bindParam(':id', $id, PDO::PARAM_STR);
            $sth->execute();
            return  $sth->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Convert from unix timestamp to timestamp
     *
     * @param int $time Unix timestamp (if null, current timestamp is used)
     *
     * @return string
     */
    protected static function getTimestamp($time = null)
    {
        if ($time === null) {
            $time = time();
        }

        return date('Y-m-d H:i:s', $time);
    }

    /**
     * Convert array of Entity items to a JSON array
     *
     * @todo Find a better way, as json_* functions are very heavy
     *
     * @param array|null $entities
     * @param mixed      $default
     *
     * @return mixed
     */
    public static function entitiesArrayToJson($entities, $default = null)
    {
        if (!is_array($entities)) {
            return $default;
        }

        //Convert each Entity item into an object based on its JSON reflection
        $json_entities = array_map(function ($entity) {
            return json_decode($entity, true);
        }, $entities);

        return json_encode($json_entities);
    }

    /**
     * Insert entry to telegram_update table
     *
     * @param int $id
     * @param int $chat_id
     * @param int $message_id
     * @param int $inline_query_id
     * @param int $chosen_inline_result_id
     * @param int $callback_query_id
     * @param int $edited_message_id
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertTelegramUpdate(
        $id,
        $chat_id,
        $message_id,
        $inline_query_id,
        $chosen_inline_result_id,
        $callback_query_id,
        $edited_message_id
    ) {
        if ($message_id === null && $inline_query_id === null && $chosen_inline_result_id === null && $callback_query_id === null && $edited_message_id === null) {
            throw new TelegramException('message_id, inline_query_id, chosen_inline_result_id, callback_query_id, edited_message_id are all null');
        }

        try {
            $sth = $this->db->prepare('
                INSERT INTO `' . self::TB_TELEGRAM_UPDATE . '`
                (`id`, `chat_id`, `message_id`, `inline_query_id`, `chosen_inline_result_id`, `callback_query_id`, `edited_message_id`)
                VALUES
                (:id, :chat_id, :message_id, :inline_query_id, :chosen_inline_result_id, :callback_query_id, :edited_message_id)
            ');

            $sth->bindParam(':id', $id, PDO::PARAM_STR);
            $sth->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);
            $sth->bindParam(':message_id', $message_id, PDO::PARAM_STR);
            $sth->bindParam(':inline_query_id', $inline_query_id, PDO::PARAM_STR);
            $sth->bindParam(':chosen_inline_result_id', $chosen_inline_result_id, PDO::PARAM_STR);
            $sth->bindParam(':callback_query_id', $callback_query_id, PDO::PARAM_STR);
            $sth->bindParam(':edited_message_id', $edited_message_id, PDO::PARAM_STR);

            return $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert users and save their connection to chats
     *
     * @param  \Yangyao\TelegramBot\Entities\User $user
     * @param  string                             $date
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertUser(User $user, $date)
    {

        $user_id        = $user->getId();
        $is_bot         = $user->getIsBot();
        $username       = $user->getUsername();
        $first_name     = $user->getFirstName();
        $last_name      = $user->getLastName();
        $language_code  = $user->getLanguageCode();

        try {
            $user = $this->findUserById($user_id);
            if(!$user){
                $sql = 'INSERT INTO `' . self::TB_USER . '`(`id`, `is_bot`, `username`, `first_name`, `last_name`, `language_code`, `created_at`, `updated_at`) VALUES (:id, :is_bot,:username, :first_name, :last_name, :language_code, :created_at, :updated_at)';
                return  $this->db->prepare($sql)->execute([
                    ':id'=> $user_id,
                    ':is_bot'=> $is_bot,
                    ':username'=> $username,
                    ':first_name'=> $first_name,
                    ':last_name'=> $last_name,
                    ':language_code'=> $language_code,
                    ':created_at'=> $date,
                    ':updated_at'=> $date,
                ]);
            }
            $sql = 'UPDATE `' . self::TB_USER . '` SET `updated_at` = :updated_at ,`first_name` = :first_name ,`last_name` = :last_name ,`language_code` = :language_code ';
            return  $this->db->prepare($sql)->execute([
                ':first_name'=> $first_name,
                ':last_name'=> $last_name,
                ':language_code'=> $language_code,
                ':updated_at'=> $date,
            ]);
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }


    /**
     * @param User $user
     * @param Chat $chat
     * @throws TelegramException
     * @return boolean
     */
    public function insertUserChat(User $user, Chat $chat){
        $user_id = $user->getId();
        $chat_id = $chat->getId();
        try {
            $user_chat = $this->findUserChat($user_id,$chat_id);
            if($user_chat) return true;
            $sql = 'INSERT INTO `' . self::TB_USER_CHAT . '`(`user_id`, `chat_id`) VALUES (:user_id, :chat_id)';
            return $this->db->prepare($sql)->execute([':user_id'=> $user_id,':chat_id'=> $chat_id,]);
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * @param $user_id
     * @param $chat_id
     * @return array
     */
    public function findUserChat($user_id,$chat_id){
        try{
            $sql = 'SELECT * FROM `' . self::TB_USER_CHAT . '` WHERE `user_id` = :user_id and `chat_id` = :chat_id ';
            $sth = $this->db->prepare($sql);
            $sth->execute([':user_id'=>$user_id,':chat_id'=>$chat_id]);
            return $sth->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * @param $id
     * @throws TelegramException
     * @return array
     */
    public function findUserById($id){
        $sql = 'SELECT * FROM `' . self::TB_USER . '` WHERE `id` = :id';
        try{
            $sth = $this->db->prepare($sql);
            $sth->execute([':id'=>$id]);
            return $sth->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert chat
     *
     * @param  \Yangyao\TelegramBot\Entities\Chat $chat
     * @param  string                             $date
     * @param  int                                $migrate_to_chat_id
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertChat(Chat $chat, $date, $migrate_to_chat_id = null)
    {

        $chat_id                             = $chat->getId();
        $chat_title                          = $chat->getTitle();
        $chat_username                       = $chat->getUsername();
        $chat_type                           = !is_null($migrate_to_chat_id)?'supergroup':$chat->getType();
        $chat_all_members_are_administrators = $chat->getAllMembersAreAdministrators();
        $id = !is_null($migrate_to_chat_id)?$migrate_to_chat_id:$chat_id;
        $oldid = !is_null($migrate_to_chat_id)?$chat_id:null;

        try {

            $chat = $this->getChatById($chat_id);
            if(!$chat){
                $sql = 'INSERT INTO `' . self::TB_CHAT . '`(`id`, `type`, `title`, `username`, `all_members_are_administrators`, `created_at` ,`updated_at`, `old_id`) VALUES (:id, :type, :title, :username, :all_members_are_administrators, :created_at, :updated_at, :oldid)';
                return $this->db->prepare($sql)->execute([
                    ':id'=> $id,
                    ':oldid'=> $oldid,
                    ':type'=> $chat_type,
                    ':title'=> $chat_title,
                    ':username'=> $chat_username,
                    ':all_members_are_administrators'=> $chat_all_members_are_administrators,
                    ':created_at'=> $date,
                    ':updated_at'=> $date,
                ]);

            }
            $sql = "UPDATE `".self::TB_CHAT."` SET `old_id` = :oldid , `title` =:title , `username` =:username , `all_members_are_administrators` = :all_members_are_administrators , `updated_at` = :updated_at WHERE `id` = ".$id." AND `type` = '".$chat_type."'";
            return   $this->db->prepare($sql)->execute([
                ':oldid'=> $oldid,
                ':title'=> $chat_title,
                ':username'=> $chat_username,
                ':all_members_are_administrators'=> $chat_all_members_are_administrators,
                ':updated_at'=> $date,
             ]);

        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * @param $chat_id
     * @throws TelegramException
     * @return array
     */
    public function getChatById($chat_id){

        $sql = 'SELECT * FROM `' . self::TB_CHAT . '` WHERE `id` = :id';
        try{
            $sth = $this->db->prepare($sql);
            $sth->execute([':id'=>$chat_id]);
            return $sth->fetch(PDO::FETCH_ASSOC);
        }catch (PDOException $e){
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert inline query request into database
     *
     * @param \Yangyao\TelegramBot\Entities\InlineQuery $inline_query
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertInlineQueryRequest(InlineQuery $inline_query)
    {

        try {
            $sth = $this->db->prepare('
                INSERT IGNORE INTO `' . self::TB_INLINE_QUERY . '`
                (`id`, `user_id`, `location`, `query`, `offset`, `created_at`)
                VALUES
                (:inline_query_id, :user_id, :location, :query, :param_offset, :created_at)
            ');

            $date            = $this->getTimestamp();
            $inline_query_id = $inline_query->getId();
            $from            = $inline_query->getFrom();
            $user_id         = null;
            if ($from instanceof User) {
                $user_id = $from->getId();
                $this->insertUser($from, $date);
            }

            $location = $inline_query->getLocation();
            $query    = $inline_query->getQuery();
            $offset   = $inline_query->getOffset();

            $sth->bindParam(':inline_query_id', $inline_query_id, PDO::PARAM_STR);
            $sth->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $sth->bindParam(':location', $location, PDO::PARAM_STR);
            $sth->bindParam(':query', $query, PDO::PARAM_STR);
            $sth->bindParam(':param_offset', $offset, PDO::PARAM_STR);
            $sth->bindParam(':created_at', $date, PDO::PARAM_STR);

            return $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert chosen inline result request into database
     *
     * @param \Yangyao\TelegramBot\Entities\ChosenInlineResult $chosen_inline_result
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertChosenInlineResultRequest(ChosenInlineResult $chosen_inline_result)
    {

        try {
            $sth = $this->db->prepare('
                INSERT INTO `' . self::TB_CHOSEN_INLINE_RESULT . '`
                (`result_id`, `user_id`, `location`, `inline_message_id`, `query`, `created_at`)
                VALUES
                (:result_id, :user_id, :location, :inline_message_id, :query, :created_at)
            ');

            $date      = $this->getTimestamp();
            $result_id = $chosen_inline_result->getResultId();
            $from      = $chosen_inline_result->getFrom();
            $user_id   = null;
            if ($from instanceof User) {
                $user_id = $from->getId();
                $this->insertUser($from, $date);
            }

            $location          = $chosen_inline_result->getLocation();
            $inline_message_id = $chosen_inline_result->getInlineMessageId();
            $query             = $chosen_inline_result->getQuery();

            $sth->bindParam(':result_id', $result_id, PDO::PARAM_STR);
            $sth->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $sth->bindParam(':location', $location, PDO::PARAM_STR);
            $sth->bindParam(':inline_message_id', $inline_message_id, PDO::PARAM_STR);
            $sth->bindParam(':query', $query, PDO::PARAM_STR);
            $sth->bindParam(':created_at', $date, PDO::PARAM_STR);
            $sth->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert callback query request into database
     *
     * @param \Yangyao\TelegramBot\Entities\CallbackQuery $callback_query
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertCallbackQueryRequest(CallbackQuery $callback_query)
    {

        try {
            $sth = $this->db->prepare('
                INSERT IGNORE INTO `' . self::TB_CALLBACK_QUERY . '`
                (`id`, `user_id`, `chat_id`, `message_id`, `inline_message_id`, `data`, `created_at`)
                VALUES
                (:callback_query_id, :user_id, :chat_id, :message_id, :inline_message_id, :data, :created_at)
            ');

            $date              = $this->getTimestamp();
            $callback_query_id = $callback_query->getId();
            $from              = $callback_query->getFrom();
            $user_id           = null;
            if ($from instanceof User) {
                $user_id = $from->getId();
                $this->insertUser($from, $date);
            }

            $message    = $callback_query->getMessage();
            $chat_id    = null;
            $message_id = null;
            if ($message instanceof Message) {
                $chat_id    = $message->getChat()->getId();
                $message_id = $message->getMessageId();

                $is_message = $this->db->query('
                    SELECT *
                    FROM `' . self::TB_MESSAGE . '`
                    WHERE `id` = ' . $message_id . '
                      AND `chat_id` = ' . $chat_id . '
                    LIMIT 1
                ')->rowCount();

                if ($is_message) {
                    $this->insertEditedMessageRequest($message);
                } else {
                    $this->insertMessageRequest($message);
                }
            }

            $inline_message_id = $callback_query->getInlineMessageId();
            $data              = $callback_query->getData();

            $sth->bindParam(':callback_query_id', $callback_query_id, PDO::PARAM_STR);
            $sth->bindParam(':user_id', $user_id, PDO::PARAM_STR);
            $sth->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);
            $sth->bindParam(':message_id', $message_id, PDO::PARAM_STR);
            $sth->bindParam(':inline_message_id', $inline_message_id, PDO::PARAM_STR);
            $sth->bindParam(':data', $data, PDO::PARAM_STR);
            $sth->bindParam(':created_at', $date, PDO::PARAM_STR);

            return $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert Message request in db
     *
     * @param \Yangyao\TelegramBot\Entities\Message $message
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertMessageRequest(Message $message)
    {
        $from = $message->getFrom();
        $chat = $message->getChat();

        $chat_id = $chat->getId();

        $date = $this->getTimestamp($message->getDate());

        $forward_from            = $message->getForwardFrom();
        $forward_from_chat       = $message->getForwardFromChat();
        $forward_from_message_id = $message->getForwardFromMessageId();
        $photo                   = $this->entitiesArrayToJson($message->getPhoto(), '');
        $entities                = $this->entitiesArrayToJson($message->getEntities(), null);
        $new_chat_members        = $message->getNewChatMembers();
        $left_chat_member        = $message->getLeftChatMember();
        $new_chat_photo          = $this->entitiesArrayToJson($message->getNewChatPhoto(), '');
        $migrate_to_chat_id      = $message->getMigrateToChatId();

        //Insert chat, update chat id in case it migrated
        $this->insertChat($chat, $date, $migrate_to_chat_id);

        //Insert user and the relation with the chat
        if (is_object($from)) {
            $this->insertUser($from, $date);
            $this->insertUserChat($from,$chat);
        }

        //Insert the forwarded message user in users table
        if ($forward_from instanceof User) {
            $forward_date = $this->getTimestamp($message->getForwardDate());
            $this->insertUser($forward_from, $forward_date);
            $forward_from = $forward_from->getId();
        }

        if ($forward_from_chat instanceof Chat) {
            $forward_date = $this->getTimestamp($message->getForwardDate());
            $this->insertChat($forward_from_chat, $forward_date);
            $forward_from_chat = $forward_from_chat->getId();
        }

        //New and left chat member
        if (!empty($new_chat_members)) {
            $new_chat_members_ids = [];
            foreach ($new_chat_members as $new_chat_member) {
                if ($new_chat_member instanceof User) {
                    //Insert the new chat user
                    $this->insertUser($new_chat_member, $date);
                    $this->insertUserChat($from,$chat);
                    $new_chat_members_ids[] = $new_chat_member->getId();
                }
            }
            $new_chat_members_ids = implode(',', $new_chat_members_ids);
        } elseif ($left_chat_member instanceof User) {
            //Insert the left chat user
            $this->insertUser($left_chat_member, $date);
            $this->insertUserChat($from,$chat);
            $left_chat_member = $left_chat_member->getId();
        }

        try {
            $sth = $this->db->prepare('
                INSERT INTO `' . self::TB_MESSAGE . '`
                (
                    `id`, `user_id`, `chat_id`, `date`, `forward_from`, `forward_from_chat`, `forward_from_message_id`,
                    `forward_date`, `reply_to_chat`, `reply_to_message`, `text`, `entities`, `audio`, `document`,
                    `photo`, `sticker`, `video`, `voice`, `video_note`, `caption`, `contact`,
                    `location`, `venue`, `new_chat_members`, `left_chat_member`,
                    `new_chat_title`,`new_chat_photo`, `delete_chat_photo`, `group_chat_created`,
                    `supergroup_chat_created`, `channel_chat_created`,
                    `migrate_from_chat_id`, `migrate_to_chat_id`, `pinned_message`
                ) VALUES (
                    :message_id, :user_id, :chat_id, :date, :forward_from, :forward_from_chat, :forward_from_message_id,
                    :forward_date, :reply_to_chat, :reply_to_message, :text, :entities, :audio, :document,
                    :photo, :sticker, :video, :voice, :video_note, :caption, :contact,
                    :location, :venue, :new_chat_members, :left_chat_member,
                    :new_chat_title, :new_chat_photo, :delete_chat_photo, :group_chat_created,
                    :supergroup_chat_created, :channel_chat_created,
                    :migrate_from_chat_id, :migrate_to_chat_id, :pinned_message
                )
            ');

            $message_id = $message->getMessageId();

            if (is_object($from)) {
                $from_id = $from->getId();
            } else {
                $from_id = null;
            }

            $reply_to_message    = $message->getReplyToMessage();
            $reply_to_message_id = null;
            if ($reply_to_message instanceof ReplyToMessage) {
                $reply_to_message_id = $reply_to_message->getMessageId();
                // please notice that, as explained in the documentation, reply_to_message don't contain other
                // reply_to_message field so recursion deep is 1
                $this->insertMessageRequest($reply_to_message);
            }

            $text                    = $message->getText();
            $audio                   = $message->getAudio();
            $document                = $message->getDocument();
            $sticker                 = $message->getSticker();
            $video                   = $message->getVideo();
            $voice                   = $message->getVoice();
            $video_note              = $message->getVideoNote();
            $caption                 = $message->getCaption();
            $contact                 = $message->getContact();
            $location                = $message->getLocation();
            $venue                   = $message->getVenue();
            $new_chat_title          = $message->getNewChatTitle();
            $delete_chat_photo       = $message->getDeleteChatPhoto();
            $group_chat_created      = $message->getGroupChatCreated();
            $supergroup_chat_created = $message->getSupergroupChatCreated();
            $channel_chat_created    = $message->getChannelChatCreated();
            $migrate_from_chat_id    = $message->getMigrateFromChatId();
            $migrate_to_chat_id      = $message->getMigrateToChatId();
            $pinned_message          = $message->getPinnedMessage();

            $sth->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);
            $sth->bindParam(':message_id', $message_id, PDO::PARAM_STR);
            $sth->bindParam(':user_id', $from_id, PDO::PARAM_STR);
            $sth->bindParam(':date', $date, PDO::PARAM_STR);
            $sth->bindParam(':forward_from', $forward_from, PDO::PARAM_STR);
            $sth->bindParam(':forward_from_chat', $forward_from_chat, PDO::PARAM_STR);
            $sth->bindParam(':forward_from_message_id', $forward_from_message_id, PDO::PARAM_STR);
            $sth->bindParam(':forward_date', $forward_date, PDO::PARAM_STR);

            $reply_to_chat_id = null;
            if ($reply_to_message_id) {
                $reply_to_chat_id = $chat_id;
            }

            $sth->bindParam(':reply_to_chat', $reply_to_chat_id, PDO::PARAM_STR);
            $sth->bindParam(':reply_to_message', $reply_to_message_id, PDO::PARAM_STR);
            $sth->bindParam(':text', $text, PDO::PARAM_STR);
            $sth->bindParam(':entities', $entities, PDO::PARAM_STR);
            $sth->bindParam(':audio', $audio, PDO::PARAM_STR);
            $sth->bindParam(':document', $document, PDO::PARAM_STR);
            $sth->bindParam(':photo', $photo, PDO::PARAM_STR);
            $sth->bindParam(':sticker', $sticker, PDO::PARAM_STR);
            $sth->bindParam(':video', $video, PDO::PARAM_STR);
            $sth->bindParam(':voice', $voice, PDO::PARAM_STR);
            $sth->bindParam(':video_note', $video_note, PDO::PARAM_STR);
            $sth->bindParam(':caption', $caption, PDO::PARAM_STR);
            $sth->bindParam(':contact', $contact, PDO::PARAM_STR);
            $sth->bindParam(':location', $location, PDO::PARAM_STR);
            $sth->bindParam(':venue', $venue, PDO::PARAM_STR);
            $sth->bindParam(':new_chat_members', $new_chat_members_ids, PDO::PARAM_STR);
            $sth->bindParam(':left_chat_member', $left_chat_member, PDO::PARAM_STR);
            $sth->bindParam(':new_chat_title', $new_chat_title, PDO::PARAM_STR);
            $sth->bindParam(':new_chat_photo', $new_chat_photo, PDO::PARAM_STR);
            $sth->bindParam(':delete_chat_photo', $delete_chat_photo, PDO::PARAM_INT);
            $sth->bindParam(':group_chat_created', $group_chat_created, PDO::PARAM_INT);
            $sth->bindParam(':supergroup_chat_created', $supergroup_chat_created, PDO::PARAM_INT);
            $sth->bindParam(':channel_chat_created', $channel_chat_created, PDO::PARAM_INT);
            $sth->bindParam(':migrate_from_chat_id', $migrate_from_chat_id, PDO::PARAM_STR);
            $sth->bindParam(':migrate_to_chat_id', $migrate_to_chat_id, PDO::PARAM_STR);
            $sth->bindParam(':pinned_message', $pinned_message, PDO::PARAM_STR);

            return $sth->execute();
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert Edited Message request in db
     *
     * @param \Yangyao\TelegramBot\Entities\Message $edited_message
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertEditedMessageRequest(Message $edited_message)
    {

        $from = $edited_message->getFrom();
        $chat = $edited_message->getChat();

        $chat_id = $chat->getId();

        $edit_date = $this->getTimestamp($edited_message->getEditDate());

        $entities = $this->entitiesArrayToJson($edited_message->getEntities(), null);

        //Insert chat
        $this->insertChat($chat, $edit_date);

        //Insert user and the relation with the chat
        if (is_object($from)) {
            $this->insertUser($from, $edit_date);
            $this->insertUserChat($from,$chat);
        }

        try {
            $sth = $this->db->prepare('
                INSERT IGNORE INTO `' . self::TB_EDITED_MESSAGE . '`
                (`chat_id`, `message_id`, `user_id`, `edit_date`, `text`, `entities`, `caption`)
                VALUES
                (:chat_id, :message_id, :user_id, :edit_date, :text, :entities, :caption)
            ');

            $message_id = $edited_message->getMessageId();

            if (is_object($from)) {
                $from_id = $from->getId();
            } else {
                $from_id = null;
            }

            $text    = $edited_message->getText();
            $caption = $edited_message->getCaption();

            $sth->bindParam(':chat_id', $chat_id, PDO::PARAM_STR);
            $sth->bindParam(':message_id', $message_id, PDO::PARAM_STR);
            $sth->bindParam(':user_id', $from_id, PDO::PARAM_STR);
            $sth->bindParam(':edit_date', $edit_date, PDO::PARAM_STR);
            $sth->bindParam(':text', $text, PDO::PARAM_STR);
            $sth->bindParam(':entities', $entities, PDO::PARAM_STR);
            $sth->bindParam(':caption', $caption, PDO::PARAM_STR);

            $sth->execute();
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Select Groups, Supergroups, Channels and/or single user Chats (also by ID or text)
     *
     * @param $select_chats_params
     *
     * @return array|bool
     * @throws TelegramException
     */
    public function selectChats($select_chats_params)
    {
        // Set defaults for omitted values.
        $select = array_merge([
            'groups'      => true,
            'supergroups' => true,
            'channels'    => true,
            'users'       => true,
            'date_from'   => null,
            'date_to'     => null,
            'chat_id'     => null,
            'text'        => null,
        ], $select_chats_params);

        if (!$select['groups'] && !$select['users'] && !$select['supergroups']) {
            return false;
        }

        try {
            $query = '
                SELECT * ,
                ' . self::TB_CHAT . '.`id` AS `chat_id`,
                ' . self::TB_CHAT . '.`username` AS `chat_username`,
                ' . self::TB_CHAT . '.`created_at` AS `chat_created_at`,
                ' . self::TB_CHAT . '.`updated_at` AS `chat_updated_at`
            ';
            if ($select['users']) {
                $query .= '
                    , ' . self::TB_USER . '.`id` AS `user_id`
                    FROM `' . self::TB_CHAT . '`
                    LEFT JOIN `' . self::TB_USER . '`
                    ON ' . self::TB_CHAT . '.`id`=' . self::TB_USER . '.`id`
                ';
            } else {
                $query .= 'FROM `' . self::TB_CHAT . '`';
            }

            //Building parts of query
            $where  = [];
            $tokens = [];

            if (!$select['groups'] || !$select['users'] || !$select['supergroups']) {
                $chat_or_user = [];

                $select['groups'] && $chat_or_user[] = self::TB_CHAT . '.`type` = "group"';
                $select['supergroups'] && $chat_or_user[] = self::TB_CHAT . '.`type` = "supergroup"';
                $select['channels'] && $chat_or_user[] = self::TB_CHAT . '.`type` = "channel"';
                $select['users'] && $chat_or_user[] = self::TB_CHAT . '.`type` = "private"';

                $where[] = '(' . implode(' OR ', $chat_or_user) . ')';
            }

            if (null !== $select['date_from']) {
                $where[]              = self::TB_CHAT . '.`updated_at` >= :date_from';
                $tokens[':date_from'] = $select['date_from'];
            }

            if (null !== $select['date_to']) {
                $where[]            = self::TB_CHAT . '.`updated_at` <= :date_to';
                $tokens[':date_to'] = $select['date_to'];
            }

            if (null !== $select['chat_id']) {
                $where[]            = self::TB_CHAT . '.`id` = :chat_id';
                $tokens[':chat_id'] = $select['chat_id'];
            }

            if (null !== $select['text']) {
                if ($select['users']) {
                    $where[] = '(
                        LOWER(' . self::TB_CHAT . '.`title`) LIKE :text
                        OR LOWER(' . self::TB_USER . '.`first_name`) LIKE :text
                        OR LOWER(' . self::TB_USER . '.`last_name`) LIKE :text
                        OR LOWER(' . self::TB_USER . '.`username`) LIKE :text
                    )';
                } else {
                    $where[] = 'LOWER(' . self::TB_CHAT . '.`title`) LIKE :text';
                }
                $tokens[':text'] = '%' . strtolower($select['text']) . '%';
            }

            if (!empty($where)) {
                $query .= ' WHERE ' . implode(' AND ', $where);
            }

            $query .= ' ORDER BY ' . self::TB_CHAT . '.`updated_at` ASC';

            $sth = $this->db->prepare($query);
            $sth->execute($tokens);

            return $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Get Telegram API request count for current chat / message
     *
     * @param integer $chat_id
     * @param string  $inline_message_id
     *
     * @return array|bool (Array containing TOTAL and CURRENT fields or false on invalid arguments)
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function getTelegramRequestCount($chat_id = null, $inline_message_id = null)
    {
        try {
            $sth = $this->db->prepare('SELECT 
                (SELECT COUNT(DISTINCT `chat_id`) FROM `' . self::TB_REQUEST_LIMITER . '` WHERE `created_at` >= :created_at_1) as LIMIT_PER_SEC_ALL,
                (SELECT COUNT(*) FROM `' . self::TB_REQUEST_LIMITER . '` WHERE `created_at` >= :created_at_2 AND ((`chat_id` = :chat_id_1 AND `inline_message_id` IS NULL) OR (`inline_message_id` = :inline_message_id AND `chat_id` IS NULL))) as LIMIT_PER_SEC,
                (SELECT COUNT(*) FROM `' . self::TB_REQUEST_LIMITER . '` WHERE `created_at` >= :created_at_minute AND `chat_id` = :chat_id_2) as LIMIT_PER_MINUTE
            ');

            $date = $this->getTimestamp();
            $date_minute = $this->getTimestamp(strtotime('-1 minute'));

            $sth->bindParam(':chat_id_1', $chat_id, \PDO::PARAM_STR);
            $sth->bindParam(':chat_id_2', $chat_id, \PDO::PARAM_STR);
            $sth->bindParam(':inline_message_id', $inline_message_id, \PDO::PARAM_STR);
            $sth->bindParam(':created_at_1', $date, \PDO::PARAM_STR);
            $sth->bindParam(':created_at_2', $date, \PDO::PARAM_STR);
            $sth->bindParam(':created_at_minute', $date_minute, \PDO::PARAM_STR);

            $sth->execute();

            return $sth->fetch();
        } catch (\Exception $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }

    /**
     * Insert Telegram API request in db
     *
     * @param string $method
     * @param array  $data
     *
     * @return bool If the insert was successful
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertTelegramRequest($method, $data)
    {
        $chat_id = ((isset($data['chat_id'])) ? $data['chat_id'] : null);
        $inline_message_id = (isset($data['inline_message_id']) ? $data['inline_message_id'] : null);

        try {
            $sth = $this->db->prepare('INSERT INTO `' . self::TB_REQUEST_LIMITER . '`
                (
                `method`, `chat_id`, `inline_message_id`, `created_at`
                )
                VALUES (
                :method, :chat_id, :inline_message_id, :created_at
                );
            ');

            $created_at = $this->getTimestamp();

            $sth->bindParam(':chat_id', $chat_id, \PDO::PARAM_STR);
            $sth->bindParam(':inline_message_id', $inline_message_id, \PDO::PARAM_STR);
            $sth->bindParam(':method', $method, \PDO::PARAM_STR);
            $sth->bindParam(':created_at', $created_at, \PDO::PARAM_STR);

            return $sth->execute();
        } catch (\Exception $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
    }


    /**
     * Select a conversation from the DB
     *
     * @param int  $user_id
     * @param int  $chat_id
     * @param bool $limit
     *
     * @return array|bool
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function selectConversation($user_id, $chat_id, $limit = null)
    {

        try {
            $query = 'SELECT * FROM `' . self::TB_CONVERSATION . '` ';
            $query .= 'WHERE `status` = :status ';
            $query .= 'AND `chat_id` = :chat_id ';
            $query .= 'AND `user_id` = :user_id ';

            if ($limit !== null) {
                $query .= ' LIMIT :limit';
            }
            $sth = $this->db->prepare($query);

            $status = 'active';
            $sth->bindParam(':status', $status);
            $sth->bindParam(':user_id', $user_id);
            $sth->bindParam(':chat_id', $chat_id);
            $sth->bindParam(':limit', $limit, PDO::PARAM_INT);
            $sth->execute();

            $results = $sth->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
        return $results;
    }

    /**
     * Insert the conversation in the database
     *
     * @param int    $user_id
     * @param int    $chat_id
     * @param string $command
     *
     * @return bool
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function insertConversation($user_id, $chat_id, $command)
    {

        try {
            $sth = $this->db->prepare('INSERT INTO `' . self::TB_CONVERSATION . '`
                (
                `status`, `user_id`, `chat_id`, `command`, `notes`, `created_at`, `updated_at`
                )
                VALUES (
                :status, :user_id, :chat_id, :command, :notes, :created_at, :updated_at
                )
            ');

            $status = 'active';
            $notes  = '[]';
            $date   = $this->getTimestamp();

            $sth->bindParam(':status', $status);
            $sth->bindParam(':command', $command);
            $sth->bindParam(':user_id', $user_id);
            $sth->bindParam(':chat_id', $chat_id);
            $sth->bindParam(':notes', $notes);
            $sth->bindParam(':created_at', $date);
            $sth->bindParam(':updated_at', $date);

            $status = $sth->execute();
        } catch (Exception $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
        return $status;
    }

    /**
     * Update a specific conversation
     *
     * @param array $fields_values
     * @param array $where_fields_values
     *
     * @return bool
     */
    public  function updateConversation(array $fields_values, array $where_fields_values)
    {
        return $this->update(self::TB_CONVERSATION, $fields_values, $where_fields_values);
    }

    /**
     * Update the conversation in the database
     *
     * @param string $table
     * @param array  $fields_values
     * @param array  $where_fields_values
     *
     * @todo This function is generic should be moved in DB.php
     *
     * @return bool
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function update($table, array $fields_values, array $where_fields_values)
    {
        //Auto update the field update_at
        $fields_values['updated_at'] = $this->getTimestamp();

        //Values
        $update         = '';
        $tokens         = [];
        $tokens_counter = 0;
        $a              = 0;
        foreach ($fields_values as $field => $value) {
            if ($a) {
                $update .= ', ';
            }
            ++$a;
            ++$tokens_counter;
            $update .= '`' . $field . '` = :' . $tokens_counter;
            $tokens[':' . $tokens_counter] = $value;
        }

        //Where
        $a     = 0;
        $where = '';
        foreach ($where_fields_values as $field => $value) {
            if ($a) {
                $where .= ' AND ';
            } else {
                ++$a;
                $where .= 'WHERE ';
            }
            ++$tokens_counter;
            $where .= '`' . $field . '`= :' . $tokens_counter;
            $tokens[':' . $tokens_counter] = $value;
        }

        $query = 'UPDATE `' . $table . '` SET ' . $update . ' ' . $where;
        try {
            $sth    = $this->db->prepare($query);
            $status = $sth->execute($tokens);
        } catch (\Exception $e) {
            throw new TelegramException(__METHOD__.$e->getMessage());
        }
        return $status;
    }
}