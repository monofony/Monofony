<?php

declare(strict_types=1);

namespace spec\App\EventSubscriber;

use Monofony\Contracts\Core\Model\Customer\CustomerInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\User\Model\UserInterface;

final class DefaultUsernameORMListenerSpec extends ObjectBehavior
{
    function it_sets_usernames_on_customer_create(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$customer]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $user->getUsername()->willReturn(null);
        $user->getUsernameCanonical()->willReturn(null);
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);
        $unitOfWork->recomputeSingleEntityChangeSet($userMetadata, $user)->shouldBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_updates_usernames_on_customer_email_change(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $user->getUsername()->willReturn('user+extra@email.com');
        $user->getUsernameCanonical()->willReturn('customer@email.com');
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);
        $unitOfWork->recomputeSingleEntityChangeSet($userMetadata, $user)->shouldBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_updates_usernames_on_customer_email_canonical_change(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $user->getUsername()->willReturn('customer+extra@email.com');
        $user->getUsernameCanonical()->willReturn('user@email.com');
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);
        $unitOfWork->recomputeSingleEntityChangeSet($userMetadata, $user)->shouldBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_not_update_usernames_when_customer_emails_are_the_same(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $user->getUsername()->willReturn('customer+extra@email.com');
        $user->getUsernameCanonical()->willReturn('customer@email.com');
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $user->setUsername(Argument::any())->shouldNotBeCalled();
        $user->setUsernameCanonical(Argument::any())->shouldNotBeCalled();

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_on_customer_create_when_no_user_associated(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customer
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$customer]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $customer->getUser()->willReturn(null);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_on_customer_update_when_no_user_associated(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customer
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customer]);

        $customer->getUser()->willReturn(null);
        $customer->getEmail()->willReturn('customer@email.com');

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_when_there_are_no_objects_scheduled_in_the_unit_of_work(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_does_nothing_when_there_are_other_objects_than_customer(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        \stdClass $stdObject,
        \stdClass $stdObject2
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$stdObject]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$stdObject2]);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldNotBeCalled();

        $this->onFlush($onFlushEventArgs);
    }

    function it_skips_objects_other_than_customers(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        \stdClass $stdObject,
        CustomerInterface $customer,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $user->getUsername()->willReturn('customer+extra@email.com');
        $user->getUsernameCanonical()->willReturn('user@email.com');
        $customer->getUser()->willReturn($user);
        $customer->getEmail()->willReturn('customer+extra@email.com');
        $customer->getEmailCanonical()->willReturn('customer@email.com');

        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$stdObject, $customer]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldBeCalledOnce();

        $this->onFlush($onFlushEventArgs);
    }

    function it_skips_customers_without_users_associated(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customerWithoutUser,
        CustomerInterface $customerWithUser,
        UserInterface $user,
        ClassMetadata $userMetadata
    ): void {
        $customerWithoutUser->getUser()->willReturn(null);

        $user->getUsername()->willReturn('customer+extra@email.com');
        $user->getUsernameCanonical()->willReturn('user@email.com');
        $customerWithUser->getUser()->willReturn($user);
        $customerWithUser->getEmail()->willReturn('customer+extra@email.com');
        $customerWithUser->getEmailCanonical()->willReturn('customer@email.com');

        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([$customerWithoutUser, $customerWithUser]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([]);

        $user->setUsername('customer+extra@email.com')->shouldBeCalled();
        $user->setUsernameCanonical('customer@email.com')->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($user->getWrappedObject()))->willReturn($userMetadata);

        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldBeCalledOnce();

        $this->onFlush($onFlushEventArgs);
    }

    function it_skips_customers_with_same_emails(
        OnFlushEventArgs $onFlushEventArgs,
        EntityManager $entityManager,
        UnitOfWork $unitOfWork,
        CustomerInterface $customerWithSameEmail,
        CustomerInterface $customerWithDifferentEmail,
        UserInterface $userWithSameEmail,
        UserInterface $userWithDifferentEmail,
        ClassMetadata $userMetadata
    ): void {
        $onFlushEventArgs->getObjectManager()->willReturn($entityManager);
        $entityManager->getUnitOfWork()->willReturn($unitOfWork);

        $unitOfWork->getScheduledEntityInsertions()->willReturn([]);
        $unitOfWork->getScheduledEntityUpdates()->willReturn([$customerWithSameEmail, $customerWithDifferentEmail]);

        $userWithSameEmail->getUsername()->willReturn('customer+extra@email.com');
        $userWithSameEmail->getUsernameCanonical()->willReturn('customer@email.com');
        $customerWithSameEmail->getUser()->willReturn($userWithSameEmail);
        $customerWithSameEmail->getEmail()->willReturn('customer+extra@email.com');
        $customerWithSameEmail->getEmailCanonical()->willReturn('customer@email.com');

        $userWithDifferentEmail->getUsername()->willReturn('customer+extra@email.com');
        $userWithDifferentEmail->getUsernameCanonical()->willReturn('user@email.com');
        $customerWithDifferentEmail->getUser()->willReturn($userWithDifferentEmail);
        $customerWithDifferentEmail->getEmail()->willReturn('customer+extra@email.com');
        $customerWithDifferentEmail->getEmailCanonical()->willReturn('customer@email.com');

        $userWithSameEmail->setUsername(Argument::any())->shouldNotBeCalled();
        $userWithSameEmail->setUsernameCanonical(Argument::any())->shouldNotBeCalled();

        $userWithDifferentEmail->setUsername(Argument::any())->shouldBeCalled();
        $userWithDifferentEmail->setUsernameCanonical(Argument::any())->shouldBeCalled();

        $entityManager->getClassMetadata(get_class($userWithDifferentEmail->getWrappedObject()))->willReturn($userMetadata);
        $unitOfWork->recomputeSingleEntityChangeSet(Argument::cetera())->shouldBeCalledOnce();

        $this->onFlush($onFlushEventArgs);
    }
}
