@extends('adminlte::page')

@section('title', 'Tipos de Vehículos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-truck"></i> Lista de Tipos de Vehículos</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createTipoVehiculoModal">
            <i class="fas fa-plus mr-1"></i> Nuevo Tipo
        </button>
    </div>
@stop

@section('content')
    <div class="js-crud-container">
        <div class="js-flash-messages">
            @include('partials.alerts')
        </div>

        <div class="card">
            <div class="card-body">
            <div class="d-flex justify-content-between flex-wrap mb-3">
                {!! Form::open(['route' => 'tipos-vehiculo.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                    <span class="mr-2">Mostrar</span>
                    {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                    <span class="ml-2">registros</span>
                    {!! Form::hidden('q', request('q')) !!}
                {!! Form::close() !!}

                {!! Form::open(['route' => 'tipos-vehiculo.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
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
                            <th>Creación</th>
                            <th>Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($vehicleTypes as $type)
                            <tr>
                                <td class="font-weight-bold">{{ $type->name }}</td>
                                <td>{{ $type->descripcion ?: 'Sin descripción' }}</td>
                                <td>{{ $type->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $type->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTipoVehiculoModal{{ $type->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {!! Form::open(['route' => ['tipos-vehiculo.destroy', $type], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas eliminar este tipo?']) !!}
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay tipos de vehículos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <span>Mostrando {{ $vehicleTypes->firstItem() ?? 0 }} a {{ $vehicleTypes->lastItem() ?? 0 }} de {{ $vehicleTypes->total() }} registros</span>
                {{ $vehicleTypes->links() }}
            </div>
        </div>
        </div>

        @foreach ($vehicleTypes as $type)
            @include('tipos-vehiculo.partials.edit-modal', ['type' => $type])
        @endforeach
        @include('tipos-vehiculo.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop

