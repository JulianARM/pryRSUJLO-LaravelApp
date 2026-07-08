@php
    $zoneOptions = ['all' => 'Todas las zonas'] + $zoneMapItems->pluck('name', 'id')->all();
@endphp

<div class="modal fade" id="zoneMapPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary">
                <h5 class="modal-title"><i class="fas fa-map mr-1"></i> Mapa de la Zona</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-4 mb-3 mb-lg-0">
                        <div class="rsu-zone-preview-panel">
                            <div class="rsu-zone-preview-hero">
                                <div class="rsu-zone-preview-icon">
                                    <i class="fas fa-map-marked-alt"></i>
                                </div>
                                <div>
                                    <h2 class="js-zone-overview-name">Zona</h2>
                                    <p class="mb-0"><i class="fas fa-map-marker-alt mr-1"></i><span class="js-zone-overview-location">-</span></p>
                                </div>
                            </div>

                            <div class="rsu-zone-preview-stats">
                                <div class="rsu-zone-preview-stat bg-primary">
                                    <span><i class="fas fa-map-pin mr-1"></i> Puntos</span>
                                    <strong class="js-zone-overview-points">0</strong>
                                </div>
                                <div class="rsu-zone-preview-stat bg-success">
                                    <span><i class="fas fa-recycle mr-1"></i> Residuos</span>
                                    <strong class="js-zone-overview-waste">N/A</strong>
                                </div>
                                <div class="rsu-zone-preview-stat bg-warning text-dark">
                                    <span><i class="fas fa-landmark mr-1"></i> Departamento</span>
                                    <strong class="js-zone-overview-departamento">-</strong>
                                </div>
                                <div class="rsu-zone-preview-stat bg-info">
                                    <span><i class="fas fa-expand-arrows-alt mr-1"></i> Area</span>
                                    <strong class="js-zone-overview-area">N/A</strong>
                                </div>
                                <div class="rsu-zone-preview-stat bg-success">
                                    <span><i class="fas fa-check-circle mr-1"></i> Zonas Activas</span>
                                    <strong class="js-zone-overview-active-zonas">0</strong>
                                </div>
                            </div>

                            <div class="form-group mt-3">
                                {!! Form::label('zone_overview_select', 'Zona a visualizar') !!}
                                {!! Form::select('zone_overview_select', $zoneOptions, 'all', [
                                    'class' => 'custom-select js-zone-overview-select',
                                ]) !!}
                            </div>

                            <div class="border-top pt-3 mt-3">
                                <h3 class="h6 text-uppercase text-muted mb-2">
                                    <i class="fas fa-align-left mr-1"></i> Descripción de la zona
                                </h3>
                                <p class="js-zone-overview-descripcion mb-0">-</p>
                            </div>

                            <div class="border-top pt-3 mt-3">
                                <h3 class="h6 text-uppercase text-muted mb-2">
                                    <i class="fas fa-check-circle mr-1"></i> ZONAS ACTIVAS
                                </h3>
                                <div class="rsu-zone-active-list js-zone-overview-active-list"></div>
                            </div>

                            <div class="border-top pt-3 mt-3">
                                <h3 class="h6 text-uppercase text-muted mb-2">
                                    <i class="fas fa-list-ol mr-1"></i> Coordenadas del poligono
                                </h3>
                                <div class="table-responsive rsu-zone-overview-table">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Latitud</th>
                                                <th>Longitud</th>
                                            </tr>
                                        </thead>
                                        <tbody class="js-zone-overview-coordinates"></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-8">
                        <h3 class="h6 text-uppercase text-muted mb-2">
                            <i class="fas fa-map mr-1"></i> Visualizacion en mapa
                        </h3>
                        <div class="rsu-zone-map-context mb-2">
                            <span><i class="fas fa-map-marker-alt mr-1"></i> Ubicación del mapa</span>
                            <strong class="js-zone-overview-map-location">José Leonardo Ortiz, Chiclayo, Lambayeque</strong>
                        </div>
                        <div class="rsu-zone-map-legend js-zone-map-legend mb-2"></div>
                        <div
                            class="rsu-zone-overview-map js-zone-overview-map"
                            data-zonas='@json($zoneMapItems)'
                            data-default-lat="-6.767305"
                            data-default-lng="-79.842276"
                        ></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
