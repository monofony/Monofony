<?php

declare(strict_types=1);

namespace App\Tests\Controller\Customer;

use App\Factory\AppUserFactory;
use App\Story\TestAppUsersStory;
use App\Tests\Controller\AuthorizedHeaderTrait;
use App\Tests\Controller\JsonApiTestCase;
use App\Tests\Controller\PurgeDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;

final class UpdateUserProfileApiTest extends JsonApiTestCase
{
    use AuthorizedHeaderTrait;
    use Factories;
    use PurgeDatabaseTrait;

    /** @test */
    public function it_does_not_allow_to_update_user_profile_for_non_authenticated_user(): void
    {
        TestAppUsersStory::load();

        $customer = AppUserFactory::find(['username' => 'sylius'])->getCustomer();

        $this->client->request(method: 'PUT', uri: '/api/customers/'.$customer->getId(), server: ['CONTENT_TYPE' => 'application/json']);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'error/access_denied_response', Response::HTTP_UNAUTHORIZED);
    }

    /** @test */
    public function it_does_not_allows_to_update_another_profile(): void
    {
        TestAppUsersStory::load();

        $customer = AppUserFactory::find(['username' => 'monofony'])->getCustomer();

        $data =
            <<<EOT
        {
            "email": "inigo.montoya@prepare-to-die.com",
            "firstName": "Inigo",
            "lastName": "Montoya",
            "subscribedToNewsletter": true
        }
EOT;

        $this->client->request(method: 'PUT', uri: '/api/customers/'.$customer->getId(), server: self::$authorizedHeaderWithContentType, content: $data);

        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_FORBIDDEN);
    }

    /** @test */
    public function it_allows_to_update_user_profile(): void
    {
        TestAppUsersStory::load();

        $customer = AppUserFactory::find(['username' => 'sylius'])->getCustomer();

        $data =
            <<<EOT
        {
            "email": "inigo.montoya@prepare-to-die.com",
            "firstName": "Inigo",
            "lastName": "Montoya",
            "subscribedToNewsletter": true
        }
EOT;

        $this->client->request(method: 'PUT', uri: '/api/customers/'.$customer->getId(), server: self::$authorizedHeaderWithContentType, content: $data);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'customer/update_user_profile_response', Response::HTTP_OK);

        $this->assertLoginWithCredentials('inigo.montoya@prepare-to-die.com', 'sylius');
    }

    private function assertLoginWithCredentials(string $username, string $password): void
    {
        $data =
            <<<EOT
        {
            "username": "$username",
            "password": "$password"
        }
EOT;

        $this->client->request(method: 'POST', uri: '/api/authentication_token', server: ['CONTENT_TYPE' => 'application/json'], content: $data);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'authentication/new_access_token', Response::HTTP_OK);
    }
}
