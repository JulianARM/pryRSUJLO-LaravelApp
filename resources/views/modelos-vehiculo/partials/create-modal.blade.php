<div class="modal fade" id="createModeloVehiculoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::open(['route' => 'modelos-vehiculo.store', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-wrench text-warning mr-2"></i>Nuevo Modelo</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre del Modelo *') !!}
                        {!! Form::text('name', old('name'), ['class' => 'form-control', 'placeholder' => 'Ej: Atego, Axor', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('code', 'Código del Modelo *') !!}
                        {!! Form::text('code', old('code'), ['class' => 'form-control', 'placeholder' => 'Ej: ATEGO-HYU', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('marca_id', 'Marca *') !!}
                        {!! Form::select('marca_id', $marcas, old('marca_id'), ['class' => 'form-control', 'placeholder' => 'Seleccione una marca', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('descripcion', 'Descripción') !!}
                        {!! Form::textarea('descripcion', old('descripcion'), ['class' => 'form-control', 'rows' => 3]) !!}
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
