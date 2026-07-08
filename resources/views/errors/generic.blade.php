<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} - {{ $title }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <style>
        body { background: #f3f6fa; color: #1f2937; }
        .rsu-error-page { align-items: center; display: flex; min-height: 100vh; padding: 2rem 1rem; }
        .rsu-error-card { background: #fff; border: 1px solid #dbe4ef; border-radius: .5rem; box-shadow: 0 12px 28px rgba(15, 23, 42, .08); margin: 0 auto; max-width: 620px; padding: 2rem; text-align: center; }
        .rsu-error-icon { align-items: center; background: #e8f3ff; border-radius: 999px; color: #0056a7; display: inline-flex; font-size: 2rem; height: 78px; justify-content: center; margin-bottom: 1rem; width: 78px; }
        .rsu-error-code { color: #64748b; font-size: .85rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .rsu-error-card h1 { color: #0f172a; font-size: 1.55rem; font-weight: 800; margin: .35rem 0 .75rem; }
        .rsu-error-card p { color: #475569; margin-bottom: 1.25rem; }
    </style>
</head>
<body>
    <main class="rsu-error-page">
        <section class="rsu-error-card">
            <div class="rsu-error-icon"><i class="fas fa-info-circle"></i></div>
            <div class="rsu-error-code">Error {{ $status }}</div>
            <h1>{{ $title }}</h1>
            <p>{{ $message }}</p>
            <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}" class="btn btn-primary">
                <i class="fas fa-arrow-left mr-1"></i> Volver
            </a>
        </section>
    </main>
</body>
</html>