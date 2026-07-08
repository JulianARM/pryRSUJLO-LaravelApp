<?php

use App\Http\Controllers\AsistenciaController;
use App\Http\Controllers\CambioProgramacionController;
use App\Http\Controllers\ColorVehiculoController;
use App\Http\Controllers\ContratoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeriadoController;
use App\Http\Controllers\GrupoPersonalController;
use App\Http\Controllers\ImagenVehiculoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloVehiculoController;
use App\Http\Controllers\MotivoCambioController;
use App\Http\Controllers\PersonalController;
use App\Http\Controllers\ProgramacionController;
use App\Http\Controllers\SolicitudVacacionController;
use App\Http\Controllers\TipoPersonalController;
use App\Http\Controllers\TipoVehiculoController;
use App\Http\Controllers\TurnoController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\ZonaController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/', '/login');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::put('dashboard/programaciones/{route_schedule}/personal', [DashboardController::class, 'updatePersonnel'])
        ->name('dashboard.schedules.personnel.update');

    Route::resource('colores', ColorVehiculoController::class)
        ->parameters(['colores' => 'vehicle_color'])
        ->names('colores-vehiculo')
        ->except(['show', 'create', 'edit']);

    Route::resource('marcas', MarcaController::class)
        ->parameters(['marcas' => 'brand'])
        ->names('marcas')
        ->except(['show', 'create', 'edit']);

    Route::resource('modelos', ModeloVehiculoController::class)
        ->parameters(['modelos' => 'brand_model'])
        ->names('modelos-vehiculo')
        ->except(['show', 'create', 'edit']);

    Route::resource('tipos-vehiculos', TipoVehiculoController::class)
        ->parameters(['tipos-vehiculos' => 'vehicle_type'])
        ->names('tipos-vehiculo')
        ->except(['show', 'create', 'edit']);

    Route::resource('vehiculos', VehiculoController::class)
        ->parameters(['vehiculos' => 'vehicle'])
        ->names('vehiculos')
        ->except(['show', 'create', 'edit']);

    Route::post('vehiculos/{vehicle}/imagenes', [ImagenVehiculoController::class, 'store'])
        ->name('vehiculos.images.store');
    Route::put('vehiculos/{vehicle}/imagenes/{image}/principal', [ImagenVehiculoController::class, 'profile'])
        ->name('vehiculos.images.profile');
    Route::delete('vehiculos/{vehicle}/imagenes/{image}', [ImagenVehiculoController::class, 'destroy'])
        ->name('vehiculos.images.destroy');

    Route::resource('tipos-personal', TipoPersonalController::class)
        ->parameters(['tipos-personal' => 'staff_type'])
        ->names('tipos-personal')
        ->except(['show', 'create', 'edit']);

    Route::get('contratos/personal/buscar', [ContratoController::class, 'searchPersonals'])
        ->name('contratos.personal.search');
    Route::get('personal/buscar', [PersonalController::class, 'search'])
        ->name('personal.search');

    Route::resource('personal', PersonalController::class)
        ->parameters(['personal' => 'employee'])
        ->names('personal')
        ->except(['show', 'create', 'edit']);

    Route::resource('contratos', ContratoController::class)
        ->parameters(['contratos' => 'contract'])
        ->names('contratos')
        ->except(['show', 'create', 'edit']);

    Route::get('asistencias/tipo-sugerido', [AsistenciaController::class, 'suggestedType'])
        ->name('asistencias.suggested-type');
    Route::resource('asistencias', AsistenciaController::class)
        ->parameters(['asistencias' => 'attendance'])
        ->names('asistencias')
        ->except(['show', 'create', 'edit']);

    Route::put('vacaciones/{vacation}/aprobar', [SolicitudVacacionController::class, 'approve'])
        ->name('vacaciones.approve');
    Route::put('vacaciones/{vacation}/rechazar', [SolicitudVacacionController::class, 'reject'])
        ->name('vacaciones.reject');
    Route::resource('vacaciones', SolicitudVacacionController::class)
        ->parameters(['vacaciones' => 'vacation'])
        ->names('vacaciones')
        ->except(['show', 'create', 'edit']);

    Route::resource('turnos', TurnoController::class)
        ->parameters(['turnos' => 'shift'])
        ->names('turnos')
        ->except(['show', 'create', 'edit']);

    Route::resource('feriados', FeriadoController::class)
        ->parameters(['feriados' => 'holiday'])
        ->names('feriados')
        ->except(['show', 'create', 'edit']);
    Route::post('feriados/cargar-peru', [FeriadoController::class, 'loadPeru'])
        ->name('feriados.load-peru');

    Route::match(['post', 'put', 'patch'], 'grupos-personal/validar-disponibilidad', [GrupoPersonalController::class, 'validateAvailability'])
        ->name('grupos-personal.validate');
    Route::resource('grupos-personal', GrupoPersonalController::class)
        ->parameters(['grupos-personal' => 'personnel_group'])
        ->names('grupos-personal')
        ->except(['create', 'edit']);

    Route::post('programaciones/validar-disponibilidad', [ProgramacionController::class, 'validateAvailability'])
        ->name('programaciones.validate');
    Route::get('programaciones/masiva', [ProgramacionController::class, 'mass'])
        ->name('programaciones.mass');
    Route::post('programaciones/masiva/validar', [ProgramacionController::class, 'validateMass'])
        ->name('programaciones.mass.validate');
    Route::post('programaciones/masiva/generar', [ProgramacionController::class, 'storeMass'])
        ->name('programaciones.mass.store');
    Route::put('programaciones/{route_schedule}/finalizar', [ProgramacionController::class, 'finalize'])
        ->name('programaciones.finalize');
    Route::resource('programaciones', ProgramacionController::class)
        ->parameters(['programaciones' => 'route_schedule'])
        ->names('programaciones')
        ->except(['show', 'create', 'edit']);

    Route::post('zonas/{zone}/coordenadas', [ZonaController::class, 'storeCoordinates'])
        ->name('zonas.coordinates.store');
    Route::resource('motivos-cambio', MotivoCambioController::class)
        ->parameters(['motivos-cambio' => 'change_reason'])
        ->names('motivos-cambio')
        ->except(['show', 'create', 'edit']);

    Route::post('cambios/masivo', [CambioProgramacionController::class, 'storeMass'])
        ->name('cambios-programacion.mass.store');
    Route::resource('cambios', CambioProgramacionController::class)
        ->parameters(['cambios' => 'schedule_change'])
        ->names('cambios-programacion')
        ->only(['index', 'show', 'destroy']);

    Route::resource('zonas', ZonaController::class)
        ->parameters(['zonas' => 'zone'])
        ->names('zonas')
        ->except(['create', 'edit']);
});
