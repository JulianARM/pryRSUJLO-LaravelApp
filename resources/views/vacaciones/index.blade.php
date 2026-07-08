@extends('adminlte::page')

@section('title', 'Vacaciones')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-plane"></i> Lista de Vacaciones</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createVacationModal">
            <i class="fas fa-plus mr-1"></i> Nueva Solicitud
        </button>
    </div>
@stop

@section('content')
    @php
        $statusClasses = [
            \App\Models\SolicitudVacacion::STATUS_PENDING => 'badge-warning',
            \App\Models\SolicitudVacacion::STATUS_APPROVED => 'badge-success',
            \App\Models\SolicitudVacacion::STATUS_REJECTED => 'badge-danger',
            \App\Models\SolicitudVacacion::STATUS_CANCELLED => 'badge-secondary',
        ];
    @endphp

    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3">
                    {!! Form::open(['route' => 'vacaciones.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'vacaciones.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        {!! Form::hidden('per_page', request('per_page', 10)) !!}
                        {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                        {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>DNI</th>
                                <th>Empleado</th>
                                <th>Fecha solicitud</th>
                                <th>Fecha de inicio</th>
                                <th>Fecha de termino</th>
                                <th>Días S.</th>
                                <th>Estado</th>
                                <th>Días R.</th>
                                <th>Notas</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vacations as $vacation)
                                <tr>
                                    <td><code>{{ $vacation->employee->dni }}</code></td>
                                    <td>{{ $vacation->employee->full_name }}</td>
                                    <td>{{ $vacation->fecha_solicitud->format('d/m/Y') }}</td>
                                    <td>{{ $vacation->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>{{ $vacation->fecha_fin->format('d/m/Y') }}</td>
                                    <td><span class="badge badge-info">{{ $vacation->dias_solicitados }}</span></td>
                                    <td><span class="badge {{ $statusClasses[$vacation->status] ?? 'badge-secondary' }}">{{ $vacation->status_label }}</span></td>
                                    <td>
                                        <span class="badge badge-secondary">
                                            {{ $vacation->status === \App\Models\SolicitudVacacion::STATUS_APPROVED ? $vacation->dias_restantes : $vacation->employee->vacationDaysAvailable($vacation->fecha_inicio->year) }}
                                        </span>
                                    </td>
                                    <td>{{ $vacation->notes ?: '-' }}</td>
                                    <td class="text-center text-nowrap">
                                        @if ($vacation->status === \App\Models\SolicitudVacacion::STATUS_PENDING)
                                            {!! Form::open(['route' => ['vacaciones.approve', $vacation], 'method' => 'PUT', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas aprobar esta solicitud de vacaciones?']) !!}
                                                <button class="btn btn-success btn-sm"><i class="fas fa-check"></i></button>
                                            {!! Form::close() !!}
                                            {!! Form::open(['route' => ['vacaciones.reject', $vacation], 'method' => 'PUT', 'class' => 'd-inline js-ajax-form']) !!}
                                                <button class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button>
                                            {!! Form::close() !!}
                                            <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#editVacationModal{{ $vacation->id }}">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        {!! Form::open(['route' => ['vacaciones.destroy', $vacation], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas eliminar esta solicitud?']) !!}
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No hay solicitudes registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $vacations->firstItem() ?? 0 }} a {{ $vacations->lastItem() ?? 0 }} de {{ $vacations->total() }} registros</span>
                    {{ $vacations->links() }}
                </div>
            </div>
        </div>

        @foreach ($vacations as $vacation)
            @if ($vacation->status === \App\Models\SolicitudVacacion::STATUS_PENDING)
                @include('vacaciones.partials.edit-modal', ['vacation' => $vacation])
            @endif
        @endforeach
        @include('vacaciones.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
