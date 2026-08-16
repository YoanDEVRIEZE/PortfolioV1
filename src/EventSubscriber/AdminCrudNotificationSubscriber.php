<?php

namespace App\EventSubscriber;

use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Option\EA;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterCrudActionEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityDeletedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityPersistedEvent;
use EasyCorp\Bundle\EasyAdminBundle\Event\AfterEntityUpdatedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class AdminCrudNotificationSubscriber implements EventSubscriberInterface
{
    private array $notificationsSent = [];
    private ?int $currentRequestId = null;

    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            AfterEntityPersistedEvent::class => 'onEntityPersisted',
            AfterEntityUpdatedEvent::class => 'onEntityUpdated',
            AfterEntityDeletedEvent::class => 'onEntityDeleted',
            AfterCrudActionEvent::class => 'onAfterCrudAction',
            KernelEvents::EXCEPTION => ['onKernelException', -64],
        ];
    }

    public function onEntityPersisted(): void
    {
        $this->addNotificationOnce('created', 'success', 'Confirmation', 'L’ajout a été effectué avec succès.', 'fa-circle-check');
    }

    public function onEntityUpdated(): void
    {
        $this->addNotificationOnce('updated', 'success', 'Confirmation', 'La modification a été enregistrée avec succès.', 'fa-circle-check');
    }

    public function onEntityDeleted(): void
    {
        $this->addNotificationOnce('deleted', 'success', 'Confirmation', 'La suppression a été effectuée avec succès.', 'fa-circle-check');
    }

    public function onAfterCrudAction(AfterCrudActionEvent $event): void
    {
        $context = $event->getAdminContext();
        $crud = $context?->getCrud();

        if(null === $context || null === $crud) {
            return;
        }

        $action = $crud->getCurrentAction();
        $formKey = match ($action) {
            Action::NEW => 'new_form',
            Action::EDIT => 'edit_form',
            default => null,
        };

        if(null === $formKey) {
            return;
        }

        $form = $event->getResponseParameters()->get($formKey);

        if(!$form instanceof FormInterface || !$form->isSubmitted() || $form->isValid()) {
            return;
        }

        $message = Action::NEW === $action
            ? 'L’ajout n’a pas été effectué. Vérifiez les champs signalés.'
            : 'La modification n’a pas été enregistrée. Vérifiez les champs signalés.';

        $this->addNotificationOnce('invalid-'.$action, 'danger', 'Erreur', $message, 'fa-circle-exclamation');
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if(!$request->attributes->has(EA::CONTEXT_REQUEST_ATTRIBUTE) || in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return;
        }

        $this->addNotificationOnce('exception', 'danger', 'Erreur', 'L’action n’a pas pu être réalisée. Vérifiez les données puis réessayez.', 'fa-circle-exclamation');
    }

    private function addNotificationOnce(string $key, string $type, string $title, string $message, string $icon): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if(null === $request || !$request->hasSession()) {
            return;
        }

        $requestId = spl_object_id($request);

        if($this->currentRequestId !== $requestId) {
            $this->notificationsSent = [];
            $this->currentRequestId = $requestId;
        }

        if(isset($this->notificationsSent[$key])) {
            return;
        }

        $session = $request->getSession();

        if(!$session instanceof FlashBagAwareSessionInterface) {
            return;
        }

        $session->getFlashBag()->add($type, [
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
        ]);
        $this->notificationsSent[$key] = true;
    }
}
