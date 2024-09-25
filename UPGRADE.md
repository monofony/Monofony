## UPGRADE FOR `0.10.x`

### FROM `0.10.x` TO `0.11.x`

* the interface `Monofony\Contracts\Api\Identifier\AppUserIdentifierNormalizerInterface` has been changed due to Symfony changes.

    Before    
    ```php
    public function denormalize($data, $type, $format = null, array $context = []);
    public function supportsDenormalization($data, $type, $format = null);
    ```

    After 
    ```php
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed;
    public function supportsDenormalization(mixed $data, string $type, string $format = null, array $context = []): bool;
    ```

* Twig 3 does not support spaceless tag anymore

    Before
    ```twig
    {% spaceless %}
    <-- .... -->
    {% endspaceless %}
    ```

    After
    ```twig
    {% apply spaceless %}
    <-- .... -->
    {% endapply %}
    ```

* Annotations are not supported anymore on Symfony 7
    
    on `App\Message\ChangeAppUserPassword`

    Before
    ```php
    /**
     * @SecurityAssert\UserPassword(message="sylius.user.plainPassword.wrong_current")
     */
    ```

    After
    ```php
    #[SecurityAssert\UserPassword(message: 'sylius.user.plainPassword.wrong_current')]
    ```

    on `App\Message\RegisterAppUser`
 
    Before
    ```php
    /**
     * @CustomConstraints\UniqueAppUserEmail()
     */
    ```
  
    After
    ```php
    #[CustomConstraints\UniqueAppUserEmail]
    ```
  
    Before
    ```php
    /**
     * @Vich\UploadableField(mapping="admin_avatar", fileNameProperty="path")
     */
    ```
  
    After
    ```php
    #[Vich\UploadableField(mapping: 'admin_avatar', fileNameProperty: 'path')]
    ```

* `Symfony\Component\Security\Core\Security` has been removed from Symfony
    
    Replace by `Symfony\Bundle\SecurityBundle\Security`

* `Symfony\Component\Messenger\Handler\MessageHandlerInterface` interface has been removed

    Before
    ```php
    final class RegisterAppUserHandler implements MessageHandlerInterface
    ```
  
    After
    ```php
    #[AsMessageHandler]
    final class RegisterAppUserHandler
    ```

* Use new AsCommand attribute on your commands

    Before
    ```php
    class InstallAssetsCommand extends Command 
    {
        protected static $defaultName = 'app:install:assets';

        // ...

        protected function configure(): void
        {
            $this->setDescription('Installs all AppName assets.');        
        }
  
        // ...
    }
    ``` 
  
    After
    ```php
    #[AsCommand(
        name: 'app:install:assets',
        description: 'Installs all AppName assets.',
    )]
    class InstallAssetsCommand extends Command 
    {
        // ...
    }
    ```

* Doctrine Event subscribers have been removed

    Before
    ```php
    final class CanonicalizerSubscriber implements EventSubscriber 
    {
        // ...
  
        public function getSubscribedEvents(): array
        {
            return [
                Events::prePersist,
                Events::preUpdate,
            ];
        }
    }
    ```

    After
    ```php
    [AsDoctrineListener(event: Events::prePersist)]
    final class CanonicalizerSubscriber
    {
        // ...
    ```  

## UPGRADE FOR `0.9.x`

### FROM `0.8.x` TO `0.9.x`

Add these lines on `config/packages/monofony_admin.yaml`

```yaml
imports:
    - { resource: '@SyliusUiBundle/Resources/config/app/config.yml' }
 ```

On `config/packages/security.yaml`

Replace
```yaml
security:
    firewalls:
        admin:
            # [...]
            anonymous: true
        api:
            # [...]
            anonymous: true
            guard:
                authenticators:
                    - lexik_jwt_authentication.jwt_token_authenticator
```

with
```yaml
security:
    # Add this line
    enable_authenticator_manager: true

    # [...]

    firewalls:
        admin:
            # [...]
            # Remove that line
            # anonymous: true 
        api:
            # [...]
            entry_point: jwt
            jwt: true
            refresh_jwt:
                check_path: /api/token/refresh
```

And replace `IS_AUTHENTICATED_ANONYMOUSLY` with `PUBLIC_ACCESS`

Update controller on routes' configuration with two dots:

Example:
`_controller: App\Controller\DashboardController:indexAction` replaced by `_controller: App\Controller\DashboardController::indexAction`

On `config/routes/api.yaml`

Replace
```yaml
gesdinet_jwt_refresh_token:
    path: /api/token/refresh
    controller: gesdinet.jwtrefreshtoken::refresh
```

With
```yaml
api_refresh_token:
    path: /api/token/refresh
```

On `config/packages/test/framework.yaml`

Replace
```yaml
framework:
    test: ~
    session:
        storage_id: session.storage.mock_file
```

With
```yaml
framework:
    test: ~
    session:
        storage_factory_id: session.storage.factory.mock_file
```

On `config/packages/test/monofony_core.yaml`

Replace
```yaml
swiftmailer:
    spool:
        type: file
        path: "%kernel.cache_dir%/spool"
```

With
```yaml
framework:
    cache:
        pools:
            test.mailer_pool:
                adapter: cache.adapter.filesystem
```

on `src/EventSubscriber/CanonicalaizerSubscriber.php`

Replace
```php
} elseif ($item instanceof UserInterface) {
```

With
```php
} elseif ($item instanceof UserInterface && method_exists($item, 'getUsername')) {
```

on `src/EventSubscriber/DefaultUsernameORMSubscriber.php`

Replace
```php
if ($customer->getEmail() === $user->getUsername() && $customer->getEmailCanonical() === $user->getUsernameCanonical()) {
    continue;
}
```

With
```php
if (!method_exists($user, 'getUsername')) {
    continue;
}
if ($customer->getEmail() === $user->getUsername() && $customer->getEmailCanonical() === $user->getUsernameCanonical()) {
    continue;
}
```

on `src/Security/UserChecker.php`

Replace
```php
if (!$user instanceof User) {
    return;
}
```

With
```php
if (!$user instanceof AdvancedUserInterface || !method_exists($user, 'isCredentialsNonExpired')
    return;
}
```

And add this line on imports

```php
use SyliusLabs\Polyfill\Symfony\Security\Core\User\AdvancedUserInterface;
```

## UPGRADE FOR `0.4.x`

### FROM `0.2.x` TO `0.4.x`

First use composer 2 using these commands:
```bash
composer self-update
composer self -v
```

Ensure you requires Symfony 4.4.*:
```bash
composer config extra.symfony.require "4.4.*"
```

Ensure you use stable releases:
```bash
composer config minimum-stability "stable"
```

Update your dependencies:
```bash
composer remove \
    monofony/api-bundle \
    monofony/front-bundle \
    monofony/admin-bundle \
    monofony/core-bundle \
    monofony/fixtures-plugin \
    --no-update --no-scripts --no-plugins
```

```bash
composer require \
    php:^7.3 \
    monofony/core-pack \
    monofony/api-pack \
    monofony/admin-pack \
    monofony/front-pack \
    symfony/dotenv:4.4.* \
    symfony/flex:^1.9 \
    symfony/monolog-bundle:^3.1 \
    symfony/webpack-encore-bundle:^1.7 \
    --no-update --no-scripts --no-plugins
```

```bash
composer require --dev monofony/test-pack:^0.4 --no-update --no-scripts --no-plugins
```

```bash
composer require \
    eightpoints/guzzle-bundle:^7.3 \
    sensio/framework-extra-bundle:^5.1 \
    sensiolabs/security-checker:^5.0 \
    sylius/mailer-bundle \
    twig/extensions \
    --no-update --no-scripts --no-plugins
```

Copy the script to migrate the code:
```bash
php -r "copy('https://raw.githubusercontent.com/Monofony/Monofony/0.x/bin/upgrade-to-0.4', 'bin/upgrade-to-0.4');"
```

Make it executuable:
```bash
chmod a+x bin/upgrade-to-0.4
```

Run it:
```bash
bin/upgrade-to-0.4
```

And finally run composer update:
```bash
composer update
```
