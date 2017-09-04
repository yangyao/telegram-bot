<?php
// +----------------------------------------------------------------------
// | Author: 杨尧 <yangyao@sailvan.com>
// +----------------------------------------------------------------------

namespace Yangyao\TelegramBot\Console;


use Yangyao\TelegramBot\Console\BaseCommand as Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Yangyao\TelegramBot\Exception\TelegramException;

class WebHookSetCommand extends Command
{
    public function configure()
    {
        $this->setName("webhook:set")
            ->setDescription("set a webhook link for you bot !")
            ->addArgument("link",InputArgument::REQUIRED,"please enter a webhook link for you bot");
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $result = $this->telegram->setWebhook($input->getArgument('link'));
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