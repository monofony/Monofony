<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ResetPasswordRequest;
use Doctrine\ORM\EntityManagerInterface;
use Monofony\Contracts\Core\Model\Customer\CustomerInterface;
use Sylius\Bundle\UserBundle\UserEvents;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\User\Security\Generator\GeneratorInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsMessageHandler]
final class ResetPasswordRequestHandler
{
    public function __construct(
        private RepositoryInterface $customerRepository,
        private GeneratorInterface $generator,
        private EntityManagerInterface $entityManager,
        private EventDispatcherInterface $eventDispatcher,
    ) {
    }

    public function __invoke(ResetPasswordRequest $message): void
    {
        /** @var CustomerInterface|null $customer */
        $customer = $this->customerRepository->findOneBy(['emailCanonical' => $message->email]);

        if (null === $customer) {
            return;
        }

        if (null === $user = $customer->getUser()) {
            return;
        }

        $user->setPasswordResetToken($this->generator->generate());
        $user->setPasswordRequestedAt(new \DateTime());

        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new GenericEvent($user), UserEvents::REQUEST_RESET_PASSWORD_TOKEN);
    }
}
