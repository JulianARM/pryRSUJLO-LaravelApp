<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class VacationIndexTest extends TestCase
{
    public function test_vacation_index_shows_dias_restantes_column(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->get(route('vacaciones.index'))
            ->assertOk()
            ->assertSee('Días R.');
    }
}
