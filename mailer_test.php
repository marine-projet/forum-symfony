<?php

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mime\Email;

require 'vendor/autoload.php'; // Assurez-vous d’exécuter dans le bon contexte.

$dsn = 'gmail://contact.marine1991@gmail.com:rpipwzxnyczsibse@default';
$transport = Transport::fromDsn($dsn);
$mailer = new Mailer($transport);

$email = (new Email())
    ->from('contact.marine1991@gmail.com')
    ->to('mignomarine@gmail.com')
    ->subject('Test Symfony Mailer')
    ->text('Ceci est un test simple pour vérifier la configuration.');

try {
    $mailer->send($email);
    echo "Email envoyé avec succès.\n";
} catch (\Exception $e) {
    echo "Échec de l'envoi : " . $e->getMessage() . "\n";
}
