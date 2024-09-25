<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Sylius\Component\Customer\Model\CustomerInterface;
use Sylius\Component\User\Canonicalizer\CanonicalizerInterface;
use Sylius\Component\User\Model\UserInterface;

#[AsDoctrineListener(event: Events::prePersist)]
final class CanonicalizerListener
{
    public function __construct(private CanonicalizerInterface $canonicalizer)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function canonicalize(LifecycleEventArgs $event): void
    {
        $item = $event->getObject();

        if ($item instanceof CustomerInterface) {
            $item->setEmailCanonical($this->canonicalizer->canonicalize($item->getEmail()));
        } elseif ($item instanceof UserInterface && method_exists($item, 'getUsername')) {
            $item->setUsernameCanonical($this->canonicalizer->canonicalize($item->getUsername()));
            $item->setEmailCanonical($this->canonicalizer->canonicalize($item->getEmail()));
        }
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        $this->canonicalize($event);
    }

    public function preUpdate(LifecycleEventArgs $event): void
    {
        $this->canonicalize($event);
    }
}
