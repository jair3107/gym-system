<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Las vistas usan @vite; en CI no existe public/build (no se compilan
        // los assets), así que los tests no deben depender del manifest.
        $this->withoutVite();
    }
}
