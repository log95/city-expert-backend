<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Enum\TestPublishStatus;
use App\Entity\Test;
use App\Entity\TestAction;
use App\Entity\TestActionType;
use App\Entity\TestHint;
use App\Entity\TestInterest;
use App\Entity\User;
use App\Enum\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
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
        $moderator->setVerifiedAt(new \DateTime());
        $moderator->setRoles([Role::MODERATOR]);
        $manager->persist($moderator);

        $cityRepository = $manager->getRepository(City::class);
        $testActionTypeRepository = $manager->getRepository(TestActionType::class);

        $city = $cityRepository->findOneBy(['name' => 'MSC']);

        for ($i = 0; $i < 20; $i++) {
            $test = new Test();
            $test->setQuestion($faker->text);
            $test->setAnswer($faker->word);
            $test->setImageUrl('https://www.culture.ru/s/vopros/kremlin/images/tild3264-3233-4131-b836-623939356634__noroot.png');
            $test->setModerator($moderator);
            $test->setCurrentStatus(TestPublishStatus::PUBLISHED);
            $test->setCreatedBy($userTestCreator);
            $test->setCity($city);
            $test->setPublishedAt(new \DateTime());

            $hint = new TestHint($test, $faker->text);

            $manager->persist($test);
            $manager->persist($hint);

            $actionTypeName = $faker->randomElement([TestActionType::SHOW_ANSWER, TestActionType::CORRECT_ANSWER, TestActionType::WRONG_ANSWER]);
            $actionType = $testActionTypeRepository->findOneBy(['name' => $actionTypeName]);
            $testAction = new TestAction($user, $test, $actionType);
            $manager->persist($testAction);

            if ($i % 2 === 0) {
                $testInterest = new TestInterest($user, $test, $faker->boolean);
                $manager->persist($testInterest);
            }
        }

        $manager->flush();
    }
}
