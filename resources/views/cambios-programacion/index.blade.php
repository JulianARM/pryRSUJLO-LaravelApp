@extends('adminlte::page')

@section('title', 'Cambios')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="rsu-page-title"><i class="fas fa-history"></i> Lista de Cambios</h1>
        <button class="btn btn-primary" data-toggle="modal" data-target="#createMassChangeModal">
            <i class="fas fa-random mr-1"></i> Nuevo Cambio Masivo
        </button>
    </div>
@stop

@section('content')
    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'cambios-programacion.index', 'method' => 'GET']) !!}
                    <div class="row align-items-end">
                        <div class="col-md-3 form-group">
                            {!! Form::label('fecha_inicio', 'Fecha de inicio') !!}
                            {!! Form::date('fecha_inicio', request('fecha_inicio'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-3 form-group">
                            {!! Form::label('fecha_fin', 'Fecha de fin') !!}
                            {!! Form::date('fecha_fin', request('fecha_fin'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-3 form-group">
                            {!! Form::label('tipo_cambio', 'Tipo de cambio') !!}
                            {!! Form::select('tipo_cambio', ['' => 'Todos'] + $changeTypes, request('tipo_cambio'), ['class' => 'form-control']) !!}
                        </div>
                        <div class="col-md-3 form-group">
                            <button class="btn btn-primary"><i class="fas fa-filter mr-1"></i> Filtrar</button>
                            <a href="{{ route('cambios-programacion.index') }}" class="btn btn-outline-secondary"><i class="fas fa-eraser mr-1"></i> Limpiar</a>
                        </div>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fecha de cambio</th>
                                <th>Fecha programación afectada</th>
                                <th>Programación</th>
                                <th>Zona</th>
                                <th>Tipo</th>
                                <th>Motivo</th>
                                <th>Usuario</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($changes as $change)
                                <tr>
                                    <td>{{ $change->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $change->fecha_programacion_afectada?->format('d/m/Y') ?? '-' }}</td>
                                    <td>#{{ $change->programacion_id }}</td>
                                    <td>{{ $change->schedule?->zone?->name ?? '-' }}</td>
                                    <td><span class="badge badge-info">{{ $changeTypes[$change->tipo_cambio] ?? $change->action }}</span></td>
                                    <td>{{ $change->reason?->name ?? $change->descripcion }}</td>
                                    <td>{{ $change->user?->name ?? '-' }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#changeDetailModal{{ $change->id }}" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        {!! Form::open(['route' => ['cambios-programacion.destroy', $change], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => '¿Deseas eliminar este cambio y revertir la programación afectada a sus valores anteriores?']) !!}
                                            <button class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">No hay cambios registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="rsu-table-footer">
                    <span>Mostrando {{ $changes->firstItem() ?? 0 }} a {{ $changes->lastItem() ?? 0 }} de {{ $changes->total() }} registros</span>
                    {{ $changes->links() }}
                </div>
            </div>
        </div>

        <div class="modal fade" id="createMassChangeModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    {!! Form::open(['route' => 'cambios-programacion.mass.store', 'method' => 'POST', 'class' => 'js-ajax-form']) !!}
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title"><i class="fas fa-random mr-1"></i> Nuevo Cambio Masivo</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning">
                                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Importante:</strong>
                                Esta acción afectará las programaciones registradas en el rango seleccionado y quedará registrada en el historial.
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">{!! Form::label('fecha_inicio', 'Fecha de inicio *') !!}{!! Form::date('fecha_inicio', now()->toDateString(), ['class' => 'form-control', 'required' => true]) !!}</div>
                                <div class="col-md-6 form-group">{!! Form::label('fecha_fin', 'Fecha de fin *') !!}{!! Form::date('fecha_fin', now()->toDateString(), ['class' => 'form-control', 'required' => true]) !!}</div>
                                <div class="col-md-6 form-group">{!! Form::label('zona_id', 'Zona *') !!}{!! Form::select('zona_id', $zonas, null, ['class' => 'form-control js-select2', 'placeholder' => 'Seleccione una zona', 'required' => true]) !!}</div>
                                <div class="col-md-6 form-group">{!! Form::label('tipo_cambio', 'Tipo de cambio *') !!}{!! Form::select('tipo_cambio', $changeTypes, null, ['class' => 'form-control js-mass-change-type', 'placeholder' => 'Seleccione el tipo', 'required' => true]) !!}</div>
                                <div class="col-md-6 form-group js-mass-change-target d-none" data-change-target="shift">{!! Form::label('turno_id', 'Nuevo turno *') !!}{!! Form::select('turno_id', $turnos, null, ['class' => 'form-control js-select2', 'placeholder' => 'Seleccione un turno']) !!}</div>
                                <div class="col-md-6 form-group js-mass-change-target d-none" data-change-target="driver">{!! Form::label('conductor_id', 'Nuevo conductor *') !!}{!! Form::select('conductor_id', $drivers, null, ['class' => 'form-control js-select2', 'placeholder' => 'Seleccione un conductor']) !!}</div>
                                <div class="col-md-12 form-group js-mass-change-target d-none" data-change-target="helper">{!! Form::label('helper_ids', 'Nuevos ocupantes *') !!}{!! Form::select('helper_ids[]', $helpers, [], ['class' => 'form-control js-select2', 'multiple' => true]) !!}</div>
                                <div class="col-md-6 form-group js-mass-change-target d-none" data-change-target="vehicle">{!! Form::label('vehiculo_id', 'Nuevo vehículo *') !!}{!! Form::select('vehiculo_id', $vehiculos, null, ['class' => 'form-control js-select2', 'placeholder' => 'Seleccione un vehículo']) !!}</div>
                                <div class="col-md-6 form-group">{!! Form::label('motivo_cambio_id', 'Motivo *') !!}{!! Form::select('motivo_cambio_id', $reasons, null, ['class' => 'form-control js-select2', 'placeholder' => 'Seleccione un motivo', 'required' => true]) !!}</div>
                                <div class="col-md-12 form-group">{!! Form::label('detail', 'Detalle del motivo *') !!}{!! Form::textarea('detail', null, ['class' => 'form-control', 'rows' => 3, 'required' => true]) !!}</div>
                            </div>
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" id="confirmMassChange" name="confirm_mass_change" value="1" required>
                                <label for="confirmMassChange" class="custom-control-label font-weight-bold">Confirmo que deseo aplicar estos cambios y registrar el historial correspondiente.</label>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                            <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Aplicar cambios</button>
                        </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        @foreach ($changes as $change)
            <div class="modal fade" id="changeDetailModal{{ $change->id }}" tabindex="-1">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary">
                            <h5 class="modal-title"><i class="fas fa-eye mr-1"></i> Detalles de Cambio #{{ $change->id }}</h5>
                            <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            <div class="rsu-change-detail-header">
                                <div><span>Tipo</span><strong>{{ $changeTypes[$change->tipo_cambio] ?? $change->action }}</strong></div>
                                <div><span>Usuario</span><strong>{{ $change->user?->name ?? '-' }}</strong></div>
                                <div><span>Motivo</span><strong>{{ $change->reason?->name ?? $change->descripcion }}</strong></div>
                                <div><span>Programación</span><strong>#{{ $change->programacion_id }}</strong></div>
                                <div><span>Fecha de cambio</span><strong>{{ $change->created_at->format('d/m/Y H:i') }}</strong></div>
                                <div><span>Fecha programación</span><strong>{{ $change->fecha_programacion_afectada?->format('d/m/Y') ?? '-' }}</strong></div>
                            </div>

                            <div class="rsu-change-detail-note">
                                <i class="fas fa-comment-alt"></i>
                                <div>
                                    <span>Detalle</span>
                                    <strong>{{ $change->detail ?: '-' }}</strong>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-lg-6 mb-3">
                                    <div class="rsu-change-panel">
                                        <h6><i class="fas fa-history"></i> Antes del cambio</h6>
                                        @include('cambios-programacion.partials.valores-programacion', ['values' => $change->valores_anteriores ?? []])
                                    </div>
                                </div>
                                <div class="col-lg-6 mb-3">
                                    <div class="rsu-change-panel is-new">
                                        <h6><i class="fas fa-check-circle"></i> Después del cambio</h6>
                                        @include('cambios-programacion.partials.valores-programacion', ['values' => $change->valores_nuevos ?? []])
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
    <script>
        document.addEventListener('change', function (event) {
            const select = event.target.closest('.js-mass-change-type');
            if (!select) return;
            document.querySelectorAll('.js-mass-change-target').forEach((target) => {
                target.classList.toggle('d-none', target.dataset.changeTarget !== select.value);
            });
        });
    </script>
@stop