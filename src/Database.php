<?php

namespace Nano\Framework;

use Illuminate\Database\Capsule\Manager as Capsule;

class Database
{
    public function __construct()
    {
        $capsule = new Capsule;

        $driver = $_ENV['DB_DRIVER'] ?? 'mysql';
        $database = $_ENV['DB_DATABASE'] ?? 'nanophp';

        $config = [
            'driver'    => $driver,
            'prefix'    => '',
        ];

        if ($driver === 'sqlite') {
            $config['database'] = str_contains($database, ':memory:')
                ? $database
                : __DIR__ . '/../../../database/' . $database . '.sqlite';

            // Auto-create SQLite file if it doesn't exist
            if ($driver === 'sqlite' && !str_contains($database, ':memory:') && !file_exists($config['database'])) {
                if (!is_dir(dirname($config['database']))) {
                    mkdir(dirname($config['database']), 0755, true);
                }
                touch($config['database']);
            }
        } else {
            $config['host'] = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $config['port'] = $_ENV['DB_PORT'] ?? ($driver === 'pgsql' ? '5432' : '3306');
            $config['database'] = $database;
            $config['username'] = $_ENV['DB_USERNAME'] ?? 'root';
            $config['password'] = $_ENV['DB_PASSWORD'] ?? '';
            $config['charset'] = 'utf8';
            if ($driver === 'mysql') {
                $config['collation'] = 'utf8_unicode_ci';
            }
        }

        $capsule->addConnection($config);

        $capsule->setAsGlobal();
        $capsule->bootEloquent();
    }

    /**
     * Get a query builder for the given table.
     *
     * @param string $table
     * @return \Illuminate\Database\Query\Builder
     */
    public function table(string $table)
    {
        return Capsule::table($table);
    }

    /**
     * Execute a raw SQL statement.
     *
     * @param string $query
     * @param array $bindings
     * @return bool
     */
    public function exec(string $query, array $bindings = []): bool
    {
        return Capsule::statement($query, $bindings);
    }

    /**
     * Execute a callback within a database transaction.
     *
     * @param callable $callback
     * @return mixed
     */
    public function transaction(callable $callback)
    {
        return Capsule::transaction($callback);
    }

    /**
     * Get the PDO connection.
     *
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return Capsule::connection()->getPdo();
    }
}
