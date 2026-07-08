<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión RSU | Nueva contraseña</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
</head>
<body class="hold-transition login-page" style="background: #f2f4f7;">
    <div class="login-box">
        <div class="card">
            <div class="card-body login-card-body">
                @include('partials.alerts')
                {!! Form::open(['url' => route('password.update'), 'method' => 'POST']) !!}
                    {!! Form::hidden('token', $request->route('token')) !!}
                    <div class="form-group">
                        {!! Form::label('email', 'Correo electronico') !!}
                        {!! Form::email('email', old('email', $request->email), ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password', 'Nueva contraseña') !!}
                        {!! Form::password('password', ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('password_confirmation', 'Confirmar contraseña') !!}
                        {!! Form::password('password_confirmation', ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Actualizar contraseña</button>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</body>
</html>
