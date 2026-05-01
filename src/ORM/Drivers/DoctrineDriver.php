<?php

declare(strict_types=1);

namespace Valhalla\Framework\ORM\Drivers;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\DBAL\DriverManager;
use RuntimeException;
use Valhalla\Framework\CLI\Support\CommandSupport;
use Valhalla\Framework\ORM\Contracts\OrmDriverInterface;

final class DoctrineDriver implements OrmDriverInterface
{
    private ?EntityManager $entityManager = null;

    public function __construct(private readonly string $basePath)
    {
    }

    public function boot(array $config): void
    {
        if ($this->entityManager !== null) {
            return;
        }

        if (! class_exists(ORMSetup::class) || ! class_exists(EntityManager::class)) {
            throw new RuntimeException('Doctrine ORM is not installed. Run [valhalla orm:install doctrine] first.');
        }

        $connection = $this->resolveConnection($config);
        $ormConfig = ORMSetup::createAttributeMetadataConfiguration([
            $this->basePath.'/src/Entities',
        ], (bool) ($config['debug'] ?? true));
        $this->entityManager = new EntityManager(DriverManager::getConnection($connection, $ormConfig), $ormConfig);
    }

    public function migrate(): void
    {
        $this->runDoctrineCommand('vendor/bin/doctrine-migrations migrate --no-interaction');
    }

    public function rollback(): void
    {
        $this->runDoctrineCommand('vendor/bin/doctrine-migrations migrate prev --no-interaction');
    }

    public function makeModel(string $name): string
    {
        $class = CommandSupport::studly($name);
        $table = CommandSupport::snake($name);
        if (! str_ends_with($table, 's')) {
            $table .= 's';
        }

        $path = $this->basePath.'/src/Entities/'.$class.'.php';
        @mkdir(dirname($path), 0777, true);

        file_put_contents($path, <<<PHP
<?php

declare(strict_types=1);

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: '{$table}')]
final class {$class}
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int \$id = null;

    public function getId(): ?int
    {
        return \$this->id;
    }
}

// Run [valhalla migrate:diff] after adding columns or relationships.
PHP);

        return $path;
    }

    public function makeMigration(string $name): string
    {
        $this->diff();

        return $this->basePath.'/database/migrations';
    }

    public function diff(): void
    {
        $this->runDoctrineCommand('vendor/bin/doctrine-migrations diff --no-interaction');
    }

    private function resolveConnection(array $config): array
    {
        $default = (string) ($config['default'] ?? 'mysql');
        $connections = (array) ($config['connections'] ?? []);

        if (! isset($connections[$default]) || ! is_array($connections[$default])) {
            throw new RuntimeException(sprintf('Database connection [%s] is not configured.', $default));
        }

        $connection = $connections[$default];
        $connection['dbname'] = $connection['database'] ?? null;
        unset($connection['database']);

        return $connection;
    }

    private function runDoctrineCommand(string $command): void
    {
        if (! is_file($this->basePath.'/vendor/bin/doctrine-migrations')) {
            throw new RuntimeException('Doctrine migrations binary not found. Run [valhalla orm:install doctrine] first.');
        }

        $exitCode = CommandSupport::run($command, $this->basePath);

        if ($exitCode !== 0) {
            throw new RuntimeException('Doctrine command failed. Review the output above for details.');
        }
    }
}
