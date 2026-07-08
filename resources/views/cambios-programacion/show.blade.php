@extends('adminlte::page')

@section('title', 'Detalle de Cambio')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-eye"></i> Detalles de Cambio</h1>
        <a href="{{ route('cambios-programacion.index') }}" class="btn btn-outline-primary"><i class="fas fa-arrow-left mr-1"></i> Volver</a>
    </div>
@stop

@section('content')
    <div class="card">
        <div class="card-body">
            <div class="rsu-change-detail-header">
                <div><span>Tipo</span><strong>{{ $changeTypes[$change->tipo_cambio] ?? $change->action }}</strong></div>
                <div><span>Usuario</span><strong>{{ $change->user?->name ?? '-' }}</strong></div>
                <div><span>Motivo</span><strong>{{ $change->reason?->name ?? $change->descripcion }}</strong></div>
                <div><span>Programación</span><strong>#{{ $change->programacion_id }}</strong></div>
                <div><span>Fecha de cambio</span><strong>{{ $change->created_at->format('d/m/Y H:i') }}</strong></div>
                <div><span>Fecha programación</span><strong>{{ $change->fecha_programacion_afectada?->format('d/m/Y') ?? '-' }}</strong></div>
            </div>

            <div class="rsu-change-detail-note">
                <i class="fas fa-comment-alt"></i>
                <div>
                    <span>Detalle</span>
                    <strong>{{ $change->detail ?: '-' }}</strong>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="rsu-change-panel">
                        <h6><i class="fas fa-history"></i> Antes del cambio</h6>
                        @include('cambios-programacion.partials.valores-programacion', ['values' => $change->valores_anteriores ?? []])
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="rsu-change-panel is-new">
                        <h6><i class="fas fa-check-circle"></i> Después del cambio</h6>
                        @include('cambios-programacion.partials.valores-programacion', ['values' => $change->valores_nuevos ?? []])
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop