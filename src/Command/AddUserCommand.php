<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:manage',
    description: 'Crée l’administrateur unique ou modifie ses identifiants.',
)]
final class AddUserCommand extends Command
{
    public function __construct(private readonly UserRepository $userRepository, private readonly EntityManagerInterface $entityManager, private readonly UserPasswordHasherInterface $passwordHasher)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Adresse e-mail de connexion')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Mot de passe (préférez la saisie interactive)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $user = $this->userRepository->findOneBy([]);
        $isNew = null === $user;
        $user ??= new User();
        $email = (string) ($input->getArgument('email') ?: $io->ask('Adresse e-mail', $user->getEmail()));

        if(false === filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('L’adresse e-mail n’est pas valide.');

            return Command::INVALID;
        }

        $password = $input->getOption('password');

        if(null === $password && ($isNew || $io->confirm('Modifier aussi le mot de passe ?', false))) {
            $password = $io->askHidden('Mot de passe (12 caractères minimum)');
        }

        if($isNew && null === $password) {
            $io->error('Un mot de passe est obligatoire lors de la création.');

            return Command::INVALID;
        }

        if(null !== $password && 1 !== preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{12,}$/', (string) $password)) {
            $io->error('Le mot de passe doit contenir au moins 12 caractères, avec une majuscule, une minuscule et un chiffre.');

            return Command::INVALID;
        }

        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);

        if(null !== $password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, (string) $password));
        }

        $user->markUpdated();

        if($isNew) {
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        $io->success($isNew ? 'Administrateur créé.' : 'Administrateur mis à jour.');

        return Command::SUCCESS;
    }
}
