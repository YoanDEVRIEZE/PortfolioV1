<?php

namespace App\Controller\Admin;

use App\Entity\Career;
use App\Enum\StatusEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;

class CareerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Career::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('parcours')
            ->setEntityLabelInPlural('Parcours')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un parcours')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le parcours')
            ->setDefaultSort(['start_date' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $isCreatePage = Crud::PAGE_NEW === $pageName;

        yield IdField::new('id')->onlyOnIndex();
        yield FormField::addFieldset('Expérience', 'fa fa-briefcase')->hideOnIndex();
        yield TextField::new('title', 'Entreprise')
            ->setHelp('2 à 100 caractères.')
            ->setColumns('col-12 col-lg-6');
        yield TextField::new('position', 'Poste occupé')
            ->setHelp('2 à 100 caractères.')
            ->setColumns('col-12 col-lg-6');
        yield DateField::new('startdate', 'Date de début')
            ->setColumns('col-12 col-md-4');
        yield DateField::new('enddate', 'Date de fin')
            ->setHelp('Laissez vide si cette expérience est en cours.')
            ->setColumns('col-12 col-md-4');
        yield ChoiceField::new('status', 'Statut')
            ->setChoices(array_combine(
                array_map(static fn (StatusEnum $status): string => $status->value, StatusEnum::cases()),
                StatusEnum::cases(),
            ))
            ->setColumns('col-12 col-md-4')
            ->onlyOnForms();
        yield TextField::new('status', 'Statut')->hideOnForm();
        yield FormField::addFieldset('Illustrations', 'fa fa-images')->hideOnIndex();
        yield Field::new('cover_picture', 'Image de couverture')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions([
                'download_label' => true,
                'allow_delete' => false,
                'attr' => ['accept' => 'image/webp'],
            ])
            ->setRequired($isCreatePage)
            ->setHelp('WEBP, 10 Mo maximum.')
            ->setColumns('col-12 col-lg-6')
            ->onlyOnForms();
        yield ImageField::new('cover_picture_filename', 'Image de couverture')
            ->setBasePath('/uploads/careers/covers')
            ->hideOnForm();
        yield Field::new('job_picture', 'Image du parcours')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions([
                'download_label' => true,
                'allow_delete' => false,
                'attr' => ['accept' => 'image/webp'],
            ])
            ->setRequired($isCreatePage)
            ->setHelp('WEBP, 10 Mo maximum.')
            ->setColumns('col-12 col-lg-6')
            ->onlyOnForms();
        yield ImageField::new('job_picture_filename', 'Image du parcours')
            ->setBasePath('/uploads/careers/details')
            ->hideOnForm();
        yield FormField::addFieldset('Description', 'fa fa-align-left')->hideOnIndex();
        yield TextEditorField::new('content', 'Contenu')
            ->setFormTypeOption('sanitize_html', true)
            ->setHelp('2 à 2 000 caractères. Le HTML dangereux est automatiquement supprimé.')
            ->setColumns('col-12')
            ->onlyOnForms();
        yield TextareaField::new('content', 'Contenu')->hideOnForm()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, static fn (Action $action): Action => $action
                ->setLabel('Ajouter un parcours')
                ->setIcon('fa fa-plus'));
    }
}
