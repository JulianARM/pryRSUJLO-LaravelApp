<?php

namespace Tests\Feature;

use RuntimeException;
use Tests\TestCase;

class FriendlyErrorHandlingTest extends TestCase
{
    public function test_unexpected_web_errors_show_friendly_page(): void
    {
        \Route::middleware('web')->get('/__test-friendly-web-error', function () {
            throw new RuntimeException('Technical database stack trace detail');
        });

        $this->get('/__test-friendly-web-error')
            ->assertStatus(500)
            ->assertSee('No se pudo completar la solicitud')
            ->assertSee('Intenta nuevamente')
            ->assertDontSee('RuntimeException')
            ->assertDontSee('Technical database stack trace detail');
    }

    public function test_unexpected_json_errors_show_friendly_message(): void
    {
        \Route::middleware('web')->get('/__test-friendly-json-error', function () {
            throw new RuntimeException('Internal class name should not leak');
        });

        $this->getJson('/__test-friendly-json-error')
            ->assertStatus(500)
            ->assertJson([
                'message' => 'No se pudo completar la solicitud. Intenta nuevamente y, si el problema continúa, comunícalo al administrador del sistema.',
            ])
            ->assertDontSee('RuntimeException')
            ->assertDontSee('Internal class name should not leak');
    }
}