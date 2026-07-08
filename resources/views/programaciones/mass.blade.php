@extends('adminlte::page')

@section('title', 'Programación Masiva')
@section('plugins.Select2', true)

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <h1 class="rsu-page-title"><i class="fas fa-calendar-plus"></i> Programación Masiva</h1>
        <a href="{{ route('programaciones.index') }}" class="btn btn-outline-primary">
            <i class="fas fa-list mr-1"></i> Ver programaciones
        </a>
    </div>
@stop

@section('content')
    @php
        $shiftFilterOptions = ['' => 'Todos los turnos'] + $turnos->toArray();
        $zoneFilterOptions = ['' => 'Todas las zonas'] + $zonas->toArray();
    @endphp

    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card">
            <div class="card-body">
                {!! Form::open(['route' => 'programaciones.mass', 'method' => 'GET', 'class' => 'rsu-filter-form']) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('fecha_inicio', 'Fecha de inicio *') !!}
                                {!! Form::date('fecha_inicio', $startDate, ['class' => 'form-control', 'required' => true]) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('fecha_fin', 'Fecha de fin *') !!}
                                {!! Form::date('fecha_fin', $endDate, ['class' => 'form-control', 'required' => true]) !!}
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="w-100 mb-3">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search mr-1"></i> Cargar grupos
                                </button>
                            </div>
                        </div>
                    </div>
                {!! Form::close() !!}

                <div class="alert alert-info mb-0">
                    <strong><i class="fas fa-info-circle mr-1"></i> Información:</strong>
                    Esta opción genera programaciones para todos los grupos activos del rango seleccionado. El filtro de turno solo reduce la lista visible; si seleccionas "Todos los turnos", el lote puede incluir grupos de diferentes turnos. El sistema valida feriados, contratos vigentes, vacaciones, cruces de personal, cruces de vehículo y programaciones ya existentes.
                </div>
            </div>
        </div>

        @if ($startDate && $endDate)
            <div class="row">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <strong><i class="fas fa-flag mr-1"></i> Feriados del rango</strong>
                        </div>
                        <div class="card-body">
                            @forelse ($massFeriados as $holiday)
                                <div class="rsu-holiday-chip {{ $holiday->activo ? 'is-active' : 'is-inactive' }}">
                                    <strong>{{ $holiday->date->format('d/m/Y') }}</strong>
                                    <span>{{ $holiday->descripcion }}</span>
                                    <small>{{ $holiday->activo ? 'Activo: se omitirá al generar' : 'Inactivo: no afecta la programación' }}</small>
                                </div>
                            @empty
                                <p class="text-muted mb-0">No hay feriados registrados en este rango.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="col-lg-8">
                    <div class="card h-100">
                        <div class="card-header bg-light">
                            <strong><i class="fas fa-clipboard-check mr-1"></i> Resumen de validación</strong>
                        </div>
                        <div class="card-body">
                            @if (! $massValidated)
                                <p class="text-muted mb-0">Carga los grupos y presiona <strong>Validar disponibilidad</strong> para revisar inconsistencias antes de generar.</p>
                            @else
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="rsu-summary-tile is-info">
                                            <span>Grupos revisados</span>
                                            <strong>{{ $massGroups->count() }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rsu-summary-tile is-success">
                                            <span>Grupos disponibles</span>
                                            <strong>{{ collect($massResults)->where('available', true)->where('skipped', false)->count() }}</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="rsu-summary-tile {{ $massHasIssues ? 'is-danger' : 'is-success' }}">
                                            <span>Inconsistencias</span>
                                            <strong>{{ collect($massResults)->filter(fn ($result) => ! ($result['available'] ?? false))->count() }}</strong>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-primary">
                    <h3 class="card-title mb-0"><i class="fas fa-users-cog mr-1"></i> Grupos a programar</h3>
                </div>
                <div class="card-body">
                    @if ($massGroups->isEmpty())
                        <div class="alert alert-warning mb-0">No se encontraron grupos activos para los filtros seleccionados.</div>
                    @else
                        <div class="rsu-mass-toolbar">
                            <div>
                                <strong>Grupos cargados: <span class="js-mass-total">{{ $massGroups->count() }}</span></strong>
                                <span class="text-muted ml-2">Visibles: <span class="js-mass-visible">{{ $massGroups->count() }}</span></span>
                            </div>
                            <div class="rsu-mass-filters">
                                <div class="rsu-mass-filter">
                                    {!! Form::label('mass_shift_filter', 'Filtrar grupos cargados por turno', ['class' => 'mb-1']) !!}
                                    {!! Form::select('mass_shift_filter', $shiftFilterOptions, $selectedTurnoId, ['class' => 'form-control js-mass-shift-filter']) !!}
                                </div>
                                <div class="rsu-mass-filter">
                                    {!! Form::label('mass_zone_filter', 'Filtrar grupos cargados por zona', ['class' => 'mb-1']) !!}
                                    {!! Form::select('mass_zone_filter', $zoneFilterOptions, $selectedMassZonaId, ['class' => 'form-control js-mass-zone-filter']) !!}
                                </div>
                                <small class="form-text text-muted w-100">Estos filtros no recargan la página ni eliminan selecciones. Solo ocultan o muestran filas.</small>
                            </div>
                        </div>

                        {!! Form::open(['route' => 'programaciones.mass.validate', 'method' => 'POST']) !!}
                            {!! Form::hidden('fecha_inicio', $startDate) !!}
                            {!! Form::hidden('fecha_fin', $endDate) !!}
                            {!! Form::hidden('turno_id', $selectedTurnoId, ['class' => 'js-mass-shift-state']) !!}
                            {!! Form::hidden('mass_zone_filter', $selectedMassZonaId, ['class' => 'js-mass-zone-state']) !!}

                            <div class="table-responsive">
                                <table class="table table-striped table-hover rsu-mass-table">
                                    <thead>
                                        <tr>
                                            <th>Usar</th>
                                            <th>Grupo</th>
                                            <th>Base</th>
                                            <th>Conductor</th>
                                            <th>Ayudantes</th>
                                            <th>Validación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($massGroups as $group)
                                            @php
                                                $row = $massRows[$group->id] ?? [];
                                                $result = $massResults[$group->id] ?? null;
                                                $expectedHelpers = max(((int) $group->vehicle->capacidad_personas) - 1, 0);
                                            @endphp
                                            <tr class="js-mass-group-row" data-shift-id="{{ $group->turno_id }}" data-zone-id="{{ $group->zona_id }}">
                                                <td>
                                                    <div class="custom-control custom-switch">
                                                        <input type="hidden" name="groups[{{ $group->id }}][enabled]" value="0">
                                                        <input type="checkbox" class="custom-control-input" id="massEnabled{{ $group->id }}" name="groups[{{ $group->id }}][enabled]" value="1" @checked($row['enabled'] ?? true)>
                                                        <label class="custom-control-label" for="massEnabled{{ $group->id }}">Sí</label>
                                                    </div>
                                                </td>
                                                <td>
                                                    <strong>{{ $group->name }}</strong>
                                                    <div class="small text-muted">{{ $group->days_label }}</div>
                                                </td>
                                                <td>
                                                    <div><i class="fas fa-clock text-primary mr-1"></i>{{ $group->shift->name }}</div>
                                                    <div><i class="fas fa-map-marker-alt text-danger mr-1"></i>{{ $group->zone->name }}</div>
                                                    <div><i class="fas fa-truck text-info mr-1"></i>{{ $group->vehicle->name }} <code>{{ $group->vehicle->placa }}</code></div>
                                                </td>
                                                <td class="rsu-mass-person-cell">
                                                    {!! Form::select("groups[{$group->id}][conductor_id]", $drivers, $row['conductor_id'] ?? $group->conductor_id, ['class' => 'form-control js-select2', 'placeholder' => 'Seleccione conductor']) !!}
                                                </td>
                                                <td class="rsu-mass-person-cell">
                                                    @for ($index = 0; $index < $expectedHelpers; $index++)
                                                        <div class="mb-2">
                                                            {!! Form::select("groups[{$group->id}][helper_ids][]", $helpers, ($row['helper_ids'][$index] ?? null), ['class' => 'form-control js-select2', 'placeholder' => 'Ayudante '.($index + 1)]) !!}
                                                        </div>
                                                    @endfor
                                                    <small class="text-muted">Capacidad: 1 conductor + {{ $expectedHelpers }} ayudante(s).</small>
                                                </td>
                                                <td>
                                                    @if (! $result)
                                                        <span class="badge badge-secondary">Sin validar</span>
                                                    @elseif ($result['skipped'])
                                                        <span class="badge badge-secondary">Omitido</span>
                                                    @elseif ($result['available'])
                                                        <div class="alert alert-success py-2 mb-0">
                                                            <strong>Disponible</strong>
                                                            <div class="small">{{ $result['count'] }} programación(es) por generar.</div>
                                                            @if (! empty($result['warnings']))
                                                                <ul class="mb-0 pl-3 small">
                                                                    @foreach ($result['warnings'] as $warning)
                                                                        <li>{{ $warning }}</li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <div class="alert alert-danger py-2 mb-0">
                                                            <strong>Inconsistencias</strong>
                                                            <ul class="mb-0 pl-3 small">
                                                                @foreach ($result['issues'] as $issue)
                                                                    <li>{{ $issue }}</li>
                                                                @endforeach
                                                            </ul>
                                                            @foreach ($result['suggestions'] as $suggestion)
                                                                <div class="mt-2 small">
                                                                    <strong>{{ $suggestion['label'] }}:</strong>
                                                                    @forelse ($suggestion['replacements'] as $replacement)
                                                                        <span class="badge badge-light">{{ $replacement['label'] }}</span>
                                                                    @empty
                                                                        <span class="text-muted">Sin reemplazos disponibles.</span>
                                                                    @endforelse
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="form-group">
                                {!! Form::label('notes', 'Notas generales') !!}
                                {!! Form::textarea('notes', old('notes', request('notes')), ['class' => 'form-control', 'rows' => 2, 'placeholder' => 'Observaciones para las programaciones generadas...']) !!}
                            </div>

                            @if ($massValidated && ! $massHasIssues)
                                <div class="form-group">
                                    {!! Form::label('reason', 'Motivo de generación masiva *') !!}
                                    {!! Form::text('reason', old('reason', 'Programación masiva generada para el rango seleccionado.'), ['class' => 'form-control', 'required' => true, 'maxlength' => 255]) !!}
                                </div>
                            @endif

                            <div class="d-flex justify-content-between flex-wrap">
                                <button type="submit" class="btn btn-outline-primary mb-2">
                                    <i class="fas fa-search mr-1"></i> Validar disponibilidad
                                </button>

                                @if ($massValidated && ! $massHasIssues)
                                    <button type="submit" class="btn btn-success mb-2" formaction="{{ route('programaciones.mass.store') }}">
                                        <i class="fas fa-calendar-plus mr-1"></i> Generar programación masiva
                                    </button>
                                @endif
                            </div>
                        {!! Form::close() !!}
                    @endif
                </div>
            </div>
        @endif
    </div>
@stop

@section('js')
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
