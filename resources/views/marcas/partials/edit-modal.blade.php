<div class="modal fade" id="editMarcaModal{{ $brand->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            {!! Form::model($brand, ['route' => ['marcas.update', $brand], 'method' => 'PUT', 'files' => true, 'class' => 'js-ajax-form']) !!}
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-tags text-warning mr-2"></i>Editar Marca</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                {!! Form::label('name', 'Nombre') !!}
                                {!! Form::text('name', null, ['class' => 'form-control', 'required' => true]) !!}
                            </div>
                            <div class="form-group">
                                {!! Form::label('descripcion', 'Descripción') !!}
                                {!! Form::textarea('descripcion', null, ['class' => 'form-control', 'rows' => 4]) !!}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                {!! Form::label('logo', 'Logo') !!}
                                {!! Form::file('logo', ['class' => 'form-control-file', 'accept' => 'image/*']) !!}
                                <small class="text-muted">Sube una imagen solo si deseas reemplazarla.</small>
                            </div>
                            <div class="border rounded bg-light text-center py-4 text-muted">
                                @if ($brand->ruta_logo)
                                    <img src="{{ asset('storage/' . $brand->ruta_logo) }}" alt="{{ $brand->name }}" class="img-fluid" style="max-height: 120px;">
                                @else
                                    <i class="far fa-image fa-4x mb-2"></i>
                                    <div>Sin imagen</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
