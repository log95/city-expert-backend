<?php

namespace App\DataFixtures;

use App\Entity\Test;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $test = new Test();
            $test->setQuestion($faker->text);
            $test->setAnswer($faker->word);
            $test->setImage($faker->url);
            $test->setHints([$faker->text]);

            $manager->persist($test);
        }

        $manager->flush();
    }
}
