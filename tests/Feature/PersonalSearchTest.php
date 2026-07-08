<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\User;
use Tests\TestCase;

class PersonalSearchTest extends TestCase
{
    public function test_employee_search_returns_select2_results(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('personal.search'))
            ->assertOk()
            ->assertJsonStructure([
                'results' => [
                    '*' => ['id', 'text', 'dias_disponibles', 'maximum_days'],
                ],
            ]);
    }

    public function test_employee_search_can_filter_by_name(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $employee = Personal::where('activo', true)->firstOrFail();

        $this->actingAs($user)
            ->getJson(route('personal.search', ['q' => $employee->nombres]))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $employee->id,
            ]);
    }
}
