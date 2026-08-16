<?php

namespace App\Controller\Admin;

use App\Entity\Skill;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ColorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichFileType;

class SkillCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Skill::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('compétence')
            ->setEntityLabelInPlural('Compétences')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une compétence')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la compétence')
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        $isCreatePage = Crud::PAGE_NEW === $pageName;

        yield IdField::new('id')->onlyOnIndex();
        yield FormField::addFieldset('Compétence', 'fa fa-code')->hideOnIndex();
        yield TextField::new('name', 'Nom')
            ->setHelp('2 à 100 caractères.')
            ->setColumns('col-12 col-lg-5');
        yield IntegerField::new('level', 'Niveau')
            ->setHelp('Valeur comprise entre 1 et 100.')
            ->setColumns('col-6 col-lg-3');
        yield ColorField::new('color', 'Couleur')
            ->setColumns('col-6 col-lg-4');
        yield Field::new('logo', 'Logo')
            ->setFormType(VichFileType::class)
            ->setFormTypeOptions([
                'download_label' => true,
                'allow_delete' => false,
                'attr' => ['accept' => 'image/webp'],
            ])
            ->setRequired($isCreatePage)
            ->setHelp('WEBP, 10 Mo maximum.')
            ->setColumns('col-12')
            ->onlyOnForms();
        yield ImageField::new('logo_filename', 'Logo')
            ->setBasePath('/uploads/skills')
            ->hideOnForm();
        yield AssociationField::new('projects', 'Projets associés')
            ->setHelp('Retirez d’abord cette compétence des projets associés avant de la supprimer.')
            ->onlyOnDetail();
    }

    public function configureActions(Actions $actions): Actions
    {
        $canDelete = static fn (Skill $skill): bool => $skill->getProjects()->isEmpty();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->disable(Action::BATCH_DELETE)
            ->update(Crud::PAGE_INDEX, Action::DELETE, static fn (Action $action): Action => $action->displayIf($canDelete))
            ->update(Crud::PAGE_DETAIL, Action::DELETE, static fn (Action $action): Action => $action->displayIf($canDelete))
            ->update(Crud::PAGE_INDEX, Action::NEW, static fn (Action $action): Action => $action
                ->setLabel('Ajouter une compétence')
                ->setIcon('fa fa-plus'));
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if($entityInstance instanceof Skill && !$entityInstance->getProjects()->isEmpty()) {
            $this->addFlash('danger', [
                'title' => 'Suppression impossible',
                'message' => 'Cette compétence est utilisée par un ou plusieurs projets. Retirez ces associations avant de la supprimer.',
                'icon' => 'fa-link',
            ]);

            return;
        }

        parent::deleteEntity($entityManager, $entityInstance);
    }
}
