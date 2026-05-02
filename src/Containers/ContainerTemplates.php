<?php

declare(strict_types=1);

namespace Valhalla\Framework\Containers;

final class ContainerTemplates
{
    public static function config(string $runtime): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

return [
    'runtime' => '{$runtime}',
];
PHP;
    }

    public static function dockerfile(string $phpVersion, ?string $database, ?string $cache): string
    {
        $packages = ['fcgi', 'git', 'unzip', 'zip'];
        $extensions = ['pdo'];

        if ($database === 'mysql') {
            $extensions[] = 'pdo_mysql';
        }

        if ($database === 'postgres') {
            $packages[] = 'postgresql-dev';
            $extensions[] = 'pdo_pgsql';
        }

        if ($cache === 'redis') {
            $packages[] = '$PHPIZE_DEPS';
        }

        $packageLine = implode(' ', array_unique($packages));
        $extensionLine = implode(' ', array_unique($extensions));
        $redisInstall = $cache === 'redis'
            ? "RUN pecl install redis && docker-php-ext-enable redis\n"
            : '';

        return <<<DOCKER
FROM composer:2 AS composer

FROM php:{$phpVersion}-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache {$packageLine}
RUN docker-php-ext-install {$extensionLine}
{$redisInstall}COPY --from=composer /usr/bin/composer /usr/bin/composer
COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader
RUN chown -R www-data:www-data /var/www/html

CMD ["php-fpm"]
DOCKER;
    }

    public static function compose(array $options): string
    {
        $services = [];
        $volumes = [];
        $runtimeFile = $options['runtime'] === 'docker' ? 'docker-compose.yml' : 'podman-compose.yml';
        $composeHeader = "services:\n";

        $services[] = <<<YAML
  app:
    build:
      context: ..
      dockerfile: docker/Dockerfile
    working_dir: /var/www/html
    volumes:
      - ../:/var/www/html
YAML;

        $dependsOn = [];
        if ($options['database'] === 'mysql') {
            $dependsOn[] = 'mysql';
        }
        if ($options['database'] === 'postgres') {
            $dependsOn[] = 'postgres';
        }
        if ($options['cache'] === 'redis') {
            $dependsOn[] = 'redis';
        }

        if ($dependsOn !== []) {
            $services[0] .= "\n    depends_on:\n";
            foreach ($dependsOn as $service) {
                $services[0] .= sprintf("      - %s\n", $service);
            }
        }

        $services[] = <<<YAML
  nginx:
    image: nginx:alpine
    ports:
      - "{$options['port']}:80"
    volumes:
      - ../:/var/www/html
      - ./nginx/nginx.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
YAML;

        if ($options['database'] === 'mysql') {
            $services[] = <<<YAML
  mysql:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: {$options['db_database']}
      MYSQL_USER: {$options['db_username']}
      MYSQL_PASSWORD: {$options['db_password']}
      MYSQL_ROOT_PASSWORD: {$options['db_password']}
    volumes:
      - mysql-data:/var/lib/mysql
      - ./mysql/init.sql:/docker-entrypoint-initdb.d/init.sql:ro
YAML;
            $volumes[] = '  mysql-data:';
        }

        if ($options['database'] === 'postgres') {
            $services[] = <<<YAML
  postgres:
    image: postgres:16-alpine
    environment:
      POSTGRES_DB: {$options['db_database']}
      POSTGRES_USER: {$options['db_username']}
      POSTGRES_PASSWORD: {$options['db_password']}
    volumes:
      - postgres-data:/var/lib/postgresql/data
YAML;
            $volumes[] = '  postgres-data:';
        }

        if ($options['cache'] === 'redis') {
            $services[] = <<<YAML
  redis:
    image: redis:7-alpine
    volumes:
      - redis-data:/data
YAML;
            $volumes[] = '  redis-data:';
        }

        if ($options['queue'] === 'yes') {
            $queueDepends = array_merge(['app'], $dependsOn);
            $services[] = "  worker:\n    build:\n      context: ..\n      dockerfile: docker/Dockerfile\n    command: php bin/valhalla queue:work\n    working_dir: /var/www/html\n    volumes:\n      - ../:/var/www/html\n    depends_on:\n";

            $index = count($services) - 1;
            foreach ($queueDepends as $service) {
                $services[$index] .= sprintf("      - %s\n", $service);
            }
        }

        $yaml = $composeHeader.implode("\n", $services);

        if ($volumes !== []) {
            $yaml .= "\nvolumes:\n".implode("\n", $volumes)."\n";
        }

        return $yaml;
    }

    public static function nginxConfig(): string
    {
        return <<<'NGINX'
server {
    listen 80;
    server_name _;
    root /var/www/html/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_pass app:9000;
    }
}
NGINX;
    }

    public static function mysqlInit(string $database): string
    {
        return sprintf("CREATE DATABASE IF NOT EXISTS `%s`;\n", $database);
    }

    public static function dockerIgnore(): string
    {
        return <<<'TXT'
vendor/
.env
node_modules/
.git/
docker/
tests/
*.log
TXT;
    }

    public static function ormConfig(string $driver): string
    {
        return <<<PHP
<?php

declare(strict_types=1);

return [
    'driver' => '{$driver}',
];
PHP;
    }

    public static function databaseConfig(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

return [
    'default' => env('DB_CONNECTION', 'mysql'),
    'debug' => (bool) env('APP_DEBUG', true),
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 3306),
            'database' => env('DB_DATABASE', 'valhalla'),
            'username' => env('DB_USERNAME', 'valhalla'),
            'password' => env('DB_PASSWORD', 'secret'),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ],
        'postgres' => [
            'driver' => 'pdo_pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int) env('DB_PORT', 5432),
            'database' => env('DB_DATABASE', 'valhalla'),
            'username' => env('DB_USERNAME', 'valhalla'),
            'password' => env('DB_PASSWORD', 'secret'),
            'charset' => 'utf8',
        ],
    ],
];
PHP;
    }
}
