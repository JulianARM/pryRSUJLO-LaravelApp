@extends('adminlte::page')

@section('title', 'Feriados')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-flag"></i> Listado de Feriados</h1>
        <div class="btn-group">
            {!! Form::open(['route' => 'feriados.load-peru', 'method' => 'POST', 'class' => 'js-ajax-form d-inline']) !!}
                {!! Form::hidden('anio', $stats['anio']) !!}
                <button class="btn btn-success" type="submit">
                    <i class="fas fa-calendar-plus mr-1"></i> Cargar Feriados Perú
                </button>
            {!! Form::close() !!}
            <button class="btn btn-primary" data-toggle="modal" data-target="#createFeriadoModal">
                <i class="fas fa-plus mr-1"></i> Nuevo Feriado
            </button>
        </div>
    </div>
@stop

@section('content')
    @php
        $days = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miércoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sábado',
            7 => 'domingo',
        ];
    @endphp

    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="row">
            <div class="col-md-3">
                <div class="small-box bg-info">
                    <div class="inner">
                        <h3>{{ $stats['total'] }}</h3>
                        <p>Total Feriados</p>
                    </div>
                    <div class="icon"><i class="fas fa-flag"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-success">
                    <div class="inner">
                        <h3>{{ $stats['active'] }}</h3>
                        <p>Feriados Activos</p>
                    </div>
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-warning">
                    <div class="inner">
                        <h3>{{ $stats['upcoming'] }}</h3>
                        <p>Próximos Feriados</p>
                    </div>
                    <div class="icon"><i class="fas fa-hourglass-half"></i></div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="small-box bg-teal">
                    <div class="inner">
                        <h3>{{ $stats['anio'] }}</h3>
                        <p>Año Actual</p>
                    </div>
                    <div class="icon"><i class="fas fa-calendar"></i></div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'feriados.index', 'method' => 'GET']) !!}
                    <div class="form-row align-items-end">
                        <div class="form-group col-md-3">
                            {!! Form::label('date_start', 'Fecha inicio') !!}
                            {!! Form::date('date_start', request('date_start'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('date_end', 'Fecha fin') !!}
                            {!! Form::date('date_end', request('date_end'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            {!! Form::label('status', 'Estado') !!}
                            {!! Form::select('status', ['' => 'Todos', 1 => 'Activos', 0 => 'Inactivos'], request('status'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="form-group col-md-3">
                            <button class="btn btn-primary btn-block" type="submit">
                                <i class="fas fa-filter mr-1"></i> Filtrar
                            </button>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3">
                    {!! Form::open(['route' => 'feriados.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'feriados.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
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
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Día</th>
                                <th>Creación</th>
                                <th>Actualización</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($feriados as $holiday)
                                <tr>
                                    <td><code>{{ $holiday->date->format('d/m/Y') }}</code></td>
                                    <td>{{ $holiday->descripcion }}</td>
                                    <td>
                                        @if ($holiday->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-info">{{ $days[$holiday->date->dayOfWeekIso] }}</span></td>
                                    <td>{{ $holiday->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $holiday->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editFeriadoModal{{ $holiday->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['feriados.destroy', $holiday], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas eliminar este feriado?']) !!}
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No hay feriados registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $feriados->firstItem() ?? 0 }} a {{ $feriados->lastItem() ?? 0 }} de {{ $feriados->total() }} registros</span>
                    {{ $feriados->links() }}
                </div>
            </div>
        </div>

        @foreach ($feriados as $holiday)
            @include('feriados.partials.edit-modal', ['holiday' => $holiday])
        @endforeach
        @include('feriados.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
