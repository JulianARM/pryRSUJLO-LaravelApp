@php
    $contract = $contract ?? null;
    $isEditing = $contract !== null;
    $selectedPersonal = old('personal_id', $contract?->personal_id);
    $selectedPersonalLabel = $contract?->employee
        ? $contract->employee->full_name . ' - ' . $contract->employee->dni . ' (' . $contract->employee->staffType->name . ')'
        : null;
    $selectedType = old('tipo_contrato', $contract?->tipo_contrato ?? \App\Models\Contrato::TYPE_PERMANENT);
    $startDate = old('fecha_inicio', $contract?->fecha_inicio?->format('Y-m-d'));
    $endDate = old('fecha_fin', $contract?->fecha_fin?->format('Y-m-d'));
@endphp

<div class="form-group">
    {!! Form::label('personal_id', 'Personal *') !!}
    <select
        name="personal_id"
        id="{{ $isEditing ? 'personal_id_' . $contract->id : 'personal_id' }}"
        class="form-control js-select2 js-employee-select"
        data-url="{{ route('contratos.personal.search') }}"
        data-placeholder="Buscar personal por nombre, apellido o DNI"
        required
    >
        <option value="">Seleccione personal</option>
        @foreach ($personal as $employee)
            <option value="{{ $employee->id }}" @selected((string) $selectedPersonal === (string) $employee->id)>
                {{ $employee->full_name }} - {{ $employee->dni }} ({{ $employee->staffType->name }})
            </option>
        @endforeach
        @if ($selectedPersonal && $selectedPersonalLabel && ! $personal->contains('id', $selectedPersonal))
            <option value="{{ $selectedPersonal }}" selected>{{ $selectedPersonalLabel }}</option>
        @endif
    </select>
    <small class="form-text text-muted">Seleccione de la lista o escriba nombre, apellido o DNI para buscar.</small>
</div>

<div class="form-group">
    {!! Form::label('tipo_contrato', 'Tipo de Contrato *') !!}
    {!! Form::select('tipo_contrato', $contractTypes, $selectedType, ['class' => 'form-control js-contract-type', 'required' => true]) !!}
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('fecha_inicio', 'Fecha de Inicio *') !!}
            {!! Form::input('date', 'fecha_inicio', $startDate, ['class' => 'form-control', 'required' => true]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('fecha_fin', 'Fecha de Finalizacion') !!}
            {!! Form::input('date', 'fecha_fin', $endDate, ['class' => 'form-control js-contract-end-date']) !!}
            <small class="form-text text-muted">Solo aplica para contrato Temporal.</small>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('salario', 'Salario *') !!}
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text">S/</span>
                </div>
                {!! Form::number('salario', old('salario', $contract->salario ?? null), ['class' => 'form-control', 'step' => '0.01', 'min' => 0, 'placeholder' => '0.00', 'required' => true]) !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('meses_periodo_prueba', 'Periodo de Prueba (meses)') !!}
            {!! Form::number('meses_periodo_prueba', old('meses_periodo_prueba', $contract->meses_periodo_prueba ?? 0), ['class' => 'form-control js-contract-trial', 'min' => 0, 'max' => 12]) !!}
            <small class="form-text text-muted">Periodo de prueba para contratos permanentes o nombrados.</small>
        </div>
    </div>
</div>

<div class="form-group mb-0">
    {!! Form::label('activo', 'Contrato Activo *', ['class' => 'd-block']) !!}
    <div class="custom-control custom-switch">
        {!! Form::checkbox('activo', 1, old('activo', $isEditing ? $contract->activo : true), ['class' => 'custom-control-input', 'id' => $isEditing ? 'activo_contract_' . $contract->id : 'activo_contract']) !!}
        {!! Form::label($isEditing ? 'activo_contract_' . $contract->id : 'activo_contract', 'Activo', ['class' => 'custom-control-label text-success font-weight-bold']) !!}
    </div>
</div>
