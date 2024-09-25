<?php

declare(strict_types=1);

namespace App\Factory;

use App\Entity\User\AdminUser;
use Monofony\Contracts\Core\Model\User\AdminUserInterface;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<AdminUser>
 */
final class AdminUserFactory extends PersistentProxyObjectFactory
{
    protected function defaults(): array
    {
        return [
            'email' => self::faker()->email(),
            'username' => self::faker()->userName(),
            'enabled' => true,
            'password' => 'password',
            'first_name' => self::faker()->firstName(),
            'last_name' => self::faker()->lastName(),
        ];
    }

    protected function initialize(): self
    {
        return $this
            ->afterInstantiate(function (AdminUserInterface $adminUser) {
                $adminUser->setPlainPassword($adminUser->getPassword());
                $adminUser->setPassword(null);
            })
        ;
    }

    public static function class(): string
    {
        return AdminUser::class;
    }
}
