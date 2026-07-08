@php
    $vacation = $vacation ?? null;
    $selectedPersonal = old('personal_id', $vacation?->personal_id);
    $days = old('dias_solicitados', $vacation?->dias_solicitados);
    $startDate = old('fecha_inicio', $vacation?->fecha_inicio?->format('Y-m-d') ?? today()->format('Y-m-d'));
    $endDate = $vacation?->fecha_fin?->format('Y-m-d');
    $selectedPersonalRecord = $personal->firstWhere('id', (int) $selectedPersonal);
    $availableDays = $selectedPersonalRecord?->vacationDaysAvailable(now()->year);
@endphp

<div class="row">
    <div class="col-md-7">
        <div class="form-group">
            {!! Form::label('personal_id', 'Personal *') !!}
            <select
                name="personal_id"
                class="form-control js-select2 js-employee-select"
                data-url="{{ route('personal.search') }}"
                data-placeholder="Buscar empleado por nombre o DNI"
                data-vacation-eligible="1"
                required
            >
                <option value="">Buscar empleado por nombre o DNI</option>
                @foreach ($personal as $employee)
                    @php($employeeAvailableDays = $employee->vacationDaysAvailable(now()->year))
                    <option
                        value="{{ $employee->id }}"
                        data-available-days="{{ $employeeAvailableDays }}"
                        @selected((string) $selectedPersonal === (string) $employee->id)
                    >
                        {{ $employee->full_name }} - DNI: {{ $employee->dni }} ({{ $employeeAvailableDays }} días disponibles)
                    </option>
                @endforeach
            </select>
            <small class="form-text text-muted js-vacation-available-text">
                @if ($availableDays !== null)
                    Días disponibles: {{ $availableDays }}
                @else
                    Seleccione personal para ver sus días disponibles.
                @endif
            </small>
        </div>
    </div>
    <div class="col-md-5">
        <div class="form-group">
            {!! Form::label('dias_solicitados', 'Días Solicitados *') !!}
            {!! Form::number('dias_solicitados', $days, ['class' => 'form-control js-vacation-days', 'min' => 1, 'max' => $availableDays ?? 30, 'placeholder' => 'Número de días', 'required' => true]) !!}
            <small class="form-text text-muted js-vacation-days-help">No debe superar los días disponibles del personal.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('fecha_inicio', 'Fecha de Inicio *') !!}
            {!! Form::input('date', 'fecha_inicio', $startDate, ['class' => 'form-control js-vacation-start', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('fecha_fin', 'Fecha de Fin') !!}
            {!! Form::input('date', 'fecha_fin', $endDate, ['class' => 'form-control js-vacation-end', 'disabled' => true]) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('notes', 'Notas') !!}
    {!! Form::textarea('notes', old('notes', $vacation?->notes), ['class' => 'form-control', 'rows' => 3, 'placeholder' => 'Observaciones o comentarios sobre la solicitud...']) !!}
</div>

<div class="alert alert-warning mb-0">
    <strong><i class="fas fa-exclamation-triangle mr-1"></i>Importante:</strong>
    <ul class="mb-0 mt-2">
        <li>Solo personal nombrado o con contrato permanente activo puede solicitar vacaciones.</li>
        <li>No se puede solicitar vacaciones en fechas que coincidan con solicitudes aprobadas o pendientes.</li>
        <li>Las solicitudes pendientes pueden ser editadas o canceladas.</li>
    </ul>
</div>
