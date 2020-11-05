<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Enum\Role;
use App\Enum\TestPublishStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }

    public function getMostFreeModerator(): ?User
    {
        $em = $this->getEntityManager();
        $conn = $em->getConnection();

        $qb = $conn->createQueryBuilder();

        $moderatorId = $qb
            ->select([
                'users.id',
            ])
            ->from('"user"', 'users')
            ->leftJoin(
                'users',
                'test',
                'test',
                $qb->expr()->and(
                    $qb->expr()->eq('users.id', 'test.moderator_id'),
                    $qb->expr()->neq('test.current_status', ':published_status'),
                    $qb->expr()->isNotNull('test.moderator_id'),
                )
            )
            ->where('jsonb_exists(users.roles, :moderator_role)')
            ->setParameters([
                'moderator_role' => Role::MODERATOR,
                'published_status' => TestPublishStatus::PUBLISHED
            ])
            ->groupBy('users.id')
            ->orderBy('COUNT(*)', 'ASC')
            ->setMaxResults(1)
            ->execute()
            ->fetchOne();

        return $moderatorId ? $em->getReference(User::class, $moderatorId) : null;
    }
}
