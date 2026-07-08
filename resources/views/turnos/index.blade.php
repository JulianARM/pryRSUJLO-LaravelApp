@extends('adminlte::page')

@section('title', 'Turnos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-clock"></i> Lista de Turnos</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createTurnoModal">
            <i class="fas fa-plus mr-1"></i> Nuevo Turno
        </button>
    </div>
@stop

@section('content')
    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3">
                    {!! Form::open(['route' => 'turnos.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'turnos.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        {!! Form::hidden('per_page', request('per_page', 10)) !!}
                        {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                        {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Hora Entrada</th>
                                <th>Hora Salida</th>
                                <th>Fecha Creación</th>
                                <th>Fecha Actualización</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($turnos as $shift)
                                <tr>
                                    <td class="font-weight-bold">{{ $shift->name }}</td>
                                    <td>{{ $shift->descripcion ?: '-' }}</td>
                                    <td><span class="badge badge-success">{{ $shift->start_time->format('H:i:s') }}</span></td>
                                    <td><span class="badge badge-danger">{{ $shift->end_time->format('H:i:s') }}</span></td>
                                    <td>{{ $shift->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $shift->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTurnoModal{{ $shift->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['turnos.destroy', $shift], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas eliminar este turno?']) !!}
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">No hay turnos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $turnos->firstItem() ?? 0 }} a {{ $turnos->lastItem() ?? 0 }} de {{ $turnos->total() }} registros</span>
                    {{ $turnos->links() }}
                </div>
            </div>
        </div>

        @foreach ($turnos as $shift)
            @include('turnos.partials.edit-modal', ['shift' => $shift])
        @endforeach
        @include('turnos.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
