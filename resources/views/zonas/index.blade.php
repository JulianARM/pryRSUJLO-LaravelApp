@extends('adminlte::page')

@section('title', 'Zonas')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/rsu.css') }}?v={{ filemtime(public_path('css/rsu.css')) }}">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="rsu-page-title"><i class="fas fa-map-marker-alt"></i> Listado de Zonas</h1>
        <div class="btn-group">
            <button class="btn btn-success" data-toggle="modal" data-target="#zoneMapPreviewModal" data-zone-preview-mode="all">
                <i class="fas fa-map mr-1"></i> Mapa de Zonas
            </button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createZonaModal">
                <i class="fas fa-plus mr-1"></i> Nueva Zona
            </button>
        </div>
    </div>
@stop

@section('content')
    @php
        $zoneMapItems = $mapZonas->map(fn ($zone) => [
            'id' => $zone->id,
            'name' => $zone->name,
            'departamento' => $zone->departamento,
            'provincia' => $zone->provincia,
            'distrito' => $zone->distrito,
            'location' => $zone->location_label,
            'descripcion' => $zone->descripcion,
            'residuos_promedio_kg' => (float) $zone->residuos_promedio_kg,
            'activo' => $zone->activo,
            'coordinates' => $zone->coordinates
                ->map(fn ($coordinate) => [
                    'lat' => (float) $coordinate->latitud,
                    'lng' => (float) $coordinate->longitud,
                ])
                ->values(),
        ])->values();
    @endphp

    <div class="js-crud-container">
        <div class="js-flash-messages">@include('partials.alerts')</div>

        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between flex-wrap mb-3">
                    {!! Form::open(['route' => 'zonas.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        <span class="mr-2">Mostrar</span>
                        {!! Form::select('per_page', [10 => 10, 25 => 25, 50 => 50], request('per_page', 10), ['class' => 'custom-select custom-select-sm', 'onchange' => 'this.form.submit()']) !!}
                        <span class="ml-2">registros</span>
                        {!! Form::hidden('q', request('q')) !!}
                    {!! Form::close() !!}

                    {!! Form::open(['route' => 'zonas.index', 'method' => 'GET', 'class' => 'form-inline mb-2']) !!}
                        {!! Form::hidden('per_page', request('per_page', 10)) !!}
                        {!! Form::label('q', 'Buscar:', ['class' => 'mr-2']) !!}
                        {!! Form::text('q', request('q'), ['class' => 'form-control form-control-sm']) !!}
                    {!! Form::close() !!}
                </div>

                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Zona</th>
                                <th>Ubicación</th>
                                <th>Residuos Prom. (Kg)</th>
                                <th>Estado</th>
                                <th>Coordenadas</th>
                                <th>Creación</th>
                                <th>Actualización</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($zonas as $zone)
                                <tr>
                                    <td class="font-weight-bold">{{ $zone->name }}</td>
                                    <td>{{ $zone->location_label }}</td>
                                    <td><span class="badge badge-info">{{ number_format((float) $zone->residuos_promedio_kg, 2) }}</span></td>
                                    <td>
                                        @if ($zone->activo)
                                            <span class="badge badge-success">Activo</span>
                                        @else
                                            <span class="badge badge-secondary">Inactivo</span>
                                        @endif
                                    </td>
                                    <td><span class="badge badge-primary">{{ $zone->coordinates_count }}</span></td>
                                    <td>{{ $zone->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $zone->updated_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-center text-nowrap">
                                        <button class="btn btn-info btn-sm" data-toggle="modal" data-target="#zoneMapPreviewModal" data-zone-preview-id="{{ $zone->id }}" title="Visualizar mapa">
                                            <i class="fas fa-map"></i>
                                        </button>
                                        <a href="{{ route('zonas.show', $zone) }}" class="btn btn-success btn-sm" title="Perímetro">
                                            <i class="fas fa-draw-polygon"></i>
                                        </a>
                                        <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editZonaModal{{ $zone->id }}" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        {!! Form::open(['route' => ['zonas.destroy', $zone], 'method' => 'DELETE', 'class' => 'd-inline js-ajax-form', 'data-confirm' => 'Deseas eliminar esta zona?']) !!}
                                            <button class="btn btn-danger btn-sm" title="Eliminar"><i class="fas fa-trash"></i></button>
                                        {!! Form::close() !!}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No hay zonas registradas.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <span>Mostrando {{ $zonas->firstItem() ?? 0 }} a {{ $zonas->lastItem() ?? 0 }} de {{ $zonas->total() }} registros</span>
                    {{ $zonas->links() }}
                </div>
            </div>
        </div>

        @foreach ($zonas as $zone)
            @include('zonas.partials.edit-modal', ['zone' => $zone])
        @endforeach
        @include('zonas.partials.map-modal', ['zoneMapItems' => $zoneMapItems])
        @include('zonas.partials.create-modal')
    </div>
@stop

@section('js')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="{{ asset('js/rsu-zonas.js') }}?v={{ filemtime(public_path('js/rsu-zonas.js')) }}"></script>
    <script src="{{ asset('js/rsu-crud.js') }}?v={{ filemtime(public_path('js/rsu-crud.js')) }}"></script>
@stop
