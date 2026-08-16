<?php

namespace App\Controller\Admin;

use App\Entity\About;
use App\Repository\AboutRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AboutCrudController extends AbstractCrudController
{
    public function __construct(private readonly AboutRepository $aboutRepository)
    {
    }

    public static function getEntityFqcn(): string
    {
        return About::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('présentation')
            ->setEntityLabelInPlural('Présentation')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une présentation')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la présentation')
            ->setDefaultSort(['id' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield FormField::addFieldset('Texte de présentation', 'fa fa-address-card')->hideOnIndex();
        yield TextField::new('title', 'Titre')
            ->setHelp('20 caractères maximum.')
            ->setColumns('col-12 col-lg-4');
        yield TextEditorField::new('content', 'Contenu')
            ->setFormTypeOption('sanitize_html', true)
            ->setHelp('255 caractères maximum. Le HTML dangereux est automatiquement retiré à l’affichage.')
            ->setColumns('col-12 col-lg-8')
            ->onlyOnForms();
        yield TextareaField::new('content', 'Contenu')
            ->hideOnForm();
    }

    public function configureActions(Actions $actions): Actions
    {
        if($this->aboutRepository->count([]) >= 3) {
            $actions = $actions->disable(Action::NEW);
        }

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, static fn (Action $action): Action => $action
                ->setLabel('Ajouter une présentation')
                ->setIcon('fa fa-plus'));
    }
}
