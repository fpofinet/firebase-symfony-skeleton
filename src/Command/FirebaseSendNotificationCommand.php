<?php
namespace App\Command;

use App\Service\FirebaseMessagingService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:firebase-send',
    description: 'Envoie une notification FCM à un appareil Android'
)]
/**
 * Summary of FirebaseSendNotificationCommand
 * cette commande a pour but de tester l'envoi de nofication avec firebase
 * elle prend en argument et dans l'ordre : le device_token, le titre et le body de la notification
 * exmple d'appel : php bin/console app:firebase-send "DEVICE_TOKEN" "Titre" "Message personnalisé"
 */
class FirebaseSendNotificationCommand extends Command
{
    public function __construct(
        private readonly FirebaseMessagingService $firebase
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('token', InputArgument::REQUIRED, 'Le device token Android')
            ->addArgument('title', InputArgument::OPTIONAL, 'Titre de la notification', 'Hello')
            ->addArgument('body', InputArgument::OPTIONAL, 'Contenu de la notification', 'Message envoyé depuis Symfony !');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $token = $input->getArgument('token');
        $title = $input->getArgument('title');
        $body = $input->getArgument('body');

        try {
            $this->firebase->sendToDevice($token, $title, $body);
            $output->writeln('<info>✅ Notification envoyée avec succès.</info>');
        } catch (\Throwable $e) {
            $output->writeln('<error>❌ Échec : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
