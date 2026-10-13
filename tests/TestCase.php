<?php

namespace Sufian\SlugHelper\Tests;

use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Sufian\SlugHelper\SlugHelperServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [SlugHelperServiceProvider::class];
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->app['db']->connection()->getSchemaBuilder()->create('posts', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
        });
    }
}
