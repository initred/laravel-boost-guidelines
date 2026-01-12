<?php

declare(strict_types=1);

namespace Initred\BoostGuidelines\Tests;

use Initred\BoostGuidelines\BoostGuidelinesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BoostGuidelinesServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('boost-guidelines.output_path', sys_get_temp_dir().'/boost-guidelines-test');
    }
}
