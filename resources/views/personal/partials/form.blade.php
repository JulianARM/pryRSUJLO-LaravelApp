@php
    $employee = $employee ?? null;
    $isEditing = $employee !== null;
    $previewId = $isEditing ? 'employee-photo-preview-' . $employee->id : 'employee-photo-preview';
    $passwordAttributes = ['class' => 'form-control', 'placeholder' => 'Minimo 6 caracteres'];
    $birthDate = old('fecha_nacimiento', $employee?->fecha_nacimiento?->format('Y-m-d'));

    if (! $isEditing) {
        $passwordAttributes['required'] = true;
    }
@endphp

<div class="row">
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('dni', 'DNI *') !!}
                    {!! Form::text('dni', old('dni', $employee->dni ?? null), ['class' => 'form-control', 'maxlength' => 8, 'placeholder' => '12345678', 'required' => true]) !!}
                    <small class="form-text text-muted">8 digitos unicos.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('tipo_personal_id', 'Tipo de Personal *') !!}
                    {!! Form::select('tipo_personal_id', $staffTypes, old('tipo_personal_id', $employee->tipo_personal_id ?? null), ['class' => 'form-control', 'placeholder' => 'Seleccione un tipo', 'required' => true]) !!}
                    <small class="form-text text-muted">La licencia es obligatoria para Conductores.</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('nombres', 'Nombres *') !!}
                    {!! Form::text('nombres', old('nombres', $employee->nombres ?? null), ['class' => 'form-control', 'placeholder' => 'Ingrese los nombres', 'required' => true]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('apellidos', 'Apellidos *') !!}
                    {!! Form::text('apellidos', old('apellidos', $employee->apellidos ?? null), ['class' => 'form-control', 'placeholder' => 'Ingrese los apellidos', 'required' => true]) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('fecha_nacimiento', 'Fecha de Nacimiento *') !!}
                    {!! Form::input('date', 'fecha_nacimiento', $birthDate, ['class' => 'form-control', 'required' => true]) !!}
                    <small class="form-text text-muted">Mayor de 18 anios.</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('telefono', 'Telefono') !!}
                    {!! Form::text('telefono', old('telefono', $employee->telefono ?? null), ['class' => 'form-control', 'placeholder' => '987654321']) !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('email', 'Email *') !!}
                    {!! Form::email('email', old('email', $employee->email ?? null), ['class' => 'form-control', 'placeholder' => 'personal@ejemplo.com', 'required' => true]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('licencia', 'Licencia de Conducir') !!}
                    {!! Form::text('licencia', old('licencia', $employee->licencia ?? null), ['class' => 'form-control', 'placeholder' => 'Ej: Q12345678']) !!}
                    <small class="form-text text-muted">Requerida cuando el tipo sea Conductor.</small>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('activo', 'Estado *') !!}
                    {!! Form::select('activo', [1 => 'Activo', 0 => 'Inactivo'], old('activo', $isEditing ? (int) $employee->activo : 1), ['class' => 'form-control', 'required' => true]) !!}
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('password', $isEditing ? 'Nueva Contraseña' : 'Contraseña *') !!}
                    {!! Form::password('password', $passwordAttributes) !!}
                    <small class="form-text text-muted">
                        {{ $isEditing ? 'Dejar en blanco para mantener la contrasenia actual.' : 'Minimo 6 caracteres.' }}
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group">
            {!! Form::label('direccion', 'Direccion *') !!}
            {!! Form::text('direccion', old('direccion', $employee->direccion ?? null), ['class' => 'form-control', 'placeholder' => 'Av. Principal 123, Distrito, Ciudad', 'required' => true]) !!}
            <small class="form-text text-muted">Direccion completa, minimo 10 caracteres.</small>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="form-group">
            {!! Form::label('photo', 'Foto de Perfil') !!}
            <label class="rsu-image-dropzone" for="{{ $isEditing ? 'employee-photo-' . $employee->id : 'employee-photo' }}">
                <div id="{{ $previewId }}" class="rsu-upload-preview w-100">
                    @if ($isEditing && $employee->ruta_foto)
                        <img class="rsu-upload-preview-img" src="{{ asset('storage/' . $employee->ruta_foto) }}" alt="{{ $employee->full_name }}">
                    @else
                        <i class="far fa-image text-muted"></i>
                    @endif
                </div>
                <small class="text-muted">Haga click para seleccionar una imagen.</small>
            </label>
            {!! Form::file('photo', ['id' => $isEditing ? 'employee-photo-' . $employee->id : 'employee-photo', 'class' => 'd-none js-photo-input', 'accept' => 'image/*', 'data-preview' => '#' . $previewId]) !!}
        </div>
    </div>
</div>
