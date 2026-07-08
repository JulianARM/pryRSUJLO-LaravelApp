<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión RSU | Recuperar contraseña</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
</head>
<body class="hold-transition login-page" style="background: #f2f4f7;">
    <div class="login-box">
        <div class="login-logo mb-3">
            <img src="{{ asset('images/municipalidad-jlo.png') }}" alt="Municipalidad Jose Leonardo Ortiz" style="max-width: 180px;">
        </div>
        <div class="card">
            <div class="card-body login-card-body">
                @include('partials.alerts')
                <p class="text-muted">Ingresa tu correo para recibir el enlace de recuperacion.</p>
                {!! Form::open(['url' => route('password.email'), 'method' => 'POST']) !!}
                    <div class="form-group">
                        {!! Form::label('email', 'Correo electronico') !!}
                        {!! Form::email('email', old('email'), ['class' => 'form-control', 'required' => true, 'autofocus' => true]) !!}
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Enviar enlace</button>
                {!! Form::close() !!}
                <div class="text-center mt-3">
                    <a href="{{ route('login') }}">Volver al inicio de sesión</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
