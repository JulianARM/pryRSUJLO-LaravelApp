<div class="modal fade" id="createColorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::open(['route' => 'colores-vehiculo.store', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-palette text-warning mr-2"></i>Nuevo Color</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre del Color') !!}
                        {!! Form::text('name', old('name'), ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('code', 'Código del Color (RGB)') !!}
                        <div class="input-group">
                            {!! Form::text('code', old('code', '#0056A7'), ['class' => 'form-control js-color-code', 'maxlength' => 7, 'required' => true, 'data-preview' => 'newColorPreview', 'data-picker' => 'newColorPicker']) !!}
                            <div class="input-group-append">
                                <span class="input-group-text rsu-color-picker-addon">
                                    {!! Form::color('picker', old('code', '#0056A7'), ['id' => 'newColorPicker', 'class' => 'rsu-color-picker js-color-picker', 'data-preview' => 'newColorPreview']) !!}
                                </span>
                            </div>
                        </div>
                        <small class="text-muted">Ingresa el codigo hexadecimal o usa el selector de color.</small>
                    </div>
                    <div class="form-group">
                        {!! Form::label('descripcion', 'Descripción') !!}
                        {!! Form::textarea('descripcion', old('descripcion'), ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                    <label>Vista Previa del Color:</label>
                    <div id="newColorPreview" class="rsu-preview" style="background: {{ old('code', '#0056A7') }}">{{ old('code', '#0056A7') }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
