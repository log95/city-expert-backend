<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Enum\TestPublishStatus;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\TestInterest;
use App\Entity\User;
use App\Enum\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;
    private Generator $faker;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager)
    {
        if (!$this->isNeedFixture($manager)) {
            return;
        }

        $password = '123456789';

        // Users
        $user = new User();
        $user->setName('Иванов Иван');
        $user->setPassword($this->encoder->encodePassword($user, $password));
        $user->setEmail('user@test.ru');
        $user->setVerifiedAt(new \DateTime());
        $manager->persist($user);

        $userTestCreator = new User();
        $userTestCreator->setName('Петров Пётр');
        $userTestCreator->setPassword($this->encoder->encodePassword($user, $password));
        $userTestCreator->setEmail('user-creator@test.ru');
        $userTestCreator->setVerifiedAt(new \DateTime());
        $manager->persist($userTestCreator);

        $moderator = new User();
        $moderator->setName('Модератор №1');
        $moderator->setPassword($this->encoder->encodePassword($user, $password));
        $moderator->setEmail('moderator@test.ru');
        $moderator->setVerifiedAt(new \DateTime());
        $moderator->setRoles([Role::MODERATOR]);
        $manager->persist($moderator);

        $cityRepository = $manager->getRepository(City::class);
        $city = $cityRepository->findOneBy(['name' => 'MSC']);

        // Tests

        // 1
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Планетарий');
        $test->setImageUrl($this->getImageUrl('planet.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Пресненский район');

        $manager->persist($test);
        $manager->persist($hint);

        $this->randomLike($userTestCreator, $test, $manager);

        // 2
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Кремль');
        $test->setImageUrl($this->getImageUrl('kreml.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Центр Москвы');

        $manager->persist($test);
        $manager->persist($hint);

        $this->randomLike($userTestCreator, $test, $manager);

        // 3
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Царицыно');
        $test->setImageUrl($this->getImageUrl('caricyno.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $manager->persist($test);

        // 4
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Поклонная гора');
        $test->setImageUrl($this->getImageUrl('poklon.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Срытый пологий холм в Западном административном округе Москвы');

        $manager->persist($test);
        $manager->persist($hint);

        $this->randomLike($userTestCreator, $test, $manager);

        // 5
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Большой театр');
        $test->setImageUrl($this->getImageUrl('big-theater.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Изначально театр был частным, но с 1794 стал казённым.');

        $manager->persist($test);
        $manager->persist($hint);

        $this->randomLike($userTestCreator, $test, $manager);

        // 6
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('ВДНХ');
        $test->setImageUrl($this->getImageUrl('vdnh.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Выставочный комплекс в Останкинском районе Северо-Восточного административного округа.');

        $manager->persist($test);

        // 7
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Зоопарк');
        $test->setImageUrl($this->getImageUrl('zoo.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Расположен рядом с Садовым кольцом между улицами Красная Пресня, Большой Грузинской и Зоологической.');

        $manager->persist($test);
        $manager->persist($hint);

        $this->randomLike($userTestCreator, $test, $manager);

        // 8
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Аэроэкспресс');
        $test->setImageUrl($this->getImageUrl('aero.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $manager->persist($test);

        // 9
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Останкинская башня');
        $test->setImageUrl($this->getImageUrl('ostank.jpeg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Является высочайшим сооружением в Европе и России.');

        $manager->persist($test);
        $manager->persist($hint);

        // 10
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Аптекарский огород');
        $test->setImageUrl($this->getImageUrl('ogorod.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Основан Петром I в 1706 году.');

        $manager->persist($test);
        $manager->persist($hint);

        // on moderator review
        $test = new Test();
        $test->setQuestion('Что изображено на фото?');
        $test->setAnswer('Кремль');
        $test->setImageUrl($this->getImageUrl('kreml.jpg'));
        $test->setModerator($moderator);
        $test->setCurrentStatus(TestPublishStatus::REVIEW);
        $test->setCreatedBy($userTestCreator);
        $test->setCity($city);
        $test->setPublishedAt(new \DateTime());

        $hint = new TestHint($test, 'Центр Москвы');

        $manager->persist($test);
        $manager->persist($hint);

        $manager->flush();
    }

    private function randomLike(User $user, Test $test, ObjectManager $manager): void
    {
        $testInterest = new TestInterest($user, $test, $this->faker->boolean);
        $manager->persist($testInterest);
    }

    private function isNeedFixture(ObjectManager $manager): bool
    {
        $userRepository = $manager->getRepository(User::class);

        return \is_null($userRepository->findOneBy(['email' => 'user@test.ru']));
    }

    private function getImageUrl(string $imageName): string
    {
        return 'http://localhost:9022/tests/' . $imageName;
    }
}
