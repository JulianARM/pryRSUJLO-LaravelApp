<div class="modal fade" id="vehicleImagesModal{{ $vehicle->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-images text-warning mr-2"></i>Gestión de Imágenes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <section class="bg-info text-white rounded p-3 mb-3">
                    <h4 class="mb-2">{{ $vehicle->name }}</h4>
                    <span class="badge badge-light mr-2"><i class="fas fa-barcode mr-1"></i>{{ $vehicle->code }}</span>
                    <span class="badge badge-light mr-2"><i class="fas fa-tag mr-1"></i>{{ $vehicle->placa }}</span>
                    <span class="badge badge-light"><i class="fas fa-calendar mr-1"></i>{{ $vehicle->anio }}</span>
                </section>

                {!! Form::open(['route' => ['vehiculos.images.store', $vehicle], 'files' => true, 'class' => 'js-ajax-form']) !!}
                    <h5 class="bg-navy text-white p-3 rounded"><i class="fas fa-cloud-upload-alt mr-2"></i>Agregar Nuevas Imágenes</h5>
                    <label class="rsu-image-dropzone w-100">
                        <i class="fas fa-images"></i>
                        <strong>Arrastra y suelta imágenes aquí</strong>
                        <span class="text-muted">o haz clic para seleccionar archivos</span>
                        <span class="mt-3">
                            <span class="badge badge-info p-2">Formatos: JPEG, PNG, JPG, GIF</span>
                            <span class="badge badge-warning p-2">Max. 2MB por imagen</span>
                        </span>
                        {!! Form::file('images[]', ['class' => 'd-none js-image-input', 'multiple' => true, 'accept' => 'image/*', 'data-preview' => '#vehicleUploadPreview' . $vehicle->id]) !!}
                    </label>
                    <div id="vehicleUploadPreview{{ $vehicle->id }}" class="rsu-upload-preview"></div>
                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar Imágenes</button>
                    </div>
                {!! Form::close() !!}

                <hr>

                <h5 class="bg-info text-white p-3 rounded">
                    <i class="fas fa-photo-video mr-2"></i>Imágenes Existentes
                    <span class="badge badge-light ml-2">{{ $vehicle->images->count() }}</span>
                </h5>
                <p class="text-muted mb-3"><i class="fas fa-crown text-warning"></i> Click en corona = Principal &nbsp; <i class="fas fa-trash text-danger"></i> Eliminar</p>

                <div class="rsu-existing-images">
                    @forelse ($vehicle->images as $image)
                        <div class="rsu-image-card {{ $image->es_principal ? 'is-profile' : '' }}">
                            <img src="{{ asset('storage/' . $image->path) }}" alt="{{ $image->nombre_original }}">
                            <div class="rsu-image-actions">
                                {!! Form::open(['route' => ['vehiculos.images.profile', $vehicle, $image], 'method' => 'PUT', 'class' => 'js-ajax-form']) !!}
                                    <button class="btn btn-warning btn-sm" title="Marcar como principal" @disabled($image->es_principal)>
                                        <i class="fas fa-crown"></i>
                                    </button>
                                {!! Form::close() !!}
                                {!! Form::open(['route' => ['vehiculos.images.destroy', $vehicle, $image], 'method' => 'DELETE', 'class' => 'js-ajax-form', 'data-confirm' => '¿Deseas eliminar esta imagen?']) !!}
                                    <button class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    @empty
                        <div class="text-muted">Este vehículo todavía no tiene imágenes registradas.</div>
                    @endforelse
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
            </div>
        </div>
    </div>
</div>

