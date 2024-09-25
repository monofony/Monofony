<?php

declare(strict_types=1);

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
final class UniqueAppUserEmail extends Constraint
{
    public string $message = 'sylius.user.email.unique';
}
