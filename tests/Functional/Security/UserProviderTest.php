<?php
declare(strict_types=1);
/**
 * /tests/Functional/Security/UserProviderTest.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Tests\Functional\Security;

use App\Entity\User;
use App\Security\UserProvider;
use Doctrine\ORM\Mapping\ClassMetadata;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\User\User as CoreUser;

/**
 * Class UserProviderTest
 *
 * @package App\Tests\Functional\Security
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class UserProviderTest extends KernelTestCase
{
    /**
     * @var UserProvider;
     */
    private $userProvider;

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testThatLoadUserByUsernameReturnsNullWithInvalidUsername(): void
    {
        static::assertNull($this->userProvider->loadUserByUsername('foobar'));
    }

    /**
     * @expectedException \Doctrine\ORM\NoResultException
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testThatRefreshUserThrowsAnExceptionIfUserIsNotFound(): void
    {
        $user = new User();
        $user->setUsername('test');

        $this->userProvider->refreshUser($user);

        unset($user);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testThatRefreshUserReturnsCorrectUser(): void
    {
        /** @var User $user */
        $user = $this->userProvider->findOneBy(['username' => 'john']);

        static::assertNotNull($user);
        static::assertInstanceOf(User::class, $user);

        /** @noinspection NullPointerExceptionInspection */
        static::assertSame($user->getId(), $this->userProvider->refreshUser($user)->getId());

        unset($user);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     * @expectedExceptionMessage Instance of "Symfony\Component\Security\Core\User\User" is not supported.
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function testThatRefreshUserThrowsAnExceptionIfUserClassIsNotSupported(): void
    {
        $user = new CoreUser('test', 'password');

        $this->userProvider->refreshUser($user);

        unset($user);
    }

    protected function setUp(): void
    {
        parent::setUp();

        static::bootKernel();

        // Store container and entity manager
        $container = static::$kernel->getContainer();
        $entityManager = $container->get('doctrine.orm.default_entity_manager');
        $repository = UserProvider::class;

        $this->userProvider = new $repository($entityManager, new ClassMetadata(User::class));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        unset($this->userProvider);
    }
}
