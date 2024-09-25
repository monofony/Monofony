<?php

declare(strict_types=1);

namespace App\Entity\User;

use App\Entity\Media\File;
use Doctrine\ORM\Mapping as ORM;
use Monofony\Contracts\Core\Model\User\AdminAvatarInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
#[ORM\Table(name: 'app_admin_avatar')]
class AdminAvatar extends File implements AdminAvatarInterface
{
    #[Vich\UploadableField(mapping: 'admin_avatar', fileNameProperty: 'path')]
    #[\Symfony\Component\Validator\Constraints\File(maxSize: '6000000', mimeTypes: ['image/*'])]
    protected ?\SplFileInfo $file = null;
}
