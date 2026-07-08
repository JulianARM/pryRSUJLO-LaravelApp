@extends('adminlte::page')

@section('title', 'Personal')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-user"></i> Lista de Personal</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createPersonalModal">
            <i class="fas fa-plus mr-1"></i> Nuevo Personal
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
                    {!! Form::open(['route' => 'personal.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'personal.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        {!! Form::hidden('per_page', request('per_page', 10)) !!}
                        {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                        {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>DNI</th>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Email</th>
                                <th>Tipo</th>
                                <th>Licencia</th>
                                <th>Estado</th>
                                <th>Creación</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($personal as $employee)
                                <tr>
                                    <td>
                                        <span class="rsu-avatar-thumb">
                                            @if ($employee->ruta_foto)
                                                <img src="{{ asset('storage/' . $employee->ruta_foto) }}" alt="{{ $employee->full_name }}">
                                            @else
                                                <i class="far fa-image text-muted"></i>
                                            @endif
                                        </span>
                                    </td>
                                    <td>{{ $employee->dni }}</td>
                                    <td>{{ $employee->nombres }}</td>
                                    <td>{{ $employee->apellidos }}</td>
                                    <td>{{ $employee->email }}</td>
                                    <td><span class="badge badge-info">{{ $employee->staffType->name }}</span></td>
                                    <td>{{ $employee->licencia ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $employee->activo ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $employee->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>{{ $employee->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editPersonalModal{{ $employee->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['personal.destroy', $employee], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas eliminar este personal?']) !!}
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted">No hay personal registrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $personal->firstItem() ?? 0 }} a {{ $personal->lastItem() ?? 0 }} de {{ $personal->total() }} registros</span>
                    {{ $personal->links() }}
                </div>
            </div>
        </div>

        @foreach ($personal as $employee)
            @include('personal.partials.edit-modal', ['employee' => $employee])
        @endforeach
        @include('personal.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
