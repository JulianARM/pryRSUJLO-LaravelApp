@php
    $detail = $scheduleDetails[$schedule->id] ?? [
        'team' => [],
        'has_issues' => false,
        'issues_count' => 0,
        'expected_helpers' => max(((int) ($schedule->vehicle?->capacidad_personas ?? 1)) - 1, 0),
    ];
    $expectedHelpers = (int) $detail['expected_helpers'];
    $selectedHelpers = $schedule->helpers->pluck('id')->values()->all();
    $dayOfWeek = $schedule->fecha_programada->dayOfWeekIso;
    $isFinalized = $schedule->status === \App\Models\Programacion::STATUS_FINALIZED;
@endphp

<div class="modal fade" id="dashboardScheduleDetailModal{{ $schedule->id }}" tabindex="-1" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-xl rsu-dashboard-detail-dialog">
        <div class="modal-content">
            {!! Form::open([
                'route' => ['dashboard.schedules.personnel.update', $schedule],
                'method' => 'PUT',
                'class' => 'js-ajax-form js-schedule-form',
                'data-validate-url' => route('programaciones.validate'),
                'data-require-schedule-validation' => 'true',
                'data-confirm' => '¿Deseas guardar el reemplazo de personal para esta programación?',
            ]) !!}
                <div class="modal-header bg-primary">
                    <h5 class="modal-title"><i class="fas fa-eye mr-1"></i> Detalle de Programación #{{ $schedule->id }}</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>

                <div class="modal-body rsu-dashboard-detail-body">
                    <input type="hidden" name="grupo_personal_id" value="{{ $schedule->grupo_personal_id }}">
                    <input type="hidden" name="fecha_inicio" value="{{ $schedule->fecha_programada->format('Y-m-d') }}">
                    <input type="hidden" name="fecha_fin" value="{{ $schedule->fecha_programada->format('Y-m-d') }}">
                    <input type="hidden" name="turno_id" value="{{ $schedule->turno_id }}" class="js-schedule-shift js-schedule-watch">
                    <input type="hidden" name="zona_id" value="{{ $schedule->zona_id }}" class="js-schedule-zone js-schedule-watch">
                    <input type="hidden" name="vehiculo_id" value="{{ $schedule->vehiculo_id }}" class="js-schedule-vehicle js-schedule-watch" data-vehicle-capacities='@json($vehicleCapacities)'>
                    <input type="checkbox" name="dias_semana[]" value="{{ $dayOfWeek }}" class="d-none js-schedule-day js-schedule-watch" checked>

                    <div class="rsu-dashboard-detail-hero {{ $detail['has_issues'] ? 'is-alert' : 'is-ok' }}">
                        <div>
                            <span class="rsu-detail-kicker">{{ $schedule->fecha_programada->format('d/m/Y') }} · {{ $schedule->shift->name }}</span>
                            <h4>{{ $schedule->zone->name }}</h4>
                            <p class="mb-0">{{ $schedule->vehicle->name }} · {{ $schedule->vehicle->placa }} | Grupo: {{ $schedule->personnelGroup?->name ?? '-' }}</p>
                        </div>
                        <div class="rsu-detail-status-pill">
                            <i class="fas {{ $detail['has_issues'] ? 'fa-exclamation-triangle' : 'fa-check-circle' }}"></i>
                            <strong>{{ $detail['has_issues'] ? 'Requiere revisión' : 'Equipo conforme' }}</strong>
                            <span>{{ $detail['issues_count'] }} observación(es)</span>
                        </div>
                    </div>

                    <div class="rsu-dashboard-section-title">
                        <div>
                            <strong>Estado del equipo asignado</strong>
                            <span>Validación de contrato, disponibilidad, vacaciones y asistencia registrada.</span>
                        </div>
                    </div>

                    <div class="rsu-dashboard-person-grid">
                        @foreach ($detail['team'] as $member)
                            <div class="rsu-dashboard-person-card {{ $member['is_ok'] ? 'is-ok' : 'is-alert' }}">
                                <div class="rsu-person-card-head">
                                    <span>{{ $member['role'] }}</span>
                                    <i class="fas {{ $member['is_ok'] ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                                </div>
                                <strong>{{ $member['person']->full_name }}</strong>
                                <small>DNI: {{ $member['person']->dni }}</small>
                                @if ($member['is_ok'])
                                    <div class="rsu-person-status-text"><i class="fas fa-check mr-1"></i> Disponible y con asistencia conforme.</div>
                                @else
                                    <ul class="rsu-person-issues">
                                        @foreach ($member['issues'] as $issue)
                                            <li>{{ $issue }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <div class="rsu-dashboard-replacement-panel">
                        <div class="rsu-dashboard-section-title mb-3">
                            <div>
                                <strong>Reemplazo de personal</strong>
                                <span>Modifica solo el personal necesario, valida disponibilidad y guarda el cambio con un motivo.</span>
                            </div>
                        </div>

                        @if ($isFinalized)
                            <div class="alert alert-success mb-0">
                                <i class="fas fa-lock mr-1"></i> Esta programación ya está finalizada. No se permiten reemplazos desde el dashboard diario.
                            </div>
                        @else
                            <div class="row">
                                <div class="col-lg-4 form-group">
                                    {!! Form::label('dashboardDriver'.$schedule->id, 'Conductor *') !!}
                                    {!! Form::select('conductor_id', $drivers, $schedule->conductor_id, [
                                        'id' => 'dashboardDriver'.$schedule->id,
                                        'class' => 'form-control js-select2 js-schedule-driver js-schedule-watch',
                                        'placeholder' => 'Seleccione conductor',
                                        'required' => true,
                                    ]) !!}
                                </div>
                                <div class="col-lg-8">
                                    <label>Ayudantes requeridos</label>
                                    <div class="rsu-dashboard-helper-grid js-schedule-helper-container" data-max-helpers="{{ max($expectedHelpers, 1) }}">
                                        @for ($index = 0; $index < max($expectedHelpers, 1); $index++)
                                            <div class="form-group js-schedule-helper-wrapper {{ $index >= $expectedHelpers ? 'd-none' : '' }}" data-helper-index="{{ $index }}">
                                                {!! Form::label('dashboardHelper'.$schedule->id.$index, 'Ayudante '.($index + 1).' *') !!}
                                                {!! Form::select('helper_ids[]', $helpers, $selectedHelpers[$index] ?? null, [
                                                    'id' => 'dashboardHelper'.$schedule->id.$index,
                                                    'class' => 'form-control js-select2 js-schedule-helper-select js-schedule-watch',
                                                    'placeholder' => 'Seleccione ayudante',
                                                    'required' => $index < $expectedHelpers,
                                                    'disabled' => $index >= $expectedHelpers,
                                                ]) !!}
                                            </div>
                                        @endfor
                                    </div>
                                    <small class="form-text text-muted js-schedule-helper-help">La programación debe contar exactamente con {{ $expectedHelpers }} ayudante(s).</small>
                                </div>
                            </div>

                            <div class="form-group">
                                {!! Form::label('changeReasonDashboard'.$schedule->id, 'Motivo del reemplazo *') !!}
                                {!! Form::text('change_reason', null, [
                                    'id' => 'changeReasonDashboard'.$schedule->id,
                                    'class' => 'form-control',
                                    'required' => true,
                                    'maxlength' => 255,
                                    'placeholder' => 'Ej: reemplazo por inasistencia o disponibilidad operativa',
                                ]) !!}
                            </div>

                            <div class="js-schedule-validation-result"></div>
                        @endif
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cerrar</button>
                    @unless ($isFinalized)
                        <button type="button" class="btn btn-outline-primary js-schedule-validate"><i class="fas fa-search mr-1"></i> Validar reemplazo</button>
                        <button type="submit" class="btn btn-primary js-schedule-submit" disabled><i class="fas fa-save mr-1"></i> Guardar reemplazo</button>
                    @endunless
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>