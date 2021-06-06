<?php

use Faker\Factory;
use Laravel\Lumen\Application;
use Laravel\Lumen\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public \Faker\Generator $faker;

    /**
     * Creates the application.
     *
     * @return Application
     */
    public function createApplication()
    {
        $this->faker = Factory::create();
        return require __DIR__ . '/../bootstrap/app.php';
    }
}
