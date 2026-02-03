<?php

namespace Nano\Framework\Testing;

use PHPUnit\Framework\TestCase as BaseTestCase;
use Nano\Framework\Application;
use Nano\Framework\Database;

abstract class TestCase extends BaseTestCase
{
    /**
     * The application instance.
     *
     * @var \Nano\Framework\Application
     */
    protected $app;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->app = $this->createApplication();
        $this->setUpDatabase();
    }

    /**
     * Create the application.
     *
     * @return \Nano\Framework\Application
     */
    protected function createApplication()
    {
        $app = require __DIR__ . '/../../../../../bootstrap/app.php';
        return $app;
    }

    /**
     * Setup the database for testing.
     */
    protected function setUpDatabase()
    {
        // Check if RefreshDatabase trait is used (simplified check)
        // Ideally we check if using in memory SQLite and run migrations

        if (config('database.default') === 'sqlite' && config('database.connections.sqlite.database') === ':memory:') {
            // Run migrations
            // For now, we manually assume we might need to migrate
            // But usually this is done via RefreshDatabase trait
        }
    }
}
