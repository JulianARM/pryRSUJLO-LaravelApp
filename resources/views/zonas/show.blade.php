@extends('adminlte::page')

@section('title', 'Perímetro de Zona')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-draw-polygon"></i> Perímetro de la Zona</h1>
        <a href="{{ route('zonas.index') }}" class="btn btn-danger">
            <i class="fas fa-chevron-left mr-1"></i> Retornar
        </a>
    </div>
@stop

@section('content')
    @php
        $coordinates = $zone->coordinates
            ->map(fn ($coordinate) => [
                'id' => $coordinate->id,
                'lat' => (float) $coordinate->latitud,
                'lng' => (float) $coordinate->longitud,
            ])
            ->values();

        $referenceZonas = $zonas->map(fn ($item) => [
            'id' => $item->id,
            'name' => $item->name,
            'coordinates' => $item->coordinates
                ->map(fn ($coordinate) => [
                    'lat' => (float) $coordinate->latitud,
                    'lng' => (float) $coordinate->longitud,
                ])
                ->values(),
        ])->values();
    @endphp

    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="row">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title"><i class="fas fa-map-marker-alt mr-1"></i> Datos de la Zona</h3>
                    </div>
                    <div class="card-body">
                        <dl class="mb-0">
                            <dt>Zona</dt>
                            <dd>{{ $zone->name }}</dd>
                            <dt>Ubicación</dt>
                            <dd>{{ $zone->location_label }}</dd>
                            <dt>Residuos promedio</dt>
                            <dd>{{ number_format((float) $zone->residuos_promedio_kg, 2) }} Kg</dd>
                            <dt>Estado</dt>
                            <dd>
                                @if ($zone->activo)
                                    <span class="badge badge-success">Activo</span>
                                @else
                                    <span class="badge badge-secondary">Inactivo</span>
                                @endif
                            </dd>
                            <dt>Descripción</dt>
                            <dd>{{ $zone->descripcion ?: '-' }}</dd>
                        </dl>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-info">
                        <h3 class="card-title"><i class="fas fa-list-ol mr-1"></i> Coordenadas</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive rsu-zone-coordinates-table">
                            <table class="table table-sm table-striped mb-0">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Latitud</th>
                                        <th>Longitud</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody class="js-zone-coordinates-body"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h3 class="card-title"><i class="fas fa-map mr-1"></i> Mapa de Perímetro</h3>
                    </div>
                    <div class="card-body">
                        {!! Form::open(['route' => ['zonas.coordinates.store', $zone], 'method' => 'POST', 'class' => 'js-ajax-form js-zone-form']) !!}
                            {!! Form::hidden('coordinates', $coordinates->toJson(), ['class' => 'js-zone-coordinates-input']) !!}
                            <div
                                id="zoneMap"
                                class="rsu-zone-map"
                                data-coordinates='@json($coordinates)'
                                data-reference-zonas='@json($referenceZonas)'
                                data-default-lat="-6.767305"
                                data-default-lng="-79.842276"
                            ></div>
                            <div class="border border-info rounded p-3 mt-3">
                                <h2 class="h6 font-weight-bold mb-3">
                                    <i class="fas fa-keyboard mr-1"></i> Agregar coordenada manual
                                </h2>
                                <div class="form-row align-items-end">
                                    <div class="form-group col-md-5">
                                        {!! Form::label('manual_latitud', 'Latitud') !!}
                                        {!! Form::number('manual_latitud', null, [
                                            'class' => 'form-control js-zone-manual-lat',
                                            'placeholder' => 'Ej: -6.767305',
                                            'step' => '0.0000001',
                                            'min' => '-90',
                                            'max' => '90',
                                        ]) !!}
                                        <small class="form-text text-muted">Valores permitidos entre -90 y 90.</small>
                                    </div>
                                    <div class="form-group col-md-5">
                                        {!! Form::label('manual_longitud', 'Longitud') !!}
                                        {!! Form::number('manual_longitud', null, [
                                            'class' => 'form-control js-zone-manual-lng',
                                            'placeholder' => 'Ej: -79.842276',
                                            'step' => '0.0000001',
                                            'min' => '-180',
                                            'max' => '180',
                                        ]) !!}
                                        <small class="form-text text-muted">Valores permitidos entre -180 y 180.</small>
                                    </div>
                                    <div class="form-group col-md-2">
                                        <button type="button" class="btn btn-info btn-block js-zone-add-manual-point">
                                            <i class="fas fa-plus mr-1"></i> Agregar
                                        </button>
                                    </div>
                                </div>
                                <div class="invalid-feedback d-block js-zone-manual-error" style="display: none !important;"></div>
                            </div>
                            <div class="d-flex justify-content-between flex-wrap mt-3">
                                <div class="btn-group mb-2">
                                    <button type="button" class="btn btn-info js-zone-use-location">
                                        <i class="fas fa-location-arrow mr-1"></i> Mi ubicacion
                                    </button>
                                    <button type="button" class="btn btn-warning js-zone-undo-point">
                                        <i class="fas fa-undo mr-1"></i> Deshacer
                                    </button>
                                    <button type="button" class="btn btn-danger js-zone-clear-points">
                                        <i class="fas fa-trash mr-1"></i> Limpiar
                                    </button>
                                </div>
                                <button type="submit" class="btn btn-primary mb-2">
                                    <i class="fas fa-save mr-1"></i> Guardar Perímetro
                                </button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/rsu-zonas.js') }}?v={{ filemtime(public_path('js/rsu-zonas.js')) }}"></script>
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
