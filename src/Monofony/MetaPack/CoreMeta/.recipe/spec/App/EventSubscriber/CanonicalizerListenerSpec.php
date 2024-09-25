<?php

declare(strict_types=1);

namespace spec\App\EventSubscriber;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\User\Canonicalizer\CanonicalizerInterface;
use Sylius\Component\User\Model\UserInterface;

final class CanonicalizerListenerSpec extends ObjectBehavior
{
    function let(CanonicalizerInterface $canonicalizer): void
    {
        $this->beConstructedWith($canonicalizer);
    }

    function it_canonicalize_user_username($canonicalizer, LifecycleEventArgs $event, UserInterface $user): void
    {
        $event->getObject()->willReturn($user);
        $user->getUsername()->willReturn('testUser');
        $user->getEmail()->willReturn('test@email.com');

        $user->setUsernameCanonical('testuser')->shouldBeCalled();
        $user->setEmailCanonical('test@email.com')->shouldBeCalled();
        $canonicalizer->canonicalize('testUser')->willReturn('testuser')->shouldBeCalled();
        $canonicalizer->canonicalize('test@email.com')->willReturn('test@email.com')->shouldBeCalled();

        $this->canonicalize($event);
    }

    function it_canonicalize_user_username_on_pre_persist_doctrine_event($canonicalizer, LifecycleEventArgs $event, UserInterface $user): void
    {
        $event->getObject()->willReturn($user);
        $user->getUsername()->willReturn('testUser');
        $user->getEmail()->willReturn('test@email.com');

        $user->setUsernameCanonical('testuser')->shouldBeCalled();
        $user->setEmailCanonical('test@email.com')->shouldBeCalled();
        $canonicalizer->canonicalize('testUser')->willReturn('testuser')->shouldBeCalled();
        $canonicalizer->canonicalize('test@email.com')->willReturn('test@email.com')->shouldBeCalled();

        $this->prePersist($event);
    }

    function it_canonicalize_customer_email_on_pre_persist_doctrine_event($canonicalizer, LifecycleEventArgs $event, CustomerInterface $customer): void
    {
        $event->getObject()->willReturn($customer);
        $customer->getEmail()->willReturn('testUser@Email.com');

        $customer->setEmailCanonical('testuser@email.com')->shouldBeCalled();
        $canonicalizer->canonicalize('testUser@Email.com')->willReturn('testuser@email.com')->shouldBeCalled();

        $this->prePersist($event);
    }

    function it_canonicalize_user_username_on_pre_update_doctrine_event($canonicalizer, LifecycleEventArgs $event, UserInterface $user): void
    {
        $event->getObject()->willReturn($user);
        $user->getUsername()->willReturn('testUser');
        $user->getEmail()->willReturn('test@email.com');

        $user->setUsernameCanonical('testuser')->shouldBeCalled();
        $user->setEmailCanonical('test@email.com')->shouldBeCalled();
        $canonicalizer->canonicalize('testUser')->willReturn('testuser')->shouldBeCalled();
        $canonicalizer->canonicalize('test@email.com')->willReturn('test@email.com')->shouldBeCalled();

        $this->preUpdate($event);
    }

    function it_canonicalize_customer_email_on_pre_update_doctrine_event($canonicalizer, LifecycleEventArgs $event, CustomerInterface $customer): void
    {
        $event->getObject()->willReturn($customer);
        $customer->getEmail()->willReturn('testUser@Email.com');

        $customer->setEmailCanonical('testuser@email.com')->shouldBeCalled();
        $canonicalizer->canonicalize('testUser@Email.com')->willReturn('testuser@email.com')->shouldBeCalled();

        $this->preUpdate($event);
    }

    function it_canonicalize_only_user_or_customer_interface_implementation_on_pre_presist($canonicalizer, LifecycleEventArgs $event): void
    {
        $item = new \stdClass();
        $event->getObject()->willReturn($item);

        $canonicalizer->canonicalize(Argument::any())->shouldNotBeCalled();

        $this->prePersist($event);
    }

    function it_canonicalize_only_user_or_customer_interface_implementation_on_pre_update($canonicalizer, LifecycleEventArgs $event): void
    {
        $item = new \stdClass();
        $event->getObject()->willReturn($item);

        $canonicalizer->canonicalize(Argument::any())->shouldNotBeCalled();

        $this->preUpdate($event);
    }
}
