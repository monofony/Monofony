<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\RegisterAppUser;
use App\Provider\CustomerProviderInterface;
use Monofony\Contracts\Core\Model\User\AppUserInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RegisterAppUserHandler
{
    public function __construct(
        private FactoryInterface $appUserFactory,
        private RepositoryInterface $appUserRepository,
        private CustomerProviderInterface $customerProvider,
    ) {
    }

    public function __invoke(RegisterAppUser $command): void
    {
        /** @var AppUserInterface $user */
        $user = $this->appUserFactory->createNew();
        $user->setPlainPassword($command->password);

        $customer = $this->customerProvider->provide($command->email);
        if (null !== $customer->getUser()) {
            throw new \DomainException(sprintf('User with email "%s" is already registered.', $command->email));
        }

        $customer->setFirstName($command->firstName);
        $customer->setLastName($command->lastName);
        $customer->setPhoneNumber($command->phoneNumber);
        $customer->setUser($user);

        $this->appUserRepository->add($user);
    }
}
