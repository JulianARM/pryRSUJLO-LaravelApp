<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Zona;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ZonaModuleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_zone_index_is_available(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();

        $this->actingAs($user)
            ->get(route('zonas.index'))
            ->assertOk()
            ->assertSee('Listado de Zonas');
    }

    public function test_coordenadas_zona_can_be_stored(): void
    {
        $user = User::where('email', 'juliaan.arm@gmail.com')->firstOrFail();
        $zone = Zona::firstOrFail();

        $coordinates = [
            ['lat' => -6.760001, 'lng' => -79.840001],
            ['lat' => -6.760501, 'lng' => -79.839001],
            ['lat' => -6.761001, 'lng' => -79.840501],
        ];

        $this->actingAs($user)
            ->postJson(route('zonas.coordinates.store', $zone), [
                'coordinates' => json_encode($coordinates),
            ])
            ->assertOk()
            ->assertJsonFragment([
                'message' => 'Perímetro de la zona actualizado correctamente.',
            ]);

        $this->assertSame(3, $zone->coordinates()->count());
    }
}
