<div class="modal fade" id="editColorModal{{ $color->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            {!! Form::model($color, ['route' => ['colores-vehiculo.update', $color], 'method' => 'PUT', 'class' => 'js-ajax-form']) !!}
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-palette text-warning mr-2"></i>Editar Color</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        {!! Form::label('name', 'Nombre del Color') !!}
                        {!! Form::text('name', null, ['class' => 'form-control', 'required' => true]) !!}
                    </div>
                    <div class="form-group">
                        {!! Form::label('code', 'Código del Color (RGB)') !!}
                        <div class="input-group">
                            {!! Form::text('code', null, ['class' => 'form-control js-color-code', 'maxlength' => 7, 'required' => true, 'data-preview' => 'editColorPreview' . $color->id, 'data-picker' => 'editColorPicker' . $color->id]) !!}
                            <div class="input-group-append">
                                <span class="input-group-text rsu-color-picker-addon">
                                    {!! Form::color('picker', $color->code, ['id' => 'editColorPicker' . $color->id, 'class' => 'rsu-color-picker js-color-picker', 'data-preview' => 'editColorPreview' . $color->id]) !!}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! Form::label('descripcion', 'Descripción') !!}
                        {!! Form::textarea('descripcion', null, ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                    <label>Vista Previa del Color:</label>
                    <div id="editColorPreview{{ $color->id }}" class="rsu-preview" style="background: {{ $color->code }}">{{ $color->code }}</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal"><i class="fas fa-times mr-1"></i> Cancelar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar</button>
                </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>
