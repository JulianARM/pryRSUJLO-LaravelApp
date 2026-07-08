@extends('adminlte::page')

@section('title', 'Marcas')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-tags"></i> Lista de Marcas</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createMarcaModal">
            <i class="fas fa-plus mr-1"></i> Nueva Marca
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
                {!! Form::open(['route' => 'marcas.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                    <span class="mr-2">Mostrar</span>
                    {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                    <span class="ml-2">registros</span>
                    {!! Form::hidden('q', request('q')) !!}
                {!! Form::close() !!}

                {!! Form::open(['route' => 'marcas.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                    {!! Form::hidden('per_page', request('per_page', 10)) !!}
                    {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                    {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                {!! Form::close() !!}
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Logo</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Creación</th>
                            <th>Actualización</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($marcas as $brand)
                            <tr>
                                <td>
                                    <span class="rsu-logo-thumb">
                                        @if ($brand->ruta_logo)
                                            <img src="{{ asset('storage/' . $brand->ruta_logo) }}" alt="{{ $brand->name }}">
                                        @else
                                            <strong>{{ Str::upper(Str::substr($brand->name, 0, 2)) }}</strong>
                                        @endif
                                    </span>
                                </td>
                                <td class="font-weight-bold">{{ $brand->name }}</td>
                                <td>{{ $brand->descripcion ?: 'Sin descripción' }}</td>
                                <td>{{ $brand->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $brand->updated_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editMarcaModal{{ $brand->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    {!! Form::open(['route' => ['marcas.destroy', $brand], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas eliminar esta marca?']) !!}
                                        <button class="btn btn-danger btn-sm">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    {!! Form::close() !!}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No hay marcas registradas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <span>Mostrando {{ $marcas->firstItem() ?? 0 }} a {{ $marcas->lastItem() ?? 0 }} de {{ $marcas->total() }} registros</span>
                {{ $marcas->links() }}
            </div>
        </div>
        </div>

        @foreach ($marcas as $brand)
            @include('marcas.partials.edit-modal', ['brand' => $brand])
        @endforeach
        @include('marcas.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
