# Remove Monofony guide

## Applications using the AdminPack

### Configure the admin menu

On `AdminMenuBuilder.php`, remove dependency to `AdminMenuBuilderInterface` and add an autoconfigure tag to configure the Knp menu yourself.

```php
// src/Menu/AdminMenuBuilder.php
// ...
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'knp_menu.menu_builder', attributes: [
    'method' => 'createMenu',
    'alias' => 'app.admin.main',
])]
final class AdminMenuBuilder
// ...
```

### Configure the dashboard statistics

Add the following `StatisticInterface`:

```php
// src/Dashboard/DashboardStatisticsProvider.php
declare(strict_types=1);

namespace App\Dashboard;

use App\Dashboard\Statistics\StatisticInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class DashboardStatisticsProvider
{
    public function __construct(
        #[TaggedIterator(tag: 'app.dashboard_statistic')]
        private iterable $statistics
    ) {
    }

    public function getStatistics(): array
    {
        /** @var string[] $statistics */
        $statistics = [];

        foreach ($this->statistics as $statistic) {
            if (!$statistic instanceof StatisticInterface) {
                throw new \LogicException(sprintf('Class %s must implement %s', get_class($statistic), StatisticInterface::class));
            }

            $statistics[] = $statistic->generate();
        }

        return $statistics;
    }
}
```

Add the following `DashboardStatisticsProvider`:

```php
declare(strict_types=1);

namespace App\Dashboard\Statistics;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: 'app.dashboard_statistic')]
interface StatisticInterface
{
    public function generate(): string;
}
```

Implements the new StatisticInterface you just created before on all your services that implement `StatisticInterface`

```diff
-use Monofony\Component\Admin\Dashboard\Statistics\StatisticInterface;
+use App\Dashboard\Statistics\StatisticInterface;
```

Update the DashboardController

```diff
// src/Controller/DashboardController.php
// ...
-use Monofony\Contracts\Admin\Dashboard\DashboardStatisticsProviderInterface;
+use App\Dashboard\DashboardStatisticsProvider;
// ...

final class DashboardController
{
    public function __construct(
        -private DashboardStatisticsProviderInterface $statisticsProvider,
        +private DashboardStatisticsProvider $statisticsProvider,
        private Environment $twig,
    ) {
    }

    // ...
}
```

### Add some autowiring configuration

```yaml
# config/services.yaml
    # ...
    services:
        # ...
        Doctrine\Persistence\ObjectManager: '@doctrine.orm.entity_manager'
        Sylius\Component\User\Security\Generator\GeneratorInterface: '@sylius.app_user.token_generator.email_verification'
```

### Remove Monofony packages

```shell
composer remove monofony/admin monofony/admin-contracts monofony/core monofony/core-bundle monofony/core-contracts
```
