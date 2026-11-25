<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_home_page_redirects_to_the_login_page(): void
    {
        $response = $this->get('/');

        $response->assertRedirect(route('filament.main.auth.login'));
    }

    public function test_the_login_page_loads(): void
    {
        $this->get(route('filament.main.auth.login'))->assertOk();
    }
}
