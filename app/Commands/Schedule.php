<?php
/**
 * Created by PhpStorm.
 * User: yangy
 * Date: 2017/9/3
 * Time: 21:51
 */

namespace Yangyao\TelegramBot\Commands;


use Yangyao\TelegramBot\Entities\Update;
use Yangyao\TelegramBot\Exception\TelegramException;
use Yangyao\TelegramBot\Telegram;

class Schedule {


    /**@var string $command_namespace*/
    private $command_namespace;

    /**
     * Commands config
     *
     * @var array
     */
    protected $commands_config = [];

    /**
     * Admins list
     *
     * @var array
     */
    protected $admins_list = [];

    /**
     * ServerResponse of the last Command execution
     *
     * @var \Yangyao\TelegramBot\Entities\ServerResponse
     */
    protected $last_command_response;


    /**@var Update $update*/
    public $update = null;

    /**@var Telegram $telegram*/
    public $telegram = null;


    /**
     * Check if runCommands() is running in this session
     *
     * @var boolean
     */
    protected $run_commands = false;


    public function run(Telegram $telegram,Update $update){
        $this->update = $update;
        $this->telegram = $telegram;
        $command_name = $this->getCommandName();
        return $this->executeCommand($command_name);
    }



    private  function getCommandName(){
        //If all else fails, it's a generic message.
        $command = 'genericmessage';

        $update_type = $this->update->getUpdateType();
        if (in_array($update_type, ['edited_message', 'channel_post', 'edited_channel_post', 'inline_query', 'chosen_inline_result', 'callback_query'], true)) {
            $command = $this->getCommandFromType($update_type);
        } elseif ($update_type === 'message') {
            $message = $this->update->getMessage();

            $type = $message->getType();
            if ($type === 'command') {
                $command = $message->getCommand();
            } elseif (in_array($type, [
                'channel_chat_created',
                'delete_chat_photo',
                'group_chat_created',
                'left_chat_member',
                'migrate_from_chat_id',
                'migrate_to_chat_id',
                'new_chat_members',
                'new_chat_photo',
                'new_chat_title',
                'pinned_message',
                'supergroup_chat_created',
            ], true)
            ) {
                $command = $this->getCommandFromType($type);
            }
        }

        return $command;

    }


    /**
     * Get the command name from the command type
     *
     * @param string $type
     *
     * @return string
     */
    protected function getCommandFromType($type)
    {
        return $this->ucfirstUnicode(str_replace('_', '', $type));
    }

    /**
     * Replace function `ucfirst` for UTF-8 characters in the class definition and commands
     *
     * @param string $str
     * @param string $encoding (default = 'UTF-8')
     *
     * @return string
     */
    protected function ucfirstUnicode($str, $encoding = 'UTF-8')
    {
        return
            mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding)
            . mb_strtolower(mb_substr($str, 1, mb_strlen($str), $encoding), $encoding);
    }


    /**
     * Get commands list
     *
     * @param Registry $registry
     * @return array $commands
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function getCommandsList(Registry $registry)
    {
        $commands = [];
        /**@var \Yangyao\TelegramBot\Commands\Command $command */
        foreach ($registry->command_list as $command) {
            $commands[$command->getName()] = $command;
        }
        return $commands;
    }

    /**
     * Get an object instance of the passed command
     * todo notice 优先取得系统命令，然后取得admin命令，最后是用户命令？奇怪的逻辑
     *
     * @param string $command
     *
     * @return \Yangyao\TelegramBot\Commands\Command|null
     */
    private  function getCommandObject($command)
    {
        $which = ['System'];
        $this->isAdmin($this->update->getUserId()) && $which[] = 'Admin';
        $which[] = 'User';

        foreach ($which as $auth) {
            $builtin_command_namespace = __NAMESPACE__ . '\\Commands\\' . $auth . 'Commands\\' . $this->ucfirstUnicode($command) . 'Command';
            $command_namespace = $this->command_namespace. $auth . 'Commands\\' . $this->ucfirstUnicode($command) . 'Command';
            if (class_exists($command_namespace)) {
                return new $command_namespace($this->telegram, $this->update);
            }elseif(class_exists($builtin_command_namespace)){
                return new $builtin_command_namespace($this->telegram, $this->update);
            }
        }
        return null;
    }
    /**
     * Get the ServerResponse of the last Command execution
     *
     * @return \Yangyao\TelegramBot\Entities\ServerResponse
     */
    public function getLastCommandResponse()
    {
        return $this->last_command_response;
    }

    /**
     * Execute /command
     *
     * @param string $command
     *
     * @return mixed
     * @throws \Yangyao\TelegramBot\Exception\TelegramException
     */
    public function executeCommand($command)
    {
        $command     = strtolower($command);
        $command_obj = $this->getCommandObject($command);

        if (!$command_obj || !$command_obj->isEnabled()) {
            //Failsafe in case the Generic command can't be found
            if ($command === 'generic') {
                throw new TelegramException('Generic command missing!');
            }

            //Handle a generic command or non existing one
            $this->last_command_response = $this->executeCommand('generic');
        } else {
            //execute() method is executed after preExecute()
            //This is to prevent executing a DB query without a valid connection
            $this->last_command_response = $command_obj->preExecute();
        }

        return $this->last_command_response;
    }

    /**
     * Sanitize Command
     *
     * @param string $command
     *
     * @return string
     */
    protected function sanitizeCommand($command)
    {
        return str_replace(' ', '', $this->ucwordsUnicode(str_replace('_', ' ', $command)));
    }


    /**
     * Is this session initiated by runCommands()
     *
     * @return bool
     */
    public function isRunCommands()
    {
        return $this->run_commands;
    }

    /**
     * Set command config
     *
     * Provide further variables to a particular commands.
     * For example you can add the channel name at the command /sendtochannel
     * Or you can add the api key for external service.
     *
     * @param string $command
     * @param array  $config
     *
     * @return \Yangyao\TelegramBot\Telegram
     */
    public function setCommandConfig($command, array $config)
    {
        $this->commands_config[$command] = $config;

        return $this;
    }

    /**
     * Get command config
     *
     * @param string $command
     *
     * @return array
     */
    public function getCommandConfig($command)
    {
        return isset($this->commands_config[$command]) ? $this->commands_config[$command] : [];
    }

    /**
     * Get list of admins
     *
     * @return array
     */
    public function getAdminList()
    {
        return $this->admins_list;
    }

    /**
     * Check if the passed user is an admin
     *
     * If no user id is passed, the current update is checked for a valid message sender.
     *
     * @param int|null $user_id
     *
     * @return bool
     */
    public function isAdmin($user_id)
    {
        if(is_null($user_id)) return false;
        return in_array($user_id, $this->admins_list, true);
    }


    public function setCommandNamespace($namespace){
        $this->command_namespace = $namespace;
    }
    /**
     * Enable a single Admin account
     *
     * @param integer $admin_id Single admin id
     *
     * @return \Yangyao\TelegramBot\Telegram
     */
    public function enableAdmin($admin_id)
    {
        if (!is_int($admin_id) || $admin_id <= 0) {
        } elseif (!in_array($admin_id, $this->admins_list, true)) {
            $this->admins_list[] = $admin_id;
        }

        return $this;
    }

    /**
     * Enable a list of Admin Accounts
     *
     * @param array $admin_ids List of admin ids
     *
     * @return \Yangyao\TelegramBot\Telegram
     */
    public function enableAdmins(array $admin_ids)
    {
        foreach ($admin_ids as $admin_id) {
            $this->enableAdmin($admin_id);
        }

        return $this;
    }



    /**
     * Replace function `ucwords` for UTF-8 characters in the class definition and commands
     *
     * @param string $str
     * @param string $encoding (default = 'UTF-8')
     *
     * @return string
     */
    protected function ucwordsUnicode($str, $encoding = 'UTF-8')
    {
        return mb_convert_case($str, MB_CASE_TITLE, $encoding);
    }

} 