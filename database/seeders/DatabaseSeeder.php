<?php

namespace Database\Seeders;

use App\Models\Asistencia;
use App\Models\ColorVehiculo;
use App\Models\Contrato;
use App\Models\Feriado;
use App\Models\GrupoPersonal;
use App\Models\Marca;
use App\Models\ModeloVehiculo;
use App\Models\Personal;
use App\Models\Programacion;
use App\Models\SaldoVacacion;
use App\Models\SolicitudVacacion;
use App\Models\TipoPersonal;
use App\Models\TipoVehiculo;
use App\Models\Turno;
use App\Models\User;
use App\Models\Vehiculo;
use App\Models\Zona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'juliaan.arm@gmail.com'],
            [
                'name' => 'Julian Armas',
                'password' => Hash::make('julian123'),
            ]
        );

        ColorVehiculo::upsert([
            ['name' => 'Gris', 'code' => '#808080', 'descripcion' => 'Color gris metálico'],
            ['name' => 'Blanco', 'code' => '#FFFFFF', 'descripcion' => 'Color blanco perlado'],
            ['name' => 'Azul Marino', 'code' => '#0E3C67', 'descripcion' => null],
        ], ['code'], ['name', 'descripcion']);

        $marcas = [
            ['name' => 'Mercedes Benz', 'descripcion' => 'Marca alemana de vehículos de alta calidad'],
            ['name' => 'Volvo', 'descripcion' => 'Marca sueca especializada en vehículos pesados'],
            ['name' => 'Hyundai', 'descripcion' => 'Marca coreana de vehículos comerciales'],
            ['name' => 'Toyota', 'descripcion' => 'Marca japonesa reconocida mundialmente'],
        ];

        foreach ($marcas as $brand) {
            Marca::updateOrCreate(['name' => $brand['name']], $brand);
        }

        $models = [
            ['brand' => 'Hyundai', 'name' => 'Hyundai Atego', 'code' => 'ATEGO-HYU', 'descripcion' => 'Modelo resistente para trabajo pesado'],
            ['brand' => 'Hyundai', 'name' => 'Hyundai Axor', 'code' => 'AXOR-HYU', 'descripcion' => 'Modelo avanzado con alta capacidad'],
            ['brand' => 'Toyota', 'name' => 'Toyota Atego', 'code' => 'ATEGO-TOY', 'descripcion' => 'Modelo resistente para trabajo pesado'],
            ['brand' => 'Toyota', 'name' => 'Toyota Axor', 'code' => 'AXOR-TOY', 'descripcion' => 'Modelo avanzado con alta capacidad'],
            ['brand' => 'Volvo', 'name' => 'Volvo Atego', 'code' => 'ATEGO-VOL', 'descripcion' => 'Modelo resistente para trabajo pesado'],
        ];

        foreach ($models as $model) {
            $brand = Marca::where('name', $model['brand'])->first();

            ModeloVehiculo::updateOrCreate(
                ['code' => $model['code']],
                [
                    'marca_id' => $brand->id,
                    'name' => $model['name'],
                    'descripcion' => $model['descripcion'],
                ]
            );
        }

        TipoVehiculo::upsert([
            ['name' => 'Camión Recolector', 'descripcion' => 'Vehículo para recolección de residuos'],
            ['name' => 'Compactador', 'descripcion' => 'Vehículo para compactar residuos'],
            ['name' => 'Volquete', 'descripcion' => 'Vehículo de carga volquete'],
        ], ['name'], ['descripcion']);

        TipoPersonal::upsert([
            ['name' => TipoPersonal::DRIVER, 'descripcion' => 'Personal autorizado para conducir vehículos', 'es_sistema' => true],
            ['name' => 'Ayudante', 'descripcion' => 'Personal de apoyo en la recolección', 'es_sistema' => true],
        ], ['name'], ['descripcion', 'es_sistema']);

        $staffTypes = TipoPersonal::pluck('id', 'name');

        $personal = [
            [
                'dni' => '12345678',
                'nombres' => 'Esteban',
                'apellidos' => 'Rojas Salazar',
                'fecha_nacimiento' => '1990-03-12',
                'telefono' => '987654321',
                'email' => 'esteban.rojas@rsu.test',
                'licencia' => 'Q12345678',
                'tipo_personal_id' => $staffTypes[TipoPersonal::DRIVER],
                'direccion' => 'Av. Principal 123, Jose Leonardo Ortiz',
            ],
            [
                'dni' => '80000019',
                'nombres' => 'Mariana',
                'apellidos' => 'Torres Vega',
                'fecha_nacimiento' => '1988-07-21',
                'telefono' => '987654322',
                'email' => 'mariana.torres@rsu.test',
                'licencia' => null,
                'tipo_personal_id' => $staffTypes['Ayudante'],
                'direccion' => 'Calle Los Jardines 456, Chiclayo',
            ],
            [
                'dni' => '70000007',
                'nombres' => 'Ricardo',
                'apellidos' => 'Salas Huaman',
                'fecha_nacimiento' => '1985-11-08',
                'telefono' => '987654323',
                'email' => 'ricardo.salas@rsu.test',
                'licencia' => 'Q70000007',
                'tipo_personal_id' => $staffTypes[TipoPersonal::DRIVER],
                'direccion' => 'Av. Saenz Pena 789, Chiclayo',
            ],
            [
                'dni' => '70000008',
                'nombres' => 'Lucia',
                'apellidos' => 'Benites Paredes',
                'fecha_nacimiento' => '1992-05-16',
                'telefono' => '987654324',
                'email' => 'lucia.benites@rsu.test',
                'licencia' => 'Q70000008',
                'tipo_personal_id' => $staffTypes[TipoPersonal::DRIVER],
                'direccion' => 'Jr. Comercio 320, Jose Leonardo Ortiz',
            ],
            [
                'dni' => '10000001',
                'nombres' => 'Diego',
                'apellidos' => 'Campos Núñez',
                'fecha_nacimiento' => '1998-09-03',
                'telefono' => '987654325',
                'email' => 'diego.campos@rsu.test',
                'licencia' => null,
                'tipo_personal_id' => $staffTypes['Ayudante'],
                'direccion' => 'Calle Real 120, Lambayeque',
            ],
            [
                'dni' => '10000002',
                'nombres' => 'Valeria',
                'apellidos' => 'Flores Cárdenas',
                'fecha_nacimiento' => '1996-01-25',
                'telefono' => '987654326',
                'email' => 'valeria.flores@rsu.test',
                'licencia' => null,
                'tipo_personal_id' => $staffTypes['Ayudante'],
                'direccion' => 'Urb. Primavera 500, Chiclayo',
            ],
            [
                'dni' => '42331346',
                'nombres' => 'Patricia',
                'apellidos' => 'Soto Aguilar',
                'fecha_nacimiento' => '1989-04-11',
                'telefono' => null,
                'email' => 'patricia.soto@rsu.test',
                'licencia' => 'Q42331346',
                'tipo_personal_id' => $staffTypes[TipoPersonal::DRIVER],
                'direccion' => 'Av. Los Incas 840, Jose Leonardo Ortiz',
            ],
        ];

        foreach ($personal as $employee) {
            Personal::updateOrCreate(
                ['dni' => $employee['dni']],
                $employee + [
                    'password' => Hash::make('personal123'),
                    'activo' => true,
                ]
            );
        }

        $vehicleData = [
            [
                'name' => 'Vehículo 01',
                'code' => 'VEH-001',
                'placa' => 'CLO-008',
                'anio' => 2020,
                'capacidad_carga' => 11860,
                'capacidad_combustible' => 60,
                'capacidad_compactacion' => 180,
                'capacidad_personas' => 3,
                'brand' => 'Toyota',
                'model' => 'Toyota Atego',
                'type' => 'Volquete',
                'color' => 'Gris',
            ],
            [
                'name' => 'Vehículo 02',
                'code' => 'VEH-002',
                'placa' => 'CLO-002',
                'anio' => 2020,
                'capacidad_carga' => 8727,
                'capacidad_combustible' => 55,
                'capacidad_compactacion' => 160,
                'capacidad_personas' => 3,
                'brand' => 'Toyota',
                'model' => 'Toyota Axor',
                'type' => 'Volquete',
                'color' => 'Blanco',
            ],
            [
                'name' => 'Vehículo 03',
                'code' => 'VEH-003',
                'placa' => 'CLO-004',
                'anio' => 2022,
                'capacidad_carga' => 8037,
                'capacidad_combustible' => 65,
                'capacidad_compactacion' => 175,
                'capacidad_personas' => 3,
                'brand' => 'Toyota',
                'model' => 'Toyota Atego',
                'type' => 'Camión Recolector',
                'color' => 'Gris',
            ],
            [
                'name' => 'Vehículo 04',
                'code' => 'VEH-004',
                'placa' => 'CLO-007',
                'anio' => 2019,
                'capacidad_carga' => 14213,
                'capacidad_combustible' => 75,
                'capacidad_compactacion' => 200,
                'capacidad_personas' => 3,
                'brand' => 'Hyundai',
                'model' => 'Hyundai Atego',
                'type' => 'Camión Recolector',
                'color' => 'Azul Marino',
            ],
        ];

        foreach ($vehicleData as $item) {
            $brand = Marca::where('name', $item['brand'])->firstOrFail();
            $model = ModeloVehiculo::where('name', $item['model'])->firstOrFail();
            $type = TipoVehiculo::where('name', $item['type'])->firstOrFail();
            $color = ColorVehiculo::where('name', $item['color'])->firstOrFail();

            Vehiculo::updateOrCreate(
                ['code' => $item['code']],
                [
                    'marca_id' => $brand->id,
                    'modelo_vehiculo_id' => $model->id,
                    'tipo_vehiculo_id' => $type->id,
                    'color_vehiculo_id' => $color->id,
                    'name' => $item['name'],
                    'placa' => $item['placa'],
                    'anio' => $item['anio'],
                    'capacidad_carga' => $item['capacidad_carga'],
                    'capacidad_combustible' => $item['capacidad_combustible'],
                    'capacidad_compactacion' => $item['capacidad_compactacion'],
                    'capacidad_personas' => $item['capacidad_personas'],
                    'descripcion' => 'Vehículo operativo para el servicio de recolección.',
                    'activo' => true,
                ]
            );
        }

        $contratos = [
            ['dni' => '12345678', 'tipo_contrato' => Contrato::TYPE_PERMANENT, 'fecha_inicio' => '2026-02-02', 'fecha_fin' => null, 'salario' => 1000, 'meses_periodo_prueba' => 3],
            ['dni' => '80000019', 'tipo_contrato' => Contrato::TYPE_PERMANENT, 'fecha_inicio' => '2024-04-18', 'fecha_fin' => null, 'salario' => 1963, 'meses_periodo_prueba' => 3],
            ['dni' => '70000008', 'tipo_contrato' => Contrato::TYPE_PERMANENT, 'fecha_inicio' => '2022-03-18', 'fecha_fin' => null, 'salario' => 3351, 'meses_periodo_prueba' => 3],
            ['dni' => '70000007', 'tipo_contrato' => Contrato::TYPE_TEMPORARY, 'fecha_inicio' => '2021-12-18', 'fecha_fin' => '2026-02-28', 'salario' => 3370, 'meses_periodo_prueba' => null],
            ['dni' => '42331346', 'tipo_contrato' => Contrato::TYPE_NAMED, 'fecha_inicio' => '2024-01-01', 'fecha_fin' => null, 'salario' => 2800, 'meses_periodo_prueba' => 0],
            ['dni' => '10000001', 'tipo_contrato' => Contrato::TYPE_PERMANENT, 'fecha_inicio' => '2026-05-15', 'fecha_fin' => null, 'salario' => 1850, 'meses_periodo_prueba' => 3],
            ['dni' => '10000002', 'tipo_contrato' => Contrato::TYPE_TEMPORARY, 'fecha_inicio' => '2026-06-01', 'fecha_fin' => '2026-08-31', 'salario' => 1650, 'meses_periodo_prueba' => null],
        ];

        foreach ($contratos as $contract) {
            $employee = Personal::with('staffType')->where('dni', $contract['dni'])->firstOrFail();

            Contrato::updateOrCreate(
                [
                    'personal_id' => $employee->id,
                    'fecha_inicio' => $contract['fecha_inicio'],
                ],
                [
                    'tipo_contrato' => $contract['tipo_contrato'],
                    'fecha_fin' => $contract['fecha_fin'],
                    'salario' => $contract['salario'],
                    'meses_periodo_prueba' => $contract['meses_periodo_prueba'],
                    'cargo' => $employee->staffType->name,
                    'activo' => true,
                ]
            );
        }

        foreach (Personal::whereHas('contratos', fn ($query) => $query
            ->where('activo', true)
            ->whereIn('tipo_contrato', [Contrato::TYPE_PERMANENT, Contrato::TYPE_NAMED]))->get() as $employee) {
            SaldoVacacion::updateOrCreate(
                [
                    'personal_id' => $employee->id,
                    'anio' => 2026,
                ],
                [
                    'dias_totales' => SaldoVacacion::DEFAULT_ANNUAL_DAYS,
                    'dias_usados' => 0,
                    'dias_disponibles' => SaldoVacacion::DEFAULT_ANNUAL_DAYS,
                ]
            );
        }

        Turno::upsert([
            ['name' => 'Madrugada', 'descripcion' => 'Turno madrugada', 'start_time' => '22:00:00', 'end_time' => '06:00:00'],
            ['name' => 'Mañana', 'descripcion' => 'Turno matutino', 'start_time' => '06:00:00', 'end_time' => '14:00:00'],
            ['name' => 'Tarde', 'descripcion' => 'Turno vespertino', 'start_time' => '14:00:00', 'end_time' => '18:00:00'],
            ['name' => 'Noche', 'descripcion' => 'Turno nocturno', 'start_time' => '18:00:00', 'end_time' => '22:00:00'],
        ], ['name'], ['descripcion', 'start_time', 'end_time']);

        $attendancePersonal = Personal::where('dni', '42331346')->firstOrFail();
        $manana = Turno::where('name', 'Mañana')->firstOrFail();

        Asistencia::updateOrCreate(
            [
                'personal_id' => $attendancePersonal->id,
                'fecha_asistencia' => '2026-04-11',
                'type' => Asistencia::TYPE_IN,
            ],
            [
                'turno_id' => $manana->id,
                'hora_asistencia' => '11:44:00',
                'registrado_en' => '2026-04-11 11:44:00',
                'status' => Asistencia::STATUS_PRESENT,
                'notes' => null,
            ]
        );

        $vacations = [
            ['dni' => '80000019', 'fecha_solicitud' => '2025-12-19', 'fecha_inicio' => '2026-01-01', 'dias_solicitados' => 5, 'status' => SolicitudVacacion::STATUS_CANCELLED],
            ['dni' => '42331346', 'fecha_solicitud' => '2026-04-13', 'fecha_inicio' => '2026-04-13', 'dias_solicitados' => 15, 'status' => SolicitudVacacion::STATUS_PENDING],
            ['dni' => '42331346', 'fecha_solicitud' => '2026-04-13', 'fecha_inicio' => '2026-04-13', 'dias_solicitados' => 5, 'status' => SolicitudVacacion::STATUS_REJECTED],
            ['dni' => '12345678', 'fecha_solicitud' => '2026-04-13', 'fecha_inicio' => '2026-04-15', 'dias_solicitados' => 15, 'status' => SolicitudVacacion::STATUS_CANCELLED],
            ['dni' => '12345678', 'fecha_solicitud' => '2026-02-02', 'fecha_inicio' => '2026-02-14', 'dias_solicitados' => 15, 'status' => SolicitudVacacion::STATUS_CANCELLED],
            ['dni' => '12345678', 'fecha_solicitud' => '2026-05-28', 'fecha_inicio' => '2026-06-03', 'dias_solicitados' => 4, 'status' => SolicitudVacacion::STATUS_APPROVED],
            ['dni' => '70000008', 'fecha_solicitud' => '2026-06-01', 'fecha_inicio' => '2026-06-10', 'dias_solicitados' => 5, 'status' => SolicitudVacacion::STATUS_PENDING],
            ['dni' => '80000019', 'fecha_solicitud' => '2026-06-03', 'fecha_inicio' => '2026-06-24', 'dias_solicitados' => 3, 'status' => SolicitudVacacion::STATUS_APPROVED],
            ['dni' => '10000001', 'fecha_solicitud' => '2026-06-04', 'fecha_inicio' => '2026-07-06', 'dias_solicitados' => 6, 'status' => SolicitudVacacion::STATUS_PENDING],
        ];

        foreach ($vacations as $vacation) {
            $employee = Personal::where('dni', $vacation['dni'])->firstOrFail();
            $startDate = Carbon::parse($vacation['fecha_inicio']);
            $vacationValues = [
                'fecha_fin' => $startDate->copy()->addDays($vacation['dias_solicitados'] - 1),
                'status' => $vacation['status'],
                'notes' => null,
            ];

            if ($vacation['status'] !== SolicitudVacacion::STATUS_APPROVED) {
                $vacationValues['saldo_vacacion_id'] = null;
                $vacationValues['dias_restantes'] = 0;
            }

            $vacationRequest = SolicitudVacacion::updateOrCreate(
                [
                    'personal_id' => $employee->id,
                    'fecha_solicitud' => $vacation['fecha_solicitud'],
                    'fecha_inicio' => $vacation['fecha_inicio'],
                    'dias_solicitados' => $vacation['dias_solicitados'],
                ],
                $vacationValues
            );

            if ($vacation['status'] === SolicitudVacacion::STATUS_APPROVED) {
                $balance = SaldoVacacion::firstOrCreate(
                    [
                        'personal_id' => $employee->id,
                        'anio' => $startDate->year,
                    ],
                    [
                        'dias_totales' => SaldoVacacion::DEFAULT_ANNUAL_DAYS,
                        'dias_usados' => 0,
                        'dias_disponibles' => SaldoVacacion::DEFAULT_ANNUAL_DAYS,
                    ]
                );

                if ((int) $vacationRequest->saldo_vacacion_id === (int) $balance->id) {
                    continue;
                }

                if ($balance->canUse($vacation['dias_solicitados'])) {
                    $balance->discount($vacation['dias_solicitados']);

                    $vacationRequest->update([
                        'saldo_vacacion_id' => $balance->id,
                        'dias_restantes' => $balance->dias_disponibles,
                    ]);
                }
            }
        }

        $zonas = [
            [
                'name' => 'Zona Centro',
                'departamento' => 'Lambayeque',
                'provincia' => 'Chiclayo',
                'distrito' => 'Jose Leonardo Ortiz',
                'descripcion' => 'Sector urbano central para planificación de recolección.',
                'residuos_promedio_kg' => 850,
                'activo' => true,
                'coordinates' => [
                    ['lat' => -6.763955, 'lng' => -79.842700],
                    ['lat' => -6.764182, 'lng' => -79.839896],
                    ['lat' => -6.767100, 'lng' => -79.840142],
                    ['lat' => -6.766872, 'lng' => -79.843050],
                ],
            ],
            [
                'name' => 'Zona Norte',
                'departamento' => 'Lambayeque',
                'provincia' => 'Chiclayo',
                'distrito' => 'Jose Leonardo Ortiz',
                'descripcion' => 'Sector norte con rutas de apoyo para unidades recolectoras.',
                'residuos_promedio_kg' => 640,
                'activo' => true,
                'coordinates' => [
                    ['lat' => -6.758910, 'lng' => -79.846950],
                    ['lat' => -6.759360, 'lng' => -79.843900],
                    ['lat' => -6.762240, 'lng' => -79.844150],
                    ['lat' => -6.761710, 'lng' => -79.847330],
                ],
            ],
        ];

        foreach ($zonas as $zoneData) {
            $coordinates = $zoneData['coordinates'];
            unset($zoneData['coordinates']);

            $zone = Zona::updateOrCreate(
                ['name' => $zoneData['name']],
                $zoneData
            );

            $zone->coordinates()->delete();

            foreach ($coordinates as $index => $coordinate) {
                $zone->coordinates()->create([
                    'latitud' => $coordinate['lat'],
                    'longitud' => $coordinate['lng'],
                    'orden' => $index + 1,
                ]);
            }
        }

        Feriado::upsert([
            ['date' => '2026-06-24', 'descripcion' => 'Festividad local referencial', 'activo' => true],
            ['date' => '2026-06-29', 'descripcion' => 'San Pedro y San Pablo', 'activo' => true],
            ['date' => '2026-07-28', 'descripcion' => 'Fiestas Patrias', 'activo' => true],
            ['date' => '2026-08-30', 'descripcion' => 'Santa Rosa de Lima', 'activo' => false],
        ], ['date'], ['descripcion', 'activo']);

        $grupoCentro = GrupoPersonal::updateOrCreate(
            ['name' => 'Grupo Centro Mañana'],
            [
                'turno_id' => Turno::where('name', 'Mañana')->firstOrFail()->id,
                'zona_id' => Zona::where('name', 'Zona Centro')->firstOrFail()->id,
                'vehiculo_id' => Vehiculo::where('code', 'VEH-001')->firstOrFail()->id,
                'conductor_id' => Personal::where('dni', '12345678')->firstOrFail()->id,
                'dias_semana' => [1, 2, 3, 4, 5],
                'activo' => true,
            ]
        );
        $grupoCentro->helpers()->sync([
            Personal::where('dni', '80000019')->firstOrFail()->id,
            Personal::where('dni', '10000001')->firstOrFail()->id,
        ]);

        $grupoNorte = GrupoPersonal::updateOrCreate(
            ['name' => 'Grupo Norte Tarde'],
            [
                'turno_id' => Turno::where('name', 'Tarde')->firstOrFail()->id,
                'zona_id' => Zona::where('name', 'Zona Norte')->firstOrFail()->id,
                'vehiculo_id' => Vehiculo::where('code', 'VEH-004')->firstOrFail()->id,
                'conductor_id' => Personal::where('dni', '70000008')->firstOrFail()->id,
                'dias_semana' => [1, 3, 5],
                'activo' => true,
            ]
        );
        $grupoNorte->helpers()->sync([
            Personal::where('dni', '10000002')->firstOrFail()->id,
        ]);

        $scheduleSamples = [
            ['group' => $grupoCentro, 'date' => '2026-06-15'],
            ['group' => $grupoCentro, 'date' => '2026-06-17'],
            ['group' => $grupoNorte, 'date' => '2026-06-15'],
            ['group' => $grupoNorte, 'date' => '2026-06-19'],
        ];

        foreach ($scheduleSamples as $sample) {
            /** @var GrupoPersonal $group */
            $group = $sample['group'];

            $schedule = Programacion::updateOrCreate(
                [
                    'grupo_personal_id' => $group->id,
                    'fecha_programada' => $sample['date'],
                ],
                [
                    'turno_id' => $group->turno_id,
                    'zona_id' => $group->zona_id,
                    'vehiculo_id' => $group->vehiculo_id,
                    'conductor_id' => $group->conductor_id,
                    'status' => Programacion::STATUS_SCHEDULED,
                    'notes' => 'Dato de prueba para revisión del Avance 05.',
                ]
            );
            $schedule->helpers()->sync($group->helpers()->pluck('personal.id')->all());
            $schedule->changes()->firstOrCreate(
                ['action' => 'created'],
                [
                    'descripcion' => 'Programación generada desde seeder para revisión.',
                    'valores_nuevos' => $schedule->load('helpers')->toArray(),
                ]
            );
        }

        $this->call(DatosPresentacionSeeder::class);
    }

}

