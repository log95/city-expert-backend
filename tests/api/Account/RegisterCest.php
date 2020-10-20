<?php

namespace Tests\Api;

use App\Entity\AuthOperation;
use App\Entity\User;
use App\Enum\AuthOperationType;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;
use Faker\Factory;

class RegisterCest
{
    public function registerInvalidDataTest(ApiTester $I)
    {
        $url = $I->grabService('router')->generate('account.register');

        $faker = Factory::create();

        $I->sendPOST($url, [
            'name' => '',
            'email' => $faker->word,
            'password' => '',
        ]);

        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
        $I->seeResponseEquals(json_encode([
            ['property_path' => 'name', 'message' => 'This value should not be blank.'],
            ['property_path' => 'email', 'message' => 'This value is not a valid email address.'],
            ['property_path' => 'password', 'message' => 'This value should not be blank.'],
            ['property_path' => 'password', 'message' => 'This value is too short. It should have 8 characters or more.'],
        ]));
    }

    public function registerValidDataTest(ApiTester $I)
    {
        $url = $I->grabService('router')->generate('account.register');

        $faker = Factory::create();

        $name = $faker->name;
        $email = $faker->email;
        $password = $faker->password(8, 100);

        $I->sendPOST($url, [
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $I->seeResponseCodeIs(HttpCode::CREATED);

        $I->seeInRepository(User::class, [
            'name' => $name,
            'email' => $email,
            'roles' => json_encode([]),
            'verifiedAt' => null,
        ]);

        /** @var User $user */
        $user = $I->grabEntityFromRepository(User::class, ['email' => $email]);

        $I->seeInRepository(AuthOperation::class, [
            'user' => ['id' => $user->getId()],
            'type' => AuthOperationType::VERIFICATION,
        ]);

    }
}
