<?php

namespace App\Controller\Admin;

use App\Entity\Message;
use App\Repository\MessageRepository;
use App\Service\MailSendService;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MessageCrudController extends AbstractCrudController
{
    public function __construct(private readonly MessageRepository $messageRepository, private readonly MailSendService $mailer, private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Message::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('message')
            ->setEntityLabelInPlural('Messages')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield DateTimeField::new('createdAt', 'Reçu le')->setFormat('dd/MM/yyyy HH:mm');
        yield TextField::new('lastname', 'Nom');
        yield TextField::new('firstname', 'Prénom');
        yield EmailField::new('email', 'E-mail');
        yield TextareaField::new('message', 'Message');
    }

    public function configureActions(Actions $actions): Actions
    {
        $replyAction = Action::new('reply', 'Répondre', 'fa fa-reply')
            ->linkToRoute('admin_message_reply', static fn (Message $message): array => ['id' => $message->getId()]);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $replyAction)
            ->add(Crud::PAGE_DETAIL, $replyAction)
            ->disable(Action::NEW, Action::EDIT, Action::BATCH_DELETE);
    }

    #[Route('/admin/messages/{id}/repondre', name: 'admin_message_reply', requirements: ['id' => '\\d+'], methods: ['GET', 'POST'])]
    public function reply(int $id, Request $request): Response
    {
        $message = $this->messageRepository->find($id);

        if(null === $message) {
            $this->addFlash('danger', 'Message introuvable.');

            return $this->redirectToMessageIndex();
        }

        if($request->isMethod('POST')) {
            $reply = trim($request->request->getString('replyMessage'));
            $token = $request->request->getString('_token');

            if(!$this->isCsrfTokenValid('reply_message_'.$message->getId(), $token)) {
                $this->addFlash('danger', 'La session a expiré. Rechargez la page avant de renvoyer votre réponse.');
            } elseif(mb_strlen($reply) < 10 || mb_strlen($reply) > 5000) {
                $this->addFlash('danger', 'La réponse doit contenir entre 10 et 5 000 caractères.');
            } elseif($this->mailer->sendReply($message, $reply)) {
                $this->addFlash('success', 'La réponse a bien été envoyée à '.$message->getEmail().'.');

                return $this->redirectToMessageIndex();
            } else {
                $this->addFlash('danger', 'L’e-mail n’a pas pu être envoyé. Vérifiez la configuration SMTP puis réessayez.');
            }
        }

        return $this->render('admin/reply_message.html.twig', [
            'page_title' => 'Répondre au message',
            'message' => $message,
        ]);
    }

    private function redirectToMessageIndex(): RedirectResponse
    {
        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}
