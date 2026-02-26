<?php
   namespace App\Command;

   use Symfony\Component\Console\Command\Command;
   use Symfony\Component\Console\Input\InputInterface;
   use Symfony\Component\Console\Output\OutputInterface;
   use Symfony\Component\Mailer\MailerInterface;
   use Symfony\Component\Mime\Email;

   class TestEmailCommand extends Command
   {
       protected static $defaultName = 'app:test-email';
       private MailerInterface $mailer;

       public function __construct(MailerInterface $mailer)
       {
           $this->mailer = $mailer;
           parent::__construct();
       }

        protected function configure(): void
        {
            $this
                ->setName('app:test-email') // Important pour définir le nom de manière explicite
                ->setDescription('Envoie un email de test pour vérifier la configuration de Mailer');
        }

       protected function execute(InputInterface $input, OutputInterface $output): int
       {
           $email = (new Email())
               ->from('test@example.com')
               ->to('recipient@example.com')
               ->subject('Test Email')
               ->text('This is a test email.');

           try {
               $this->mailer->send($email);
               $output->writeln('Email envoyé avec succès.');
           } catch (\Exception $e) {
               $output->writeln('Erreur : ' . $e->getMessage());
           }

           $output->writeln('MAILER_DSN: ' . $_ENV['MAILER_DSN']);

           return Command::SUCCESS;
       }
   }