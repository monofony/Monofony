<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\Customer\Customer;
use App\Entity\User\AppUser;
use Monofony\Contracts\Core\Model\User\AppUserInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<AppUser>
 */
final class AppUserFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array
    {
        return [
            'customer' => null,
            'username' => self::faker()->userName(),
            'email' => self::faker()->email(),
            'first_name' => self::faker()->firstName(),
            'last_name' => self::faker()->lastName(),
            'enabled' => true,
            'password' => 'password123',
            'roles' => [],
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->beforeInstantiate(function (array $attributes): array {
                $customer = $attributes['customer'];
                $roles = $attributes['roles'];
                $roles[] = 'ROLE_USER';
                $attributes['roles'] = array_unique($roles);

                if (null === $customer) {
                    $customer = new Customer();
                    $customer->setEmail($attributes['email']);
                    $customer->setFirstName($attributes['first_name']);
                    $customer->setLastName($attributes['last_name']);
                }

                unset($attributes['email']);
                unset($attributes['first_name']);
                unset($attributes['last_name']);

                $attributes['customer'] = $customer;

                return $attributes;
            })
            ->afterInstantiate(function (AppUserInterface $appUser) {
                $appUser->setPlainPassword($appUser->getPassword());
                $appUser->setPassword(null);
            })
        ;
    }

    public static function class(): string
    {
        return AppUser::class;
    }
}
