<?php

declare(strict_types=1);

namespace App\Tests\Controller\Customer;

use App\Factory\AppUserFactory;
use App\Story\TestAppUsersStory;
use App\Tests\Controller\JsonApiTestCase;
use App\Tests\Controller\PurgeDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Foundry\Test\Factories;

class ResetPasswordApiTest extends JsonApiTestCase
{
    use Factories;
    use PurgeDatabaseTrait;

    /** @test */
    public function it_does_not_allow_to_request_password_without_required_data(): void
    {
        $data =
            <<<EOT
        {
            "email": ""
        }
EOT;

        $this->client->request('POST', '/api/request_password', [], [], ['CONTENT_TYPE' => 'application/json'], $data);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'customer/request_password_validation_response', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function it_allows_to_request_new_password(): void
    {
        TestAppUsersStory::load();

        $data =
            <<<EOT
        {
            "email": "api@sylius.com"
        }
EOT;

        $this->client->request('POST', '/api/request_password', [], [], ['CONTENT_TYPE' => 'application/json'], $data);

        $response = $this->client->getResponse();
        $this->assertEquals($response->getStatusCode(), Response::HTTP_NO_CONTENT);
    }

    /** @test */
    public function it_does_not_allow_to_reset_password_without_required_data(): void
    {
        TestAppUsersStory::load();

        $data =
            <<<EOT
        {
            "password": ""
        }
EOT;

        $this->client->request('PATCH', '/api/reset_password/t0ken', [], [], ['CONTENT_TYPE' => 'application/merge-patch+json'], $data);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'customer/reset_password_validation_response', Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /** @test */
    public function it_does_not_allow_to_reset_password_with_token_not_found(): void
    {
        $data =
            <<<EOT
        {
            "password": "newPassword"
        }
EOT;

        $this->client->request('PATCH', '/api/reset_password/t0ken', [], [], ['CONTENT_TYPE' => 'application/merge-patch+json'], $data);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'customer/token_not_found_validation_response', Response::HTTP_NOT_FOUND);
    }

    /** @test */
    public function it_does_not_allow_to_reset_password_with_token_expired(): void
    {
        TestAppUsersStory::load();

        $user = AppUserFactory::find(['username' => 'sylius']);
        $user->_disableAutoRefresh();
        $user->setPasswordRequestedAt(new \DateTimeImmutable('-1 day'));
        $user->setPasswordResetToken('expired_t0ken');
        $user->_save();

        $data =
            <<<EOT
        {
            "password": "newPassword"
        }
EOT;

        $this->client->request('PATCH', '/api/reset_password/expired_t0ken', [], [], ['CONTENT_TYPE' => 'application/merge-patch+json'], $data);

        $response = $this->client->getResponse();
        $this->assertResponse($response, 'customer/token_expired_validation_response', Response::HTTP_BAD_REQUEST);
    }

    /** @test */
    public function it_allows_to_reset_password(): void
    {
        TestAppUsersStory::load();

        $user = AppUserFactory::find(['username' => 'sylius']);
        $user->disableAutoRefresh();
        $user->setPasswordRequestedAt(new \DateTimeImmutable());
        $user->setPasswordResetToken('t0ken');
        $user->save();

        $data =
            <<<EOT
        {
            "password": "newPassword"
        }
EOT;

        $this->client->request('PATCH', '/api/reset_password/t0ken', [], [], ['CONTENT_TYPE' => 'application/merge-patch+json'], $data);

        $response = $this->client->getResponse();
        $this->assertResponseCode($response, Response::HTTP_NO_CONTENT);
    }
}
