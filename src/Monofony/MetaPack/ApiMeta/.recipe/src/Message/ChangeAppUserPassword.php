<?php

declare(strict_types=1);

namespace App\Message;

use Symfony\Component\Security\Core\Validator\Constraints as SecurityAssert;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ChangeAppUserPassword implements AppUserIdAwareInterface
{
    public ?int $appUserId = null;

    #[SecurityAssert\UserPassword(message: 'sylius.user.plainPassword.wrong_current')]
    #[NotBlank]
    #[Groups(groups: ['customer:password:write'])]
    public ?string $currentPassword;

    #[NotBlank]
    #[Groups(groups: ['customer:password:write'])]
    public ?string $newPassword;

    public function __construct(?string $currentPassword = null, ?string $newPassword = null)
    {
        $this->currentPassword = $currentPassword;
        $this->newPassword = $newPassword;
    }

    public function getAppUserId(): ?int
    {
        return $this->appUserId;
    }

    public function setAppUserId(?int $appUserId): void
    {
        $this->appUserId = $appUserId;
    }
}
