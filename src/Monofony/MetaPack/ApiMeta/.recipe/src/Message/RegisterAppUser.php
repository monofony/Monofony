<?php

declare(strict_types=1);

namespace App\Message;

use App\Validator\Constraints as CustomConstraints;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

final class RegisterAppUser
{
    #[CustomConstraints\UniqueAppUserEmail]
    #[NotBlank(message: 'sylius.customer.email.not_blank')]
    #[Email(message: 'sylius.customer.email.invalid', mode: 'strict')]
    #[Length(max: 254, maxMessage: 'sylius.customer.email.max')]
    #[Groups(groups: ['customer:write'])]
    public ?string $email = null;

    #[NotBlank(message: 'sylius.user.plainPassword.not_blank')]
    #[Length(min: 4, max: 254, minMessage: 'sylius.user.password.min', maxMessage: 'sylius.user.password.max')]
    #[Groups(groups: ['customer:write'])]
    public ?string $password = null;

    #[Groups(groups: ['customer:write'])]
    public ?string $firstName = null;

    #[Groups(groups: ['customer:write'])]
    public ?string $lastName = null;

    #[Groups(groups: ['customer:write'])]
    public ?string $phoneNumber = null;

    public function __construct(
        ?string $email = null,
        ?string $password = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $phoneNumber = null
    ) {
        $this->email = $email;
        $this->password = $password;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->phoneNumber = $phoneNumber;
    }
}
