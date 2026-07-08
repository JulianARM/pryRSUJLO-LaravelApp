@extends('adminlte::page')

@section('title', 'Contratos')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-file-contract"></i> Lista de Contratos</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createContratoModal">
            <i class="fas fa-plus mr-1"></i> Nuevo Contrato
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
                    {!! Form::open(['route' => 'contratos.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'contratos.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
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
                                <th>Tipo de contrato</th>
                                <th>Inicio</th>
                                <th>Fin</th>
                                <th>Salario</th>
                                <th>Posicion</th>
                                <th>Activo</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($contratos as $contract)
                                <tr>
                                    <td>{{ $contract->employee->dni }}</td>
                                    <td>{{ $contract->employee->full_name }}</td>
                                    <td><span class="badge badge-info">{{ $contract->tipo_contrato_label }}</span></td>
                                    <td>{{ $contract->fecha_inicio->format('d/m/Y') }}</td>
                                    <td>{{ $contract->fecha_fin?->format('d/m/Y') ?? '-' }}</td>
                                    <td><span class="badge badge-success">S/ {{ number_format((float) $contract->salario, 2) }}</span></td>
                                    <td>{{ $contract->cargo }}</td>
                                    <td>
                                        <span class="badge {{ $contract->activo ? 'badge-success' : 'badge-secondary' }}">
                                            {{ $contract->activo ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editContratoModal{{ $contract->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['contratos.destroy', $contract], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas desactivar este contrato?']) !!}
                                            <button class="btn btn-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No hay contratos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $contratos->firstItem() ?? 0 }} a {{ $contratos->lastItem() ?? 0 }} de {{ $contratos->total() }} registros</span>
                    {{ $contratos->links() }}
                </div>
            </div>
        </div>

        @foreach ($contratos as $contract)
            @include('contratos.partials.edit-modal', ['contract' => $contract])
        @endforeach
        @include('contratos.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
