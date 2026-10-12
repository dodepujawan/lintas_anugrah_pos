<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Disable mocked console output during tests so migrate:fresh can run normally.
     *
     * @var bool
     */
    public $mockConsoleOutput = false;

    /**
     * Configure the parameters used when migrating the database during tests.
     *
     * @return array<string, mixed>
     */
    protected function migrateFreshUsing()
    {
        return array_merge(
            [
                '--drop-views' => $this->shouldDropViews(),
                '--drop-types' => $this->shouldDropTypes(),
                '--force' => true,
            ],
            $this->seeder() ? ['--seeder' => $this->seeder()] : ['--seed' => $this->shouldSeed()]
        );
    }
}
