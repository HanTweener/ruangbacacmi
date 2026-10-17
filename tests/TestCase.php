<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.{$connection}.database");
        if (! (($connection === 'mysql' && $database === 'cmi_test') || ($connection === 'sqlite' && $database === ':memory:'))) {
            throw new \RuntimeException('Refusing database tests outside isolated cmi_test or SQLite :memory:.');
        }

        return $app;
    }
}
