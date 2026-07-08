@extends('adminlte::page')

@section('title', 'Grupos de Personal')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-users"></i> Lista de Grupos de Personal</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createGrupoPersonalModal">
            <i class="fas fa-plus mr-1"></i> Nuevo Grupo
        </button>
    </div>
@stop

@section('content')
    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3">
                    {!! Form::open(['route' => 'grupos-personal.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'grupos-personal.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        {!! Form::hidden('per_page', request('per_page', 10)) !!}
                        {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                        {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Turno</th>
                                <th>Zona</th>
                                <th>Vehículo</th>
                                <th>Días</th>
                                <th>Conductor</th>
                                <th>Ayudantes</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($groups as $group)
                                <tr>
                                    <td class="font-weight-bold">{{ $group->name }}</td>
                                    <td>{{ $group->shift->name }}</td>
                                    <td>{{ $group->zone->name }}</td>
                                    <td>{{ $group->vehicle->name }} <code>{{ $group->vehicle->placa }}</code></td>
                                    <td>{{ $group->days_label }}</td>
                                    <td>{{ $group->driver->full_name }}</td>
                                    <td>{{ $group->helpers->pluck('full_name')->implode(', ') }}</td>
                                    <td>
                                        @if ($group->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td class="text-center text-nowrap">
                                        <a href="{{ route('grupos-personal.show', $group) }}" class="btn btn-info btn-sm" title="Detalles">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editGrupoPersonalModal{{ $group->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['grupos-personal.destroy', $group], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas eliminar este grupo de personal?']) !!}
                                            <button class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted">No hay grupos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $groups->firstItem() ?? 0 }} a {{ $groups->lastItem() ?? 0 }} de {{ $groups->total() }} registros</span>
                    {{ $groups->links() }}
                </div>
            </div>
        </div>

        @foreach ($groups as $group)
            @include('grupos-personal.partials.edit-modal', ['group' => $group])
        @endforeach
        @include('grupos-personal.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
