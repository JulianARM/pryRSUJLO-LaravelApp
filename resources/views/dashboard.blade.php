@extends('adminlte::page')

@section('title', 'Dashboard')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div>
            <h1 class="rsu-page-title mb-1"><i class="fas fa-chart-line"></i> Dashboard General</h1>
            <small class="text-muted">Monitoreo y gestión de programaciones del día.</small>
        </div>
        <a href="{{ route('programaciones.index') }}" class="btn btn-primary">
            <i class="fas fa-list mr-1"></i> Ir al Módulo de Programación
        </a>
    </div>
@stop

@section('content')
    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="row">
            <div class="col-md-3"><div class="rsu-dashboard-tile is-info"><span>Total de programaciones</span><strong>{{ $metrics['total'] }}</strong><i class="fas fa-calendar-check"></i></div></div>
            <div class="col-md-3"><div class="rsu-dashboard-tile is-success"><span>Completadas</span><strong>{{ $metrics['completed'] }}</strong><i class="fas fa-check-circle"></i></div></div>
            <div class="col-md-3"><div class="rsu-dashboard-tile is-warning"><span>Incompletas</span><strong>{{ $metrics['incomplete'] }}</strong><i class="fas fa-clock"></i></div></div>
            <div class="col-md-3"><div class="rsu-dashboard-tile is-danger"><span>Personal faltante</span><strong>{{ $metrics['missing_staff'] }}</strong><i class="fas fa-user-times"></i></div></div>
        </div>

        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'dashboard', 'method' => 'GET']) !!}
                    <div class="row align-items-end">
                        <div class="col-md-4 form-group">
                            {!! Form::label('date', 'Fecha') !!}
                            {!! Form::date('date', $date, ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-4 form-group">
                            {!! Form::label('turno_id', 'Turno') !!}
                            {!! Form::select('turno_id', ['' => 'Todos los turnos'] + $turnos->toArray(), $selectedTurnoId, ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-4 form-group">
                            <button class="btn btn-primary"><i class="fas fa-search mr-1"></i> Buscar</button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary"><i class="fas fa-eraser mr-1"></i> Limpiar</a>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-primary"><h3 class="card-title mb-0"><i class="fas fa-user-check mr-1"></i> Zonas con personal faltante</h3></div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Turno</th>
                            <th>Vehículo</th>
                            <th>Personal observado</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if ($missingSchedules->isEmpty())
                        <tr><td colspan="6" class="text-center text-muted">No hay zonas con personal faltante para la fecha seleccionada.</td></tr>
                    @else
                        @foreach ($missingSchedules as $item)
                            <tr>
                                <td class="font-weight-bold">{{ $item['schedule']->zone->name }}</td>
                                <td>{{ $item['schedule']->shift->name }}</td>
                                <td>{{ $item['schedule']->vehicle->name }} <code>{{ $item['schedule']->vehicle->placa }}</code></td>
                                <td>
                                    @foreach ($item['issues'] as $issue)
                                        <div><span class="badge badge-danger">{{ $issue['role'] }}</span> {{ $issue['name'] }} <small class="text-muted">({{ implode(', ', $issue['issues']) }})</small></div>
                                    @endforeach
                                </td>
                                <td><span class="badge badge-warning">{{ $item['schedule']->status_label }}</span></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#dashboardScheduleDetailModal{{ $item['schedule']->id }}"><i class="fas fa-eye mr-1"></i> Ver Detalles</button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-light"><strong><i class="fas fa-list mr-1"></i> Programaciones del día</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Zona</th>
                            <th>Turno</th>
                            <th>Grupo</th>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Ayudantes</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    @if ($schedules->isEmpty())
                        <tr><td colspan="8" class="text-center text-muted">No hay programaciones para esta fecha.</td></tr>
                    @else
                        @foreach ($schedules as $schedule)
                            <tr>
                                <td class="font-weight-bold">{{ $schedule->zone->name }}</td>
                                <td>{{ $schedule->shift->name }}</td>
                                <td>{{ $schedule->personnelGroup?->name ?? '-' }}</td>
                                <td>{{ $schedule->vehicle->name }} <code>{{ $schedule->vehicle->placa }}</code></td>
                                <td>{{ $schedule->driver->full_name }}</td>
                                <td>{{ $schedule->helpers->pluck('full_name')->implode(', ') }}</td>
                                <td><span class="badge badge-{{ $schedule->status === \App\Models\Programacion::STATUS_FINALIZED ? 'success' : 'primary' }}">{{ $schedule->status_label }}</span></td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#dashboardScheduleDetailModal{{ $schedule->id }}">
                                        <i class="fas fa-eye mr-1"></i> Ver Detalles
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>

        @foreach ($schedules as $schedule)
            @include('dashboard.partials.schedule-detail-modal', ['schedule' => $schedule])
        @endforeach
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop