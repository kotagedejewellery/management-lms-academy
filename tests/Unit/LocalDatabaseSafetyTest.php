<?php

namespace Tests\Unit;

use Tests\TestCase;

class LocalDatabaseSafetyTest extends TestCase
{
    public function test_tests_use_an_isolated_in_memory_database(): void
    {
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('array', env('CACHE_STORE'));
        $this->assertSame('array', config('cache.default'));
    }

    public function test_postgres_volume_is_external_to_the_compose_lifecycle(): void
    {
        $compose = file_get_contents(base_path('docker-compose.yml'));

        $this->assertIsString($compose);
        $this->assertMatchesRegularExpression(
            '/postgres_data:\\s*\\n\\s*#.*\\n\\s*external: true\\s*\\n\\s*name: kgj-lms-academy_postgres_data/m',
            $compose,
        );
    }
}
