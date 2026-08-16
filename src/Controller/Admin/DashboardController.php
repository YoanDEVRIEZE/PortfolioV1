<?php

namespace App\Controller\Admin;

use App\Repository\AboutRepository;
use App\Repository\CareerRepository;
use App\Repository\MessageRepository;
use App\Repository\ProjectRepository;
use App\Repository\SiteParameterRepository;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly MessageRepository $messageRepository, private readonly ProjectRepository $projectRepository, private readonly CareerRepository $careerRepository, private readonly SkillRepository $skillRepository, private readonly AboutRepository $aboutRepository, private readonly UserRepository $userRepository, private readonly SiteParameterRepository $siteParameterRepository, private readonly Packages $assets)
    {
    }

    public function index(): Response
    {
        $user = $this->userRepository->findOneBy([]);
        $settings = $this->siteParameterRepository->findOneBy([]);

        return $this->render('admin/my-dashboard.html.twig', [
            'message_count' => $this->messageRepository->count([]),
            'project_count' => $this->projectRepository->count([]),
            'career_count' => $this->careerRepository->count([]),
            'skill_count' => $this->skillRepository->count([]),
            'about_count' => $this->aboutRepository->count([]),
            'latest_projects' => $this->projectRepository->findBy([], ['id' => 'DESC'], 3),
            'latest_careers' => $this->careerRepository->findBy([], ['id' => 'DESC'], 3),
            'has_user' => null !== $user,
            'has_identity' => null !== $user && null !== $user->getFirstname() && null !== $user->getLastname(),
            'has_settings' => null !== $settings,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administration du portfolio')
            ->setFaviconPath($this->profileImageUrl())
            ->setDefaultColorScheme('dark')
            ->renderContentMaximized();
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('styles/admin.css')
            ->addJsFile('js/admin.js');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-chart-line');
        yield MenuItem::linkToRoute('Voir le portfolio', 'fa fa-arrow-up-right-from-square', 'app_site_locale');
        yield MenuItem::section('Contenu');
        yield MenuItem::linkTo(AboutCrudController::class, 'Présentation', 'fa fa-address-card');
        yield MenuItem::linkTo(ProjectCrudController::class, 'Projets', 'fa fa-folder-open');
        yield MenuItem::linkTo(CareerCrudController::class, 'Parcours', 'fa fa-briefcase');
        yield MenuItem::linkTo(SkillCrudController::class, 'Compétences', 'fa fa-code');
        yield MenuItem::section('Activité');
        yield MenuItem::linkTo(MessageCrudController::class, 'Messages', 'fa fa-envelope');
        yield MenuItem::section('Configuration');
        yield MenuItem::linkTo(SiteParameterCrudController::class, 'Paramètres du site', 'fa fa-globe');
        yield MenuItem::linkTo(UserCrudController::class, 'Mon utilisateur', 'fa fa-user-shield');
    }

    public function configureUserMenu(UserInterface $user): UserMenu
    {
        return parent::configureUserMenu($user)
            ->setAvatarUrl($this->profileImageUrl());
    }

    private function profileImageUrl(): string
    {
        $filename = $this->userRepository->findOneBy([])?->getPhotoFilename();

        if(null !== $filename && '' !== $filename) {
            return '/uploads/profile/'.$filename;
        }

        return $this->assets->getUrl('styles/img/profil/default.webp');
    }
}
