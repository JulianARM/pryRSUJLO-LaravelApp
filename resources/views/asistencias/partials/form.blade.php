@php
    $attendance = $attendance ?? null;
    $selectedPersonal = old('personal_id', $attendance?->personal_id);
    $selectedTurno = old('turno_id', $attendance?->turno_id);
    $selectedType = old('type', $attendance?->type ?? \App\Models\Asistencia::TYPE_IN);
    $shiftOptions = $turnos->map(fn ($shift) => [
        'id' => $shift->id,
        'name' => $shift->name,
        'start' => $shift->start_time->format('H:i'),
        'end' => $shift->end_time->format('H:i'),
    ])->values();
    $currentTurno = $turnos->firstWhere('id', (int) $selectedTurno);
@endphp

<div class="form-group">
    {!! Form::label('personal_id', 'Empleado *') !!}
    <select
        name="personal_id"
        class="form-control js-select2 js-employee-select"
        data-url="{{ route('personal.search') }}"
        data-attendance-type-url="{{ route('asistencias.suggested-type') }}"
        data-placeholder="Buscar empleado por nombre o DNI"
        required
    >
        <option value="">Buscar empleado por nombre o DNI</option>
        @foreach ($personal as $employee)
            <option value="{{ $employee->id }}" @selected((string) $selectedPersonal === (string) $employee->id)>
                {{ $employee->full_name }} - DNI: {{ $employee->dni }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">Busque por nombre, apellido o DNI del empleado.</small>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('fecha_asistencia', 'Fecha *') !!}
            {!! Form::input('date', 'fecha_asistencia', old('fecha_asistencia', $attendance?->fecha_asistencia?->format('Y-m-d') ?? today()->format('Y-m-d')), ['class' => 'form-control', 'required' => true]) !!}
            <small class="form-text text-muted">Seleccione la fecha de asistencia.</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('hora_asistencia', 'Hora *') !!}
            {!! Form::input('time', 'hora_asistencia', old('hora_asistencia', $attendance?->registrado_en?->format('H:i') ?? now()->format('H:i')), ['class' => 'form-control', 'required' => true]) !!}
            <small class="form-text text-muted">Seleccione la hora de registro.</small>
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            {!! Form::label('turno_id', 'Turno') !!}
            {!! Form::hidden('turno_id', $selectedTurno, ['class' => 'js-attendance-shift-id']) !!}
            <input
                type="text"
                class="form-control js-attendance-shift-name"
                value="{{ $currentTurno ? $currentTurno->name.' ('.$currentTurno->start_time->format('H:i').' - '.$currentTurno->end_time->format('H:i').')' : 'Se asignara segun la hora' }}"
                data-turnos='@json($shiftOptions)'
                readonly
            >
            <small class="form-text text-muted">Se calcula automaticamente segun la hora registrada.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('type', 'Tipo *') !!}
            {!! Form::hidden('type', $selectedType, ['class' => 'js-attendance-type']) !!}
            {!! Form::hidden('attendance_id', $attendance?->id, ['class' => 'js-attendance-id']) !!}
            <input
                type="text"
                class="form-control js-attendance-type-label"
                value="{{ \App\Models\Asistencia::TYPES[$selectedType] ?? 'Entrada' }}"
                readonly
            >
            <small class="form-text text-muted">Se calcula automaticamente segun los registros del dia.</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('status', 'Estado *') !!}
            {!! Form::select('status', $statuses, old('status', $attendance?->status ?? \App\Models\Asistencia::STATUS_PRESENT), ['class' => 'form-control', 'required' => true]) !!}
            <small class="form-text text-muted">Estado de la asistencia.</small>
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('notes', 'Notas') !!}
    {!! Form::textarea('notes', old('notes', $attendance?->notes), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Agregue notas adicionales sobre la asistencia...']) !!}
    <small class="form-text text-muted">Observaciones o comentarios sobre el registro.</small>
</div>
