<?php

namespace App\Tests\Entity;

use App\Entity\Career;
use App\Entity\Message;
use App\Entity\Project;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class EntityRulesTest extends KernelTestCase
{
    public function testContactMessageIsNormalizedAndValidated(): void
    {
        $message = (new Message())
            ->setLastname(' <script> ')
            ->setFirstname('A')
            ->setEmail(' VISITOR@EXAMPLE.COM ')
            ->setMessage('trop court');

        $violations = $this->validator()->validate($message);
        $paths = [];

        foreach($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        self::assertSame('visitor@example.com', $message->getEmail());
        self::assertContains('lastname', $paths);
        self::assertContains('firstname', $paths);
        self::assertContains('message', $paths);
    }

    public function testProjectRequiresAtLeastOneSkill(): void
    {
        $project = (new Project())
            ->setTitle('Portfolio')
            ->setContent('Description du projet');

        $violations = $this->validator()->validate($project);
        $paths = [];

        foreach($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        self::assertContains('skills', $paths);
    }

    public function testCareerEndDateCannotPrecedeStartDate(): void
    {
        $career = (new Career())
            ->setTitle('Entreprise')
            ->setPosition('Développeur')
            ->setContent('Description du parcours')
            ->setStartDate(new \DateTimeImmutable('2026-08-01'))
            ->setEndDate(new \DateTimeImmutable('2026-07-01'));

        $violations = $this->validator()->validate($career);
        $paths = [];

        foreach($violations as $violation) {
            $paths[] = $violation->getPropertyPath();
        }

        self::assertContains('dateRangeValid', $paths);
    }

    public function testAdministratorRoleCannotBeRemovedAndSecretsAreErased(): void
    {
        $user = (new User())
            ->setRoles(['ROLE_USER'])
            ->setPlainPassword('TemporaryPassword1')
            ->setCurrentPassword('CurrentPassword1');

        self::assertContains('ROLE_ADMIN', $user->getRoles());

        $user->eraseCredentials();

        self::assertNull($user->getPlainPassword());
        self::assertNull($user->getCurrentPassword());
    }

    public function testRichHtmlRemovesExecutableContent(): void
    {
        self::bootKernel();
        $sanitizer = self::getContainer()->get(HtmlSanitizerInterface::class);
        $html = $sanitizer->sanitize('<p>Texte sûr</p><script>alert(1)</script><a href="javascript:alert(1)">Lien</a>');

        self::assertStringContainsString('Texte sûr', $html);
        self::assertStringNotContainsString('<script', $html);
        self::assertStringNotContainsString('javascript:', $html);
    }

    private function validator(): ValidatorInterface
    {
        self::bootKernel();

        return self::getContainer()->get(ValidatorInterface::class);
    }
}
