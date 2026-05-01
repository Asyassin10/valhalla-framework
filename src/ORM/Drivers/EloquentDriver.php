<?php

declare(strict_types=1);

namespace Valhalla\Framework\ORM\Drivers;

use Illuminate\Database\Capsule\Manager as Capsule;
use RuntimeException;
use Valhalla\Framework\CLI\Support\CommandSupport;
use Valhalla\Framework\ORM\Contracts\OrmDriverInterface;

final class EloquentDriver implements OrmDriverInterface
{
    private bool $booted = false;

    public function __construct(private readonly string $basePath)
    {
    }

    public function boot(array $config): void
    {
        if ($this->booted) {
            return;
        }

        if (! class_exists(Capsule::class)) {
            throw new RuntimeException('Eloquent ORM is not installed. Run [valhalla orm:install eloquent] first.');
        }

        $connection = $this->resolveConnection($config);
        $capsule = new Capsule();
        $capsule->addConnection($connection);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();
        $this->booted = true;
    }

    public function migrate(): void
    {
        $this->requireCapsule();
        $files = glob($this->basePath.'/database/migrations/*.php') ?: [];
        sort($files);

        foreach ($files as $file) {
            $migration = require $file;

            if (is_object($migration) && method_exists($migration, 'up')) {
                $migration->up();
            }
        }
    }

    public function rollback(): void
    {
        $this->requireCapsule();
        $files = glob($this->basePath.'/database/migrations/*.php') ?: [];
        sort($files);
        $file = array_pop($files);

        if (! is_string($file)) {
            return;
        }

        $migration = require $file;

        if (is_object($migration) && method_exists($migration, 'down')) {
            $migration->down();
        }
    }

    public function makeModel(string $name): string
    {
        $class = CommandSupport::studly($name);
        $path = $this->basePath.'/src/Models/'.$class.'.php';
        @mkdir(dirname($path), 0777, true);

        file_put_contents($path, <<<PHP
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

final class {$class} extends Model
{
    protected array \$guarded = [];
}
PHP);

        return $path;
    }

    public function makeMigration(string $name): string
    {
        $table = CommandSupport::snake($name);
        if (! str_ends_with($table, 's')) {
            $table .= 's';
        }

        $file = sprintf(
            '%s/database/migrations/%s_%s.php',
            $this->basePath,
            date('Y_m_d_His'),
            CommandSupport::snake($name)
        );
        @mkdir(dirname($file), 0777, true);

        file_put_contents($file, <<<PHP
<?php

declare(strict_types=1);

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class
{
    public function up(): void
    {
        Capsule::schema()->create('{$table}', function (Blueprint \$table): void {
            \$table->id();
            \$table->timestamps();
        });
    }

    public function down(): void
    {
        Capsule::schema()->dropIfExists('{$table}');
    }
};
PHP);

        return $file;
    }

    private function resolveConnection(array $config): array
    {
        $default = (string) ($config['default'] ?? 'mysql');
        $connections = (array) ($config['connections'] ?? []);

        if (! isset($connections[$default]) || ! is_array($connections[$default])) {
            throw new RuntimeException(sprintf('Database connection [%s] is not configured.', $default));
        }

        return $connections[$default];
    }

    private function requireCapsule(): void
    {
        if (! class_exists(Capsule::class)) {
            throw new RuntimeException('Eloquent ORM is not installed. Run [valhalla orm:install eloquent] first.');
        }
    }
}
