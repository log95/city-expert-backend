<?php

namespace App\DataFixtures;

use App\Entity\Enum\TestPublishStatus;
use App\Entity\Test;
use App\Entity\TestHint;
use App\Entity\User;
use App\Enum\Role;
use App\Repository\CityRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;
    private CityRepository $cityRepository;

    public function __construct(UserPasswordEncoderInterface $encoder, CityRepository $cityRepository)
    {
        $this->encoder = $encoder;
        $this->cityRepository = $cityRepository;
    }

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        $user = new User();
        $user->setName('User');
        $user->setPassword($this->encoder->encodePassword($user, '12345678'));
        $user->setEmail('user@yandex.ru');
        $user->setVerifiedAt(new \DateTime());
        $manager->persist($user);

        $userTestCreator = new User();
        $userTestCreator->setName('UserTestCreator');
        $userTestCreator->setPassword($this->encoder->encodePassword($user, '12345678'));
        $userTestCreator->setEmail('user-creator@yandex.ru');
        $userTestCreator->setVerifiedAt(new \DateTime());
        $manager->persist($userTestCreator);

        $moderator = new User();
        $moderator->setName('Moderator');
        $moderator->setPassword($this->encoder->encodePassword($user, '12345678'));
        $moderator->setEmail('moderator@yandex.ru');
        $user->setVerifiedAt(new \DateTime());
        $moderator->setRoles([Role::MODERATOR]);
        $manager->persist($moderator);

        $city = $this->cityRepository->findOneBy(['name' => 'MOSCOW']);

        for ($i = 0; $i < 20; $i++) {
            $test = new Test();
            $test->setQuestion($faker->text);
            $test->setAnswer($faker->word);
            $test->setImageUrl('https://www.culture.ru/s/vopros/kremlin/images/tild3264-3233-4131-b836-623939356634__noroot.png');
            $test->setModerator($moderator);
            $test->setCurrentStatus(TestPublishStatus::REVIEWED);
            $test->setCreatedBy($userTestCreator);
            $test->setCity($city);

            $hint = new TestHint($test, $faker->text);

            $manager->persist($test);
            $manager->persist($hint);
        }

        $manager->flush();
    }
}
