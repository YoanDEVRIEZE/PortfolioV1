<?php

namespace App\Service;

use App\Entity\Message;
use App\Repository\SiteParameterRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

final class MailSendService
{
    public function __construct(private readonly MailerInterface $mailer, private readonly Environment $twig, private readonly UserRepository $userRepository, private readonly SiteParameterRepository $siteParameterRepository, private readonly LoggerInterface $logger, private readonly string $mailerFrom)
    {
    }

    public function sendContactNotification(Message $message): bool
    {
        $owner = $this->userRepository->findOneBy([]);

        if(null === $owner || null === $owner->getEmail()) {
            $this->logger->warning('Le message a été enregistré, mais aucun administrateur ne peut recevoir la notification.');

            return false;
        }

        try {
            $email = (new Email())
                ->from(Address::create($this->mailerFrom))
                ->to($owner->getEmail())
                ->replyTo((string) $message->getEmail())
                ->subject('Demande de contact via le portfolio')
                ->html($this->twig->render('email/contactEmail.html.twig', [
                    'owner' => $owner,
                    'site' => $this->siteParameterRepository->findOneBy([]),
                    'firstname' => $message->getFirstname(),
                    'lastname' => $message->getLastname(),
                    'email' => $message->getEmail(),
                    'message' => $message->getMessage(),
                ]));
        } catch (\Throwable $exception) {
            return $this->reportPreparationFailure('notification de contact', $exception);
        }

        return $this->send($email, 'notification de contact');
    }

    public function sendReply(Message $message, string $reply): bool
    {
        $owner = $this->userRepository->findOneBy([]);
        try {
            $email = (new Email())
                ->from(Address::create($this->mailerFrom))
                ->to((string) $message->getEmail())
                ->subject('Réponse à votre message')
                ->html($this->twig->render('email/responseEmail.html.twig', [
                    'owner' => $owner,
                    'site' => $this->siteParameterRepository->findOneBy([]),
                    'firstname' => $message->getFirstname(),
                    'lastname' => $message->getLastname(),
                    'email' => $message->getEmail(),
                    'replyMessage' => $reply,
                ]));

            if(null !== $owner?->getEmail()) {
                $email->replyTo($owner->getEmail());
            }
        } catch (\Throwable $exception) {
            return $this->reportPreparationFailure('réponse au message', $exception);
        }

        return $this->send($email, 'réponse au message');
    }

    private function send(Email $email, string $context): bool
    {
        try {
            $this->mailer->send($email);

            return true;
        } catch (TransportExceptionInterface $exception) {
            $this->logger->error(sprintf('Échec de l’envoi de la %s.', $context), ['exception' => $exception]);

            return false;
        }
    }

    private function reportPreparationFailure(string $context, \Throwable $exception): false
    {
        $this->logger->error(sprintf('Impossible de préparer la %s.', $context), ['exception' => $exception]);

        return false;
    }
}
