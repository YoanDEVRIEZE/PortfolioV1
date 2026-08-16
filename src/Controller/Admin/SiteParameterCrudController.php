<?php

namespace App\Controller\Admin;

use App\Entity\SiteParameter;
use App\Repository\SiteParameterRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

class SiteParameterCrudController extends AbstractCrudController
{
    public function __construct(private readonly SiteParameterRepository $siteParameterRepository, private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    public static function getEntityFqcn(): string
    {
        return SiteParameter::class;
    }

    public function index(AdminContext $context): KeyValueStore|Response
    {
        $settings = $this->siteParameterRepository->findOneBy([]);

        if(null === $settings) {
            return parent::index($context);
        }

        return $this->redirect($this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::EDIT)
            ->setEntityId($settings->getId())
            ->generateUrl());
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('configuration du site')
            ->setEntityLabelInPlural('Paramètres du site')
            ->setPageTitle(Crud::PAGE_NEW, 'Configurer le portfolio')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier les paramètres du site');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();
        yield FormField::addFieldset('Référencement', 'fa fa-magnifying-glass')->hideOnIndex();
        yield TextField::new('title', 'Titre du site')
            ->setHelp('100 caractères maximum. La balise &lt;br&gt; permet un retour à la ligne sur l’accueil.')
            ->setColumns('col-12');
        yield TextareaField::new('description', 'Description SEO')
            ->setHelp('255 caractères maximum.')
            ->setColumns('col-12 col-lg-6');
        yield TextareaField::new('mediadescription', 'Description pour les réseaux')
            ->setHelp('255 caractères maximum.')
            ->setColumns('col-12 col-lg-6');
        yield ArrayField::new('keyword', 'Mots-clés')
            ->setHelp('Ajoutez un mot-clé par élément.')
            ->setColumns('col-12');
        yield UrlField::new('urlsite', 'URL publique')
            ->setHelp('Adresse complète du portfolio, par exemple https://portfolio.example.')
            ->setColumns('col-12');
    }

    public function configureActions(Actions $actions): Actions
    {
        if($this->siteParameterRepository->count([]) >= 1) {
            $actions = $actions->disable(Action::NEW);
        }

        return $actions
            ->disable(Action::DELETE, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_NEW, Action::SAVE_AND_ADD_ANOTHER)
            ->update(Crud::PAGE_INDEX, Action::NEW, static fn (Action $action): Action => $action
                ->setLabel('Configurer le site')
                ->setIcon('fa fa-plus'));
    }
}
