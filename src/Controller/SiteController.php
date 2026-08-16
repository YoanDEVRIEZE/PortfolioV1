<?php

namespace App\Controller;

use App\Form\MessageType;
use App\Service\MailSendService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

final class SiteController extends AbstractController
{
    public function __construct(private readonly MailSendService $mailSendService, private readonly EntityManagerInterface $entityManager)
    {
    }

    #[Route('/', name: 'app_site_locale', methods: ['GET', 'POST'])]
    public function locale(): Response
    {
        return $this->redirectToRoute('app_site', ['_locale' => 'fr']);
    }

    #[Route('/{_locale}', name: 'app_site', requirements: ['_locale' => 'fr|en'], methods: ['GET', 'POST'])]
    public function index(string $_locale, Request $request, #[Target('contact_form')] RateLimiterFactoryInterface $contactFormLimiter): Response
    {
        $messageForm = $this->createForm(MessageType::class);
        $messageForm->handleRequest($request);

        if($messageForm->isSubmitted()) {
            $rateLimit = $contactFormLimiter
                ->create($request->getClientIp() ?? 'anonymous')
                ->consume();

            if($messageForm->isValid() && $rateLimit->isAccepted()) {
                $message = $messageForm->getData();
                $this->entityManager->persist($message);
                $this->entityManager->flush();
                $emailSent = $this->mailSendService->sendContactNotification($message);

                $this->addFlash(
                    'success',
                    $emailSent
                        ? 'Votre message a bien été envoyé. Je vous répondrai dès que possible.'
                        : 'Votre message est enregistré. La notification e-mail est momentanément indisponible.',
                );

                return $this->redirectToRoute('app_site', [
                    '_locale' => $_locale,
                    'section' => 4,
                ]);
            }

            if(!$rateLimit->isAccepted()) {
                $this->addFlash('error', 'Trop de tentatives. Patientez quelques minutes avant de réessayer.');
            } else {
                $this->addFlash('error', 'Le formulaire contient des erreurs. Vérifiez les champs signalés.');
            }
        }

        return $this->render('site/index.html.twig', [
            'form' => $messageForm,
            'section' => $messageForm->isSubmitted() ? 4 : 0,
            'open_contact' => $messageForm->isSubmitted(),
            'locale' => $_locale,
        ]);
    }
}
