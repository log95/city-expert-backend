<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\City;
use App\Entity\Country;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

final class Version20200525083516 extends AbstractMigration implements ContainerAwareInterface
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

        $country = new Country('RUS');
        $city = new City('MSC', $country);

        $em->persist($country);
        $em->persist($city);

        $em->flush();
    }

    public function down(Schema $schema) : void
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'postgresql', 'Migration can only be executed safely on \'postgresql\'.');

        $this->addSql('CREATE SCHEMA public');

        /** @var ObjectManager $em */
        $em = $this->container->get('doctrine.orm.entity_manager');

        $countryRepository = $em->getRepository(Country::class);
        $country = $countryRepository->findOneBy(['name' => 'RUS']);

        $em->remove($country);
        $em->flush();
    }
}
