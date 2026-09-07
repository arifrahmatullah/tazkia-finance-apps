<?php

namespace App\Mail\Transport;

use Google\Client as GoogleClient;
use Google\Service\Gmail as GoogleGmail;
use Google\Service\Gmail\Message as GoogleGmailMessage;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;

class GmailApiTransport extends AbstractTransport
{
    protected function doSend(SentMessage $message): void
    {
        $client = new GoogleClient();
        $client->setAuthConfig(storage_path('app/google/client_secret.json'));
        $client->addScope('https://www.googleapis.com/auth/gmail.send');

        $tokenPath = storage_path('app/google/token.json');
        $token = json_decode((string) file_get_contents($tokenPath), true);
        $client->setAccessToken($token);

        if ($client->isAccessTokenExpired()) {
            $refreshed = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            $refreshed['refresh_token'] = $refreshed['refresh_token'] ?? $token['refresh_token'];
            file_put_contents($tokenPath, json_encode($refreshed, JSON_PRETTY_PRINT));
            $client->setAccessToken($refreshed);
        }

        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $raw = base64_encode($email->toString());
        $raw = str_replace(['+', '/', '='], ['-', '_', ''], $raw);

        $gmailMessage = new GoogleGmailMessage();
        $gmailMessage->setRaw($raw);

        (new GoogleGmail($client))->users_messages->send('me', $gmailMessage);
    }

    public function __toString(): string
    {
        return 'gmail-api';
    }
}
