<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Vich\UploaderBundle\Form\Type\VichFileType;

class UserCrudController extends AbstractCrudController
{
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher, private readonly UserRepository $userRepository, private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function index(AdminContext $context): KeyValueStore|Response
    {
        $user = $this->userRepository->findOneBy([]);

        if(null === $user) {
            return parent::index($context);
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($user->getId())
            ->generateUrl());
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('administrateur')
            ->setEntityLabelInPlural('Mon utilisateur')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier mon profil et mes accès');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield FormField::addFieldset('Identité et contact', 'fa fa-user')->hideOnIndex();
        yield EmailField::new('email', 'E-mail de connexion')
            ->setColumns('col-12 col-lg-6');
        yield TextField::new('phone', 'Téléphone')
            ->setHelp('10 chiffres, sans espaces.')
            ->setColumns('col-12 col-lg-6');
        yield TextField::new('lastname', 'Nom')
            ->setColumns('col-12 col-lg-6');
        yield TextField::new('firstname', 'Prénom')
            ->setColumns('col-12 col-lg-6');
        yield UrlField::new('linkgithub', 'Lien GitHub')
            ->setColumns('col-12 col-lg-6');
        yield UrlField::new('linklinkedin', 'Lien LinkedIn')
            ->setColumns('col-12 col-lg-6');
        yield FormField::addFieldset('Photo et CV', 'fa fa-file-arrow-up')->hideOnIndex();
        yield Field::new('photo', 'Photo de profil')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions([
                'download_label' => true,
                'allow_delete' => false,
                'attr' => ['accept' => 'image/webp'],
            ])
            ->setHelp('WEBP, 10 Mo maximum.')
            ->setColumns('col-12 col-lg-6')
            ->onlyOnForms();
        yield ImageField::new('photo_filename', 'Photo de profil')
            ->setBasePath('/uploads/profile')
            ->hideOnForm();
        yield Field::new('cv', 'CV')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions([
                'download_label' => true,
                'allow_delete' => false,
                'attr' => ['accept' => 'application/pdf'],
            ])
            ->setHelp('PDF, 10 Mo maximum.')
            ->setColumns('col-12 col-lg-6')
            ->onlyOnForms();
        yield TextField::new('cvfilename', 'CV')->hideOnForm();
        yield FormField::addFieldset('Sécurité', 'fa fa-lock')->hideOnIndex();
        yield TextField::new('currentPassword', 'Mot de passe actuel')
            ->setFormType(PasswordType::class)
            ->setFormTypeOptions([
                'mapped' => false,
                'required' => false,
                'attr' => ['autocomplete' => 'current-password'],
            ])
            ->setHelp('Obligatoire uniquement si vous définissez un nouveau mot de passe.')
            ->setColumns('col-12 col-xl-4')
            ->onlyOnForms();
        yield TextField::new('plainPassword', 'Nouveau mot de passe')
            ->setFormType(RepeatedType::class)
            ->setFormTypeOptions([
                'type' => PasswordType::class,
                'required' => false,
                'invalid_message' => 'Les deux nouveaux mots de passe ne correspondent pas.',
                'first_options' => [
                    'label' => 'Nouveau mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Confirmation du nouveau mot de passe',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
            ])
            ->setHelp('12 caractères minimum avec majuscule, minuscule et chiffre. Laissez vide pour le conserver.')
            ->setColumns('col-12 col-xl-8')
            ->onlyOnForms();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::NEW, Action::DELETE, Action::BATCH_DELETE);
    }

    public function createEditFormBuilder(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormBuilderInterface
    {
        $builder = parent::createEditFormBuilder($entityDto, $formOptions, $context);
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $form = $event->getForm();
            $user = $form->getData();

            if(!$user instanceof User || null === $user->getPlainPassword() || '' === trim($user->getPlainPassword())) {
                return;
            }

            $currentPassword = (string) $form->get('currentPassword')->getData();

            if('' === $currentPassword || !$this->passwordHasher->isPasswordValid($user, $currentPassword)) {
                $form->get('currentPassword')->addError(new FormError('Le mot de passe actuel est obligatoire et doit être correct.'));
            }
        });

        return $builder;
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if(!$entityInstance instanceof User) {
            parent::updateEntity($entityManager, $entityInstance);

            return;
        }

        if(null !== $entityInstance->getPlainPassword() && '' !== trim($entityInstance->getPlainPassword())) {
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword()));
        }

        $entityInstance->setRoles(['ROLE_ADMIN'])->markUpdated()->eraseCredentials();
        parent::updateEntity($entityManager, $entityInstance);
    }
}
