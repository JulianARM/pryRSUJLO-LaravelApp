@extends('adminlte::page')

@section('title', 'Programaciones')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="rsu-page-title"><i class="fas fa-calendar-check"></i> Lista de Programaciones</h1>
        <div class="btn-group">
            <a href="{{ route('programaciones.mass') }}" class="btn btn-success">
                <i class="fas fa-calendar-plus mr-1"></i> Programación Masiva
            </a>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createProgramacionModal">
                <i class="fas fa-plus mr-1"></i> Nueva Programación
            </button>
        </div>
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
        $dayLabels = \App\Models\GrupoPersonal::DAYS;
    @endphp

    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3">
                    {!! Form::open(['route' => 'programaciones.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'programaciones.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        {!! Form::hidden('per_page', request('per_page', 10)) !!}
                        {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                        {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Día</th>
                                <th>Grupo</th>
                                <th>Turno</th>
                                <th>Zona</th>
                                <th>Vehículo</th>
                                <th>Conductor</th>
                                <th>Ayudantes</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($schedules as $schedule)
                                <tr>
                                    <td><code>{{ $schedule->fecha_programada->format('d/m/Y') }}</code></td>
                                    <td>{{ $dayLabels[$schedule->fecha_programada->dayOfWeekIso] ?? '-' }}</td>
                                    <td>{{ $schedule->personnelGroup?->name ?? '-' }}</td>
                                    <td>{{ $schedule->shift->name }}</td>
                                    <td>{{ $schedule->zone->name }}</td>
                                    <td>{{ $schedule->vehicle->name }} <code>{{ $schedule->vehicle->placa }}</code></td>
                                    <td>{{ $schedule->driver->full_name }}</td>
                                    <td>{{ $schedule->helpers->pluck('full_name')->implode(', ') }}</td>
                                    <td><span class="badge {{ $statusClasses[$schedule->status] ?? 'badge-secondary' }}">{{ $schedule->status_label }}</span></td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#historyProgramacionModal{{ $schedule->id }}" title="Historial">
                                            <i class="fas fa-history"></i>
                                        </button>
                                        @if ($schedule->status !== \App\Models\Programacion::STATUS_FINALIZED)
                                            <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editProgramacionModal{{ $schedule->id }}" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            {!! Form::open(['route' => ['programaciones.finalize', $schedule], 'method' => 'PUT', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas finalizar esta programación?']) !!}
                                                <button class="btn btn-success btn-sm" title="Finalizar"><i class="fas fa-check"></i></button>
                                            {!! Form::close() !!}
                                        @endif
                                        {!! Form::open(['route' => ['programaciones.destroy', $schedule], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas eliminar esta programación?']) !!}
                                            <button class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No hay programaciones registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="rsu-table-footer">
                    <span>Mostrando {{ $schedules->firstItem() ?? 0 }} a {{ $schedules->lastItem() ?? 0 }} de {{ $schedules->total() }} registros</span>
                    {{ $schedules->links() }}
                </div>
            </div>
        </div>

        @foreach ($schedules as $schedule)
            @include('programaciones.partials.edit-modal', ['schedule' => $schedule])
            @include('programaciones.partials.history-modal', ['schedule' => $schedule])
        @endforeach
        @include('programaciones.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
