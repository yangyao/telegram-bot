<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Console;


use Yangyao\TelegramBot\Console\BaseCommand as Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yangyao\TelegramBot\Exception\TelegramException;

class WebHookUnsetCommand extends Command
{
    public function configure()
    {
        $this->setName("webhook:unset")
            ->setDescription("unset webhook link for you bot !");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $result = $this->telegram->deleteWebhook();
            // To use a self-signed certificate, use this line instead
            //$result = $telegram->setWebhook($hook_url, ['certificate' => $certificate_path]);
            if ($result->isOk()) {
                $output->writeln("<info>".$result->getDescription()."</info>") ;
            }
        } catch (TelegramException $e) {
            $output->writeln("<error>".$e->getMessage()."</error>") ;
        }

    }

}