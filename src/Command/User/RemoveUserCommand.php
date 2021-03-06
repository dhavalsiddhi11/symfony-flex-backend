<?php
declare(strict_types = 1);
/**
 * /src/Command/User/RemoveUserCommand.php
 *
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
namespace App\Command\User;

use App\Entity\User;
use App\Resource\UserResource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class RemoveUserCommand
 *
 * @package App\Command\User
 * @author  TLe, Tarmo Leppänen <tarmo.leppanen@protacon.com>
 */
class RemoveUserCommand extends Command
{
    /**
     * @var UserResource
     */
    private $userResource;

    /**
     * @var UserHelper
     */
    private $userHelper;

    /**
     * RemoveUserCommand constructor.
     *
     * @param UserResource $userResource
     * @param UserHelper   $userHelper
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct(UserResource $userResource, UserHelper $userHelper)
    {
        parent::__construct('user:remove');

        $this->userResource = $userResource;
        $this->userHelper = $userHelper;

        $this->setDescription('Console command to remove existing user');
    }

    /** @noinspection PhpMissingParentCallCommonInspection */
    /**
     * Executes the current command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $io = new SymfonyStyle($input, $output);
        $io->write("\033\143");

        // Get user entity
        $user = $this->userHelper->getUser($io, 'Which user you want to remove?');

        if ($user instanceof User) {
            // Delete user
            $this->userResource->delete($user->getId());

            $message = 'User removed - have a nice day';
        }

        if ($input->isInteractive()) {
            $io->success($message ?? 'Nothing changed - have a nice day');
        }

        return null;
    }
}
