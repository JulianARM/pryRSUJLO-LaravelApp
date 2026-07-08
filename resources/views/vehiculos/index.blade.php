@extends('adminlte::page')

@section('title', 'Vehículos')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-car"></i> Lista de Vehículos</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createVehiculoModal">
            <i class="fas fa-plus mr-1"></i> Nuevo Vehículo
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
                    {!! Form::open(['route' => 'vehiculos.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'vehiculos.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        {!! Form::hidden('per_page', request('per_page', 10)) !!}
                        {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                        {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Imagen</th>
                                <th>Nombre</th>
                                <th>Código</th>
                                <th>Placa</th>
                                <th>Año</th>
                                <th>Capacidad</th>
                                <th>Marca</th>
                                <th>Modelo</th>
                                <th>Tipo</th>
                                <th>Color</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($vehiculos as $vehicle)
                                <tr>
                                    <td>
                                        <span class="rsu-table-thumb">
                                            @if ($vehicle->profileImage)
                                                <img src="{{ asset('storage/' . $vehicle->profileImage->path) }}" alt="{{ $vehicle->name }}">
                                            @else
                                                <i class="far fa-image text-muted"></i>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="font-weight-bold">{{ $vehicle->name }}</td>
                                    <td><code>{{ $vehicle->code }}</code></td>
                                    <td><code>{{ $vehicle->placa }}</code></td>
                                    <td>{{ $vehicle->anio }}</td>
                                    <td>{{ number_format((float) $vehicle->capacidad_carga, 0) }}</td>
                                    <td>{{ $vehicle->brand->name }}</td>
                                    <td>{{ $vehicle->model->name }}</td>
                                    <td>{{ $vehicle->type->name }}</td>
                                    <td><span class="rsu-swatch" style="background: {{ $vehicle->color->code }}"></span></td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editVehiculoModal{{ $vehicle->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#vehicleImagesModal{{ $vehicle->id }}">
                                            <i class="fas fa-image"></i>
                                        </button>
                                        {!! Form::open(['route' => ['vehiculos.destroy', $vehicle], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas eliminar este vehículo?']) !!}
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center text-muted">No hay vehículos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $vehiculos->firstItem() ?? 0 }} a {{ $vehiculos->lastItem() ?? 0 }} de {{ $vehiculos->total() }} registros</span>
                    {{ $vehiculos->links() }}
                </div>
            </div>
        </div>

        @foreach ($vehiculos as $vehicle)
            @include('vehiculos.partials.edit-modal', ['vehicle' => $vehicle])
            @include('vehiculos.partials.images-modal', ['vehicle' => $vehicle])
        @endforeach
        @include('vehiculos.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop

