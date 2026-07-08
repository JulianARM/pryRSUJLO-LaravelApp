<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión RSU | Iniciar sesión</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
    <style>
        body { background: #f2f4f7; }
        .login-box { width: 360px; }
        .login-logo img { max-width: 180px; }
        .login-card-body { border-radius: .35rem; box-shadow: 0 .25rem 1rem rgba(15, 23, 42, .12); }
    </style>
</head>
<body class="hold-transition login-page">
    <div class="login-box">
        <div class="login-logo mb-3">
            <img src="{{ asset('images/municipalidad-jlo.png') }}" alt="Municipalidad Jose Leonardo Ortiz">
        </div>

        <div class="card border-0">
            <div class="card-body login-card-body">
                @include('partials.alerts')

                {!! Form::open(['url' => route('login'), 'method' => 'POST']) !!}
                    <div class="form-group">
                        {!! Form::label('email', 'Correo electronico') !!}
                        {!! Form::email('email', old('email'), ['class' => 'form-control', 'required' => true, 'autofocus' => true]) !!}
                    </div>

                    <div class="form-group">
                        {!! Form::label('password', 'Contraseña') !!}
                        {!! Form::password('password', ['class' => 'form-control', 'required' => true]) !!}
                    </div>

                    <div class="row align-items-center">
                        <div class="col-7">
                            <div class="icheck-primary">
                                {!! Form::checkbox('remember', true, false, ['id' => 'remember']) !!}
                                {!! Form::label('remember', 'Mantener sesión activa', ['class' => 'font-weight-normal']) !!}
                            </div>
                        </div>
                        <div class="col-5">
                            <button type="submit" class="btn btn-primary btn-block">
                                Iniciar sesión
                            </button>
                        </div>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="text-right mt-3">
                            <a href="{{ route('password.request') }}">Olvidé mi contraseña</a>
                        </div>
                    @endif
                {!! Form::close() !!}
            </div>
        </div>
    </div>

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
</body>
</html>
