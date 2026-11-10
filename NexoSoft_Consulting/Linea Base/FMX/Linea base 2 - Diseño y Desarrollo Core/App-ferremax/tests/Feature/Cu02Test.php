<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Cu02Test extends TestCase
{
    use RefreshDatabase;

    public function test_cierre_de_sesion(): void
    {
        $this->actingAs($this->usuario())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
        $this->get('/')->assertRedirect('/login');
    }

    public function test_logout_no_acepta_get(): void
    {
        $this->get('/logout')->assertStatus(405);
    }
}
