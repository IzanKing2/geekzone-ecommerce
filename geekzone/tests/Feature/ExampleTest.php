<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_api_health_check(): void
    {
        $response = $this->getJson('/api/categorias');

        $response->assertStatus(200);
    }
}
