<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\ResetPassword;
use Doctrine\ORM\EntityManagerInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\User\Model\UserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Webmozart\Assert\Assert;

#[AsMessageHandler]
final class ResetPasswordHandler
{
    public function __construct(
        private RepositoryInterface $appUserRepository,
        private EntityManagerInterface $entityManager,
        private RequestStack $requestStack,
        private array $syliusResources,
    ) {
    }

    public function __invoke(ResetPassword $message): void
    {
        /** @var UserInterface|null $user */
        $user = $this->appUserRepository->findOneBy(['passwordResetToken' => $this->getToken()]);

        if (null === $user) {
            throw new NotFoundHttpException('Token not found.');
        }

        $lifetime = new \DateInterval($this->getTtl());
        if (!$user->isPasswordRequestNonExpired($lifetime)) {
            $this->handleExpiredToken($user);

            return;
        }

        $user->setPlainPassword($message->password);
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);

        $this->entityManager->flush();
    }

    private function getToken(): string
    {
        /** @var Request $request */
        $request = $this->requestStack->getCurrentRequest();
        Assert::notNull($request);

        /** @var string $token */
        $token = $request->attributes->get('token');
        Assert::notNull($token);

        return $token;
    }

    private function getTtl(): string
    {
        /** @var string $ttl */
        $ttl = $this->syliusResources['sylius.app_user']['resetting']['token']['ttl'] ?? null;
        Assert::notNull($ttl, 'Token ttl was not found but it should.');

        return $ttl;
    }

    protected function handleExpiredToken(UserInterface $user): void
    {
        $user->setPasswordResetToken(null);
        $user->setPasswordRequestedAt(null);

        $this->entityManager->flush();

        throw new BadRequestHttpException('Token expired.');
    }
}
