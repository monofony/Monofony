<?php

declare(strict_types=1);

namespace App\Identifier;

use Monofony\Contracts\Api\Identifier\AppUserIdentifierNormalizerInterface;
use Monofony\Contracts\Core\Model\User\AppUserInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

final class AppUserIdentifierNormalizer implements AppUserIdentifierNormalizerInterface
{
    public function __construct(private Security $security)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): string
    {
        $user = $this->security->getUser();

        if (null === $user || !$user instanceof AppUserInterface) {
            throw new AccessDeniedHttpException();
        }

        return (string) $user->getCustomer()->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return 'me' === $data;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['object' => true];
    }
}
