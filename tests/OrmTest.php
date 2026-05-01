<?php

declare(strict_types=1);

namespace Valhalla\Framework\Tests;

use PHPUnit\Framework\TestCase;
use Valhalla\Framework\ORM\Drivers\DoctrineDriver;
use Valhalla\Framework\ORM\Drivers\EloquentDriver;
use Valhalla\Framework\ORM\OrmManager;
use Valhalla\Framework\Support\Config;

final class OrmTest extends TestCase
{
    private array $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            $this->deleteDirectory($path);
        }
    }

    public function test_eloquent_driver_scaffolds_model_and_migration(): void
    {
        $basePath = $this->makeProject();
        $driver = new EloquentDriver($basePath);

        $model = $driver->makeModel('Order');
        $migration = $driver->makeMigration('create_order');

        self::assertFileExists($model);
        self::assertFileExists($migration);
        self::assertStringContainsString('namespace App\\Models;', (string) file_get_contents($model));
        self::assertStringContainsString('protected array $guarded = [];', (string) file_get_contents($model));
        self::assertStringContainsString("Capsule::schema()->create('create_orders'", (string) file_get_contents($migration));
    }

    public function test_doctrine_driver_scaffolds_entity(): void
    {
        $basePath = $this->makeProject();
        $driver = new DoctrineDriver($basePath);

        $entity = $driver->makeModel('Invoice');

        self::assertFileExists($entity);
        $contents = (string) file_get_contents($entity);
        self::assertStringContainsString('namespace App\\Entities;', $contents);
        self::assertStringContainsString('#[ORM\\Entity]', $contents);
        self::assertStringContainsString('Run [valhalla migrate:diff] after adding columns or relationships.', $contents);
    }

    public function test_orm_manager_detects_missing_installation(): void
    {
        $basePath = $this->makeProject();
        @mkdir($basePath.'/config', 0777, true);
        file_put_contents($basePath.'/config/database.php', <<<'PHP'
<?php

declare(strict_types=1);

return [
    'default' => 'mysql',
    'connections' => [],
];
PHP);

        $config = new Config($basePath);
        $config->load();
        $manager = new OrmManager($config, $basePath);

        self::assertFalse($manager->installed());
    }

    private function makeProject(): string
    {
        $path = sys_get_temp_dir().'/valhalla-orm-'.bin2hex(random_bytes(4));
        @mkdir($path.'/src', 0777, true);
        @mkdir($path.'/database/migrations', 0777, true);
        $this->paths[] = $path;

        return $path;
    }

    private function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
                continue;
            }

            unlink($item->getPathname());
        }

        rmdir($path);
    }
}
