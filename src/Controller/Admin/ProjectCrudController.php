<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use App\Enum\StatusEnum;
use App\Repository\SkillRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Vich\UploaderBundle\Form\Type\VichFileType;

class ProjectCrudController extends AbstractCrudController
{
    public function __construct(private readonly SkillRepository $skillRepository, private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('projet')
            ->setEntityLabelInPlural('Projets')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un projet')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le projet')
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $isCreatePage = Crud::PAGE_NEW === $pageName;

        yield IdField::new('id')->onlyOnIndex();
        yield FormField::addFieldset('Présentation', 'fa fa-folder-open')->hideOnIndex();
        yield TextField::new('title', 'Titre')
            ->setHelp('2 à 100 caractères.')
            ->setColumns('col-12 col-lg-5');
        yield TextField::new('description', 'Description courte')
            ->setHelp('2 à 150 caractères.')
            ->setColumns('col-12 col-lg-7');
        yield ChoiceField::new('status', 'Statut')
            ->setChoices(array_combine(
                array_map(static fn (StatusEnum $status): string => $status->value, StatusEnum::cases()),
                StatusEnum::cases(),
            ))
            ->setColumns('col-12 col-lg-4')
            ->onlyOnForms();
        yield TextField::new('status', 'Statut')->hideOnForm();
        yield AssociationField::new('skills', 'Compétences')
            ->autocomplete()
            ->setCrudController(SkillCrudController::class)
            ->setFormTypeOption('by_reference', false)
            ->setHelp('Sélectionnez au moins une compétence créée dans la rubrique « Compétences ».')
            ->setColumns('col-12 col-lg-8')
            ->onlyOnForms();
        yield CollectionField::new('skills', 'Compétences')
            ->setTemplatePath('admin/fields/skills_icons.html.twig')
            ->hideOnForm();
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
            ->setBasePath('/uploads/projects/covers')
            ->hideOnForm();
        yield Field::new('project_picture', 'Image détaillée')
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
        yield ImageField::new('project_picture_filename', 'Image détaillée')
            ->setBasePath('/uploads/projects/details')
            ->hideOnForm();
        yield FormField::addFieldset('Contenu et lien', 'fa fa-align-left')->hideOnIndex();
        yield UrlField::new('link', 'Lien du projet')
            ->setHelp('Adresse complète commençant par https://.')
            ->setColumns('col-12');
        yield TextEditorField::new('content', 'Contenu')
            ->setFormTypeOption('sanitize_html', true)
            ->setHelp('2 à 2 000 caractères. Le HTML dangereux est automatiquement supprimé.')
            ->setColumns('col-12')
            ->onlyOnForms();
        yield TextareaField::new('content', 'Contenu')->hideOnForm()->hideOnIndex();
    }

    public function configureActions(Actions $actions): Actions
    {
        if(0 === $this->skillRepository->count([])) {
            $createSkill = Action::new('createSkillFirst', 'Créer d’abord une compétence', 'fa fa-code')
                ->createAsGlobalAction()
                ->linkToUrl(fn (): string => $this->adminUrlGenerator
                    ->setController(SkillCrudController::class)
                    ->setAction(Action::NEW)
                    ->generateUrl());
            $actions = $actions
                ->disable(Action::NEW)
                ->add(Crud::PAGE_INDEX, $createSkill);
        }

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, static fn (Action $action): Action => $action
                ->setLabel('Ajouter un projet')
                ->setIcon('fa fa-plus'));
    }
}
