@extends('adminlte::page')

@section('title', 'Modelos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-wrench"></i> Lista de Modelos de Vehículos</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createModeloVehiculoModal">
            <i class="fas fa-plus mr-1"></i> Nuevo Modelo
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
                {!! Form::open(['route' => 'modelos-vehiculo.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                    <span class="mr-2">Mostrar</span>
                    {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                    <span class="ml-2">registros</span>
                    {!! Form::hidden('q', request('q')) !!}
                {!! Form::close() !!}

                {!! Form::open(['route' => 'modelos-vehiculo.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
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
                            <th>Código</th>
                            <th>Marca</th>
                            <th>Descripción</th>
                            <th>Creación</th>
                            <th>Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($brandModels as $model)
                            <tr>
                                <td class="font-weight-bold">{{ $model->name }}</td>
                                <td><code>{{ $model->code }}</code></td>
                                <td><span class="badge badge-info">{{ $model->brand->name }}</span></td>
                                <td>{{ $model->descripcion ?: 'Sin descripción' }}</td>
                                <td>{{ $model->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $model->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editModeloVehiculoModal{{ $model->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {!! Form::open(['route' => ['modelos-vehiculo.destroy', $model], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas eliminar este modelo?']) !!}
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No hay modelos registrados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <span>Mostrando {{ $brandModels->firstItem() ?? 0 }} a {{ $brandModels->lastItem() ?? 0 }} de {{ $brandModels->total() }} registros</span>
                {{ $brandModels->links() }}
            </div>
        </div>
        </div>

        @foreach ($brandModels as $model)
            @include('modelos-vehiculo.partials.edit-modal', ['model' => $model, 'marcas' => $marcas])
        @endforeach
        @include('modelos-vehiculo.partials.create-modal', ['marcas' => $marcas])
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop

