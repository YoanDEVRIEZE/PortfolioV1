<?php

namespace App\EventListener;

use App\Entity\SiteParameter;
use App\Entity\User;
use App\Repository\AboutRepository;
use App\Repository\CareerRepository;
use App\Repository\ProjectRepository;
use App\Repository\SiteParameterRepository;
use App\Repository\SkillRepository;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

final class LoadSiteListener
{
    public function __construct(private readonly Environment $twig, private readonly SiteParameterRepository $siteParameterRepository, private readonly UserRepository $userRepository, private readonly AboutRepository $aboutRepository, private readonly ProjectRepository $projectRepository, private readonly CareerRepository $careerRepository, private readonly SkillRepository $skillRepository)
    {
    }

    #[AsEventListener(event: KernelEvents::REQUEST)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $route = $event->getRequest()->attributes->getString('_route');

        if(!$event->isMainRequest() || !in_array($route, ['app_site', 'app_site_locale', 'app_login'], true)) {
            return;
        }

        $this->twig->addGlobal('siteParameter', $this->siteParameterRepository->findOneBy([]) ?? new SiteParameter());
        $this->twig->addGlobal('user', $this->userRepository->findOneBy([]) ?? new User());
        $this->twig->addGlobal('about', $this->aboutRepository->findAll());
        $this->twig->addGlobal('project', $this->projectRepository->findBy([], ['id' => 'DESC']));
        $this->twig->addGlobal('career', $this->careerRepository->findBy([], ['id' => 'DESC']));
        $this->twig->addGlobal('skill', $this->skillRepository->findAll());
    }
}
