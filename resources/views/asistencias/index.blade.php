@extends('adminlte::page')

@section('title', 'Asistencias')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-user-clock"></i> Listado de Asistencias</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createAsistenciaModal">
            <i class="fas fa-plus mr-1"></i> Agregar nueva asistencia
        </button>
    </div>
@stop

@section('content')
    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card mb-3">
            <div class="card-body">
                {!! Form::open(['route' => 'asistencias.index', 'method' => 'GET']) !!}
                    <div class="row align-items-end">
                        <div class="col-md-3">
                            {!! Form::label('date_from', 'Fecha de inicio') !!}
                            {!! Form::input('date', 'date_from', request('date_from'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-3">
                            {!! Form::label('date_to', 'Fecha de fin') !!}
                            {!! Form::input('date', 'date_to', request('date_to'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-4">
                            {!! Form::label('employee', 'Buscar empleado') !!}
                            {!! Form::text('employee', request('employee'), ['class' => 'form-control', 'placeholder' => 'DNI, nombre o apellido...']) !!}
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                            <a href="{{ route('asistencias.index') }}" class="btn btn-outline-secondary"><i class="fas fa-eraser mr-1"></i> Limpiar</a>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3">
                    {!! Form::open(['route' => 'asistencias.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('date_from', request('date_from')) !!}
                        {!! Form::hidden('date_to', request('date_to')) !!}
                        {!! Form::hidden('employee', request('employee')) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>DNI</th>
                                <th>Empleado</th>
                                <th>Fecha y Hora</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Turno</th>
                                <th>Notas</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($asistencias as $attendance)
                                <tr>
                                    <td><code>{{ $attendance->employee->dni }}</code></td>
                                    <td>{{ $attendance->employee->full_name }}</td>
                                    <td>{{ $attendance->registrado_en->format('d/m/Y') }}<br><small>{{ $attendance->registrado_en->format('H:i') }}</small></td>
                                    <td><span class="badge badge-success">{{ $attendance->type_label }}</span></td>
                                    <td><span class="badge badge-info">{{ $attendance->status_label }}</span></td>
                                    <td><span class="badge badge-primary">{{ $attendance->shift->name }}</span></td>
                                    <td>{{ $attendance->notes ?: '-' }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editAsistenciaModal{{ $attendance->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['asistencias.destroy', $attendance], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas eliminar esta asistencia?']) !!}
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay asistencias registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $asistencias->firstItem() ?? 0 }} a {{ $asistencias->lastItem() ?? 0 }} de {{ $asistencias->total() }} registros</span>
                    {{ $asistencias->links() }}
                </div>
            </div>
        </div>

        @foreach ($asistencias as $attendance)
            @include('asistencias.partials.edit-modal', ['attendance' => $attendance])
        @endforeach
        @include('asistencias.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
