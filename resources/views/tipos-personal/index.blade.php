@extends('adminlte::page')

@section('title', 'Tipos de Personal')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-user-tie"></i> Lista de Tipos de Personal</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createTipoPersonalModal">
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
                    {!! Form::open(['route' => 'tipos-personal.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'tipos-personal.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
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
                            @forelse ($staffTypes as $type)
                                <tr>
                                    <td><span class="badge badge-info">{{ $type->name }}</span></td>
                                    <td>{{ $type->descripcion ?: 'Sin descripción' }}</td>
                                    <td>{{ $type->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $type->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editTipoPersonalModal{{ $type->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['tipos-personal.destroy', $type], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas eliminar este tipo de personal?']) !!}
                                            <button class="btn btn-danger btn-sm" @disabled($type->es_sistema)>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">No hay tipos de personal registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $staffTypes->firstItem() ?? 0 }} a {{ $staffTypes->lastItem() ?? 0 }} de {{ $staffTypes->total() }} registros</span>
                    {{ $staffTypes->links() }}
                </div>
            </div>
        </div>

        @foreach ($staffTypes as $type)
            @include('tipos-personal.partials.edit-modal', ['type' => $type])
        @endforeach
        @include('tipos-personal.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
