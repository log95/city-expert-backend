<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\TestActionType;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20200519140303 extends AbstractMigration implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        /** @var ObjectManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $testActionTypes = [
            (new TestActionType())->setName(TestActionType::CORRECT_ANSWER),
            (new TestActionType())->setName(TestActionType::SHOW_ANSWER),
            (new TestActionType())->setName(TestActionType::SHOW_HINT),
            (new TestActionType())->setName(TestActionType::WRONG_ANSWER),
        ];

        foreach ($testActionTypes as $testActionType) {
            $em->persist($testActionType);
        }

        $em->flush();
    }

    public function down(Schema $schema) : void
    {
        $testActionTypes = [
            TestActionType::CORRECT_ANSWER,
            TestActionType::SHOW_ANSWER,
            TestActionType::SHOW_HINT,
            TestActionType::WRONG_ANSWER,
        ];

        /** @var ObjectManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $repository = $em->getRepository(TestActionType::class);

        $criteria = Criteria::create()
            ->where(Criteria::expr()->in('name', $testActionTypes));

        $actionTypeObjects = $repository->matching($criteria);
        foreach ($actionTypeObjects as $actionTypeObject) {
            $em->remove($actionTypeObject);
        }

        $em->flush();
    }
}
