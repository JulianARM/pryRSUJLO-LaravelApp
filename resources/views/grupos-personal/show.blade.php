@extends('adminlte::page')

@section('title', 'Detalles del Grupo')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="rsu-page-title"><i class="fas fa-users"></i> Detalles del Grupo</h1>
        <a href="{{ route('grupos-personal.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-arrow-left mr-1"></i> Volver
        </a>
    </div>
@stop

@section('content')
    @php
        $statusClasses = [
            \App\Models\Programacion::STATUS_SCHEDULED => 'badge-primary',
            \App\Models\Programacion::STATUS_FINALIZED => 'badge-success',
            \App\Models\Programacion::STATUS_REPROGRAMMED => 'badge-warning',
            \App\Models\Programacion::STATUS_CANCELLED => 'badge-secondary',
        ];
        $changes = $personnelGroup->schedules
            ->flatMap(fn ($schedule) => $schedule->changes->map(fn ($change) => ['schedule' => $schedule, 'change' => $change]))
            ->sortByDesc(fn ($item) => $item['change']->created_at)
            ->values();
    @endphp

    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary">
                    <h3 class="card-title mb-0">{{ $personnelGroup->name }}</h3>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Turno:</strong> {{ $personnelGroup->shift->name }}</p>
                    <p class="mb-2"><strong>Zona:</strong> {{ $personnelGroup->zone->name }}</p>
                    <p class="mb-2"><strong>Vehículo:</strong> {{ $personnelGroup->vehicle->name }} <code>{{ $personnelGroup->vehicle->placa }}</code></p>
                    <p class="mb-2"><strong>Días:</strong> {{ $personnelGroup->days_label }}</p>
                    <p class="mb-0">
                        <strong>Estado:</strong>
                        <span class="badge {{ $personnelGroup->activo ? 'badge-success' : 'badge-secondary' }}">
                            {{ $personnelGroup->activo ? 'Activo' : 'Inactivo' }}
                        </span>
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <strong><i class="fas fa-user-friends mr-1"></i> Equipo asignado</strong>
                </div>
                <div class="card-body">
                    <div class="rsu-person-detail">
                        <span>Conductor</span>
                        <strong>{{ $personnelGroup->driver->full_name }}</strong>
                        <small>DNI: {{ $personnelGroup->driver->dni }}</small>
                    </div>
                    @foreach ($personnelGroup->helpers as $helper)
                        <div class="rsu-person-detail">
                            <span>Ayudante {{ $loop->iteration }}</span>
                            <strong>{{ $helper->full_name }}</strong>
                            <small>DNI: {{ $helper->dni }}</small>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-light">
                    <strong><i class="fas fa-calendar-check mr-1"></i> Programaciones del grupo</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Turno</th>
                                    <th>Vehículo</th>
                                    <th>Conductor</th>
                                    <th>Ayudantes</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($personnelGroup->schedules as $schedule)
                                    <tr>
                                        <td><code>{{ $schedule->fecha_programada->format('d/m/Y') }}</code></td>
                                        <td>{{ $schedule->shift->name }}</td>
                                        <td>{{ $schedule->vehicle->name }} <code>{{ $schedule->vehicle->placa }}</code></td>
                                        <td>{{ $schedule->driver->full_name }}</td>
                                        <td>{{ $schedule->helpers->pluck('full_name')->implode(', ') }}</td>
                                        <td><span class="badge {{ $statusClasses[$schedule->status] ?? 'badge-secondary' }}">{{ $schedule->status_label }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">Este grupo aún no tiene programaciones generadas.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-light">
                    <strong><i class="fas fa-history mr-1"></i> Historial de cambios</strong>
                </div>
                <div class="card-body">
                    @forelse ($changes as $item)
                        <div class="rsu-history-item">
                            <div>
                                <strong>{{ $item['change']->created_at->format('d/m/Y H:i') }}</strong>
                                <span class="badge badge-light">{{ $item['change']->action }}</span>
                            </div>
                            <p class="mb-1">{{ $item['change']->descripcion }}</p>
                            <small class="text-muted">
                                Programación del {{ $item['schedule']->fecha_programada->format('d/m/Y') }}
                            </small>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No hay historial registrado para este grupo.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@stop
