@php
    $esEdicion = $establecimiento->exists;

    $telefonos = old('telefonos', $establecimiento->telefonos->map(function ($telefono) {
        return [
            'numero'   => $telefono->numero,
            'etiqueta' => $telefono->etiqueta,
        ];
    })->all());

    $horarios = old('horarios', $establecimiento->horarios->map(function ($horario) {
        return [
            'dias'        => $horario->dias,
            'hora_inicio' => substr($horario->hora_inicio, 0, 5),
            'hora_fin'    => substr($horario->hora_fin, 0, 5),
            'nota'        => $horario->nota,
        ];
    })->all());

    $servicios = old('servicios', $establecimiento->servicios->map(function ($servicio) {
        return [
            'nombre'      => $servicio->nombre,
            'categoria'   => $servicio->categoria,
            'descripcion' => $servicio->descripcion,
        ];
    })->all());

    $secciones = old('secciones', $establecimiento->secciones->map(function ($seccion) {
        return [
            'titulo' => $seccion->titulo,
            'texto'  => $seccion->texto,
            'items'  => is_array($seccion->items) ? implode("\n", $seccion->items) : null,
        ];
    })->all());

    $filas = old('cronograma_filas', $establecimiento->cronogramasFilas->map(function ($fila) {
        return [
            'dia_o_fecha'   => $fila->dia_o_fecha,
            'servicio'      => $fila->servicio,
            'profesional'   => $fila->profesional,
            'horario'       => $fila->horario,
            'observaciones' => $fila->observaciones,
        ];
    })->all());

    $diasSemana = [
        1 => 'Lun', 2 => 'Mar', 3 => 'Mié', 4 => 'Jue',
        5 => 'Vie', 6 => 'Sáb', 7 => 'Dom',
    ];
@endphp

<div class="row">
    <div class="col-md-12">
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation" class="active"><a href="#tab-datos" aria-controls="tab-datos" role="tab" data-toggle="tab"><i class="fa fa-info-circle"></i> Datos generales</a></li>
            <li role="presentation"><a href="#tab-contacto" aria-controls="tab-contacto" role="tab" data-toggle="tab"><i class="fa fa-phone"></i> Contacto y horarios</a></li>
            <li role="presentation"><a href="#tab-servicios" aria-controls="tab-servicios" role="tab" data-toggle="tab"><i class="fa fa-stethoscope"></i> Servicios</a></li>
            <li role="presentation"><a href="#tab-cronograma-semanal" aria-controls="tab-cronograma-semanal" role="tab" data-toggle="tab"><i class="fa fa-table"></i> Cronograma semanal por especialidad</a></li>
            <li role="presentation"><a href="#tab-detalle" aria-controls="tab-detalle" role="tab" data-toggle="tab"><i class="fa fa-list-alt"></i> Secciones, campañas y actividades</a></li>
            @if($esEdicion)
                <li role="presentation"><a href="#tab-archivos" aria-controls="tab-archivos" role="tab" data-toggle="tab"><i class="fa fa-calendar"></i> Cronogramas y actividades (Mes)</a></li>
            @endif
        </ul>

        <div class="tab-content" style="padding-top:20px;">
            <div role="tabpanel" class="tab-pane fade in active" id="tab-datos">
                <div class="row">
                    <div class="col-md-8">
                        <div class="form-group">
                            <label for="nombre">Nombre del establecimiento *</label>
                            {!! Form::text('nombre', null, ['class' => 'form-control', 'id' => 'nombre', 'required' => true, 'placeholder' => 'Ej. Hospital de Segundo Nivel "Madre Obrera"']) !!}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="tipo">Tipo *</label>
                                    {!! Form::select('tipo', [
                                        'hospital'       => 'Hospital',
                                        'centro_salud'   => 'Centro de salud',
                                        'rehabilitacion' => 'Centro de rehabilitación',
                                    ], null, ['class' => 'form-control', 'id' => 'tipo', 'required' => true]) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nivel">Nivel / dependencia</label>
                                    {!! Form::text('nivel', null, ['class' => 'form-control', 'id' => 'nivel', 'placeholder' => 'Ej. 2.º nivel, 1.er nivel']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="slug">Identificador para la URL</label>
                                    {!! Form::text('slug', null, ['class' => 'form-control', 'id' => 'slug', 'placeholder' => 'Se genera desde el nombre si se deja vacío']) !!}
                                    <small class="text-muted">Ej. <code>hospital-madre-obrera</code>. No cambia automáticamente si el nombre cambia.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="responsable">Responsable</label>
                                    {!! Form::text('responsable', null, ['class' => 'form-control', 'id' => 'responsable', 'placeholder' => 'Nombre y cargo']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zona">Zona / distrito</label>
                                    {!! Form::text('zona', null, ['class' => 'form-control', 'id' => 'zona']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="direccion">Dirección</label>
                                    {!! Form::text('direccion', null, ['class' => 'form-control', 'id' => 'direccion']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion">Descripción</label>
                            {!! Form::textarea('descripcion', null, ['class' => 'form-control', 'id' => 'descripcion', 'rows' => 4, 'maxlength' => 3000]) !!}
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="latitud">Latitud</label>
                                    {!! Form::text('latitud', null, ['class' => 'form-control', 'id' => 'latitud', 'placeholder' => '-18.4267800']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="longitud">Longitud</label>
                                    {!! Form::text('longitud', null, ['class' => 'form-control', 'id' => 'longitud', 'placeholder' => '-66.5680400']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="atiende_24h" value="0">
                                <input type="checkbox" name="atiende_24h" value="1" {{ old('atiende_24h', $establecimiento->atiende_24h) ? 'checked' : '' }}>
                                Atiende emergencias las 24 horas, todos los días
                            </label>
                        </div>

                        <div class="checkbox">
                            <label>
                                <input type="hidden" name="activo" value="0">
                                <input type="checkbox" name="activo" value="1" {{ old('activo', $establecimiento->exists ? $establecimiento->activo : true) ? 'checked' : '' }}>
                                Visible en el sitio público
                            </label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Fotografía de fachada</label>
                            <input type="hidden" name="foto_fachada_id" id="foto_fachada_id" value="{{ old('foto_fachada_id', $establecimiento->foto_fachada_id) }}">
                            <input type="file" id="foto-input" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif">
                            <small class="text-muted">JPG, PNG, WEBP o GIF. Máximo 10 MB. Se genera miniatura y versión optimizada.</small>
                            <div id="foto-status" class="m-t-8"></div>
                            <div id="foto-preview" class="m-t-10">
                                @if($establecimiento->foto)
                                    <img src="{{ $establecimiento->fotoUrl() }}" alt="Fotografía actual"
                                        style="max-width:100%;border-radius:8px;border:1px solid #e5e7eb;">
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab-contacto">
                <div class="row">
                    <div class="col-md-6">
                        <h4 style="font-size:16px;">Teléfonos</h4>
                        <div id="telefonos-lista">
                            @foreach($telefonos as $i => $telefono)
                                <div class="repeat-row" style="display:flex;gap:8px;margin-bottom:8px;">
                                    <input type="text" name="telefonos[{{ $i }}][numero]" value="{{ $telefono['numero'] ?? '' }}"
                                        class="form-control" placeholder="Número de teléfono">
                                    <select name="telefonos[{{ $i }}][etiqueta]" class="form-control" style="max-width:120px;">
                                        @foreach(['fijo' => 'Fijo', 'celular' => 'Celular', 'whatsapp' => 'WhatsApp'] as $valor => $texto)
                                            <option value="{{ $valor }}" {{ ($telefono['etiqueta'] ?? 'fijo') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                        @endforeach
                                    </select>
                                    <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-default btn-sm add-row" data-target="telefonos-lista" data-template="tpl-telefono">
                            <i class="fa fa-plus"></i> Agregar teléfono
                        </button>
                    </div>

                    <div class="col-md-6">
                        <h4 style="font-size:16px;">Horarios de atención</h4>
                        <div id="horarios-lista">
                            @foreach($horarios as $i => $horario)
                                <div class="repeat-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:10px;">
                                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                                        @foreach($diasSemana as $dia => $nombreDia)
                                            <label class="btn btn-xs btn-default" style="margin:0;">
                                                <input type="checkbox" name="horarios[{{ $i }}][dias][]" value="{{ $dia }}"
                                                    {{ in_array($dia, (array) ($horario['dias'] ?? []), true) ? 'checked' : '' }}>
                                                {{ $nombreDia }}
                                            </label>
                                        @endforeach
                                    </div>
                                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                                        <input type="time" name="horarios[{{ $i }}][hora_inicio]" value="{{ $horario['hora_inicio'] ?? '' }}" class="form-control">
                                        <input type="time" name="horarios[{{ $i }}][hora_fin]" value="{{ $horario['hora_fin'] ?? '' }}" class="form-control">
                                        <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
                                    </div>
                                    <input type="text" name="horarios[{{ $i }}][nota]" value="{{ $horario['nota'] ?? '' }}"
                                        class="form-control" placeholder="Nota (ej. consulta mañana)">
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="btn btn-default btn-sm add-row" data-target="horarios-lista" data-template="tpl-horario">
                            <i class="fa fa-plus"></i> Agregar horario
                        </button>
                    </div>
                </div>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab-servicios">
                <h4 style="font-size:16px;">Servicios y prestaciones</h4>
                <div id="servicios-lista">
                    @foreach($servicios as $i => $servicio)
                        <div class="repeat-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:8px;">
                            <div style="display:flex;gap:8px;margin-bottom:8px;">
                                <input type="text" name="servicios[{{ $i }}][nombre]" value="{{ $servicio['nombre'] ?? '' }}"
                                    class="form-control" placeholder="Nombre del servicio">
                                <select name="servicios[{{ $i }}][categoria]" class="form-control" style="max-width:190px;">
                                    @foreach($categorias as $valor => $texto)
                                        <option value="{{ $valor }}" {{ ($servicio['categoria'] ?? 'otro') === $valor ? 'selected' : '' }}>{{ $texto }}</option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
                            </div>
                            <textarea name="servicios[{{ $i }}][descripcion]" class="form-control" rows="2" placeholder="Detalle opcional: Responsable y prestaciones (una por línea)">{{ $servicio['descripcion'] ?? '' }}</textarea>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-default btn-sm add-row" data-target="servicios-lista" data-template="tpl-servicio">
                    <i class="fa fa-plus"></i> Agregar servicio
                </button>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab-cronograma-semanal">
                <h4 style="font-size:16px;">Cronograma semanal/mensual de atención por especialidad o servicio</h4>
                <p class="text-muted">Registra las filas de atención por especialidad, profesional, días y horarios que se muestran en la tabla pública del establecimiento.</p>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Día o fecha</th>
                                <th>Especialidad / Servicio</th>
                                <th>Profesional responsable</th>
                                <th>Horario</th>
                                <th>Consultorio / Observaciones</th>
                                <th style="width:50px;"></th>
                            </tr>
                        </thead>
                        <tbody id="filas-lista">
                            @foreach($filas as $i => $fila)
                                <tr class="repeat-row">
                                    <td><input type="text" name="cronograma_filas[{{ $i }}][dia_o_fecha]" value="{{ $fila['dia_o_fecha'] ?? '' }}" class="form-control" placeholder="Lunes a viernes"></td>
                                    <td><input type="text" name="cronograma_filas[{{ $i }}][servicio]" value="{{ $fila['servicio'] ?? '' }}" class="form-control" placeholder="Ej. Pediatría / Vacunación"></td>
                                    <td><input type="text" name="cronograma_filas[{{ $i }}][profesional]" value="{{ $fila['profesional'] ?? '' }}" class="form-control" placeholder="Dr. / Lic."></td>
                                    <td><input type="text" name="cronograma_filas[{{ $i }}][horario]" value="{{ $fila['horario'] ?? '' }}" class="form-control" placeholder="08:00 - 12:00"></td>
                                    <td><input type="text" name="cronograma_filas[{{ $i }}][observaciones]" value="{{ $fila['observaciones'] ?? '' }}" class="form-control" placeholder="Consultorio 1"></td>
                                    <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-default btn-sm add-row" data-target="filas-lista" data-template="tpl-fila">
                    <i class="fa fa-plus"></i> Agregar fila de cronograma
                </button>
            </div>

            <div role="tabpanel" class="tab-pane fade" id="tab-detalle">
                <h4 style="font-size:16px;">Secciones, campañas y actividades de la ficha pública</h4>
                <p class="text-muted">Agrega secciones informativas o actividades del centro (requisitos, SUS, calendario de vacunación, campañas, ferias, charlas educativas). Separa cada ítem en una línea.</p>
                <div style="margin-bottom:12px;display:flex;flex-wrap:wrap;gap:6px;">
                    <span class="text-muted" style="font-size:12px;line-height:26px;">Agregar sección rápida:</span>
                    <button type="button" class="btn btn-xs btn-info js-add-preset-seccion"
                        data-titulo="Cronograma de atención por especialidad"
                        data-texto="Horarios y días de atención por especialidad médica:"
                        data-items="Medicina general: lunes a viernes&#10;Odontología: lunes a viernes&#10;Enfermería y vacunación: lunes a viernes">
                        + Cronograma por especialidad
                    </button>
                    <button type="button" class="btn btn-xs btn-info js-add-preset-seccion"
                        data-titulo="Calendario de vacunación"
                        data-texto="Esquema regular del Programa Ampliado de Inmunización (PAI) y campañas de vacunación vigentes:"
                        data-items="BCG y Hepatitis B (recién nacidos)&#10;Pentavalente, Antipolio, Rotavirus y Neumococo (2, 4 y 6 meses)&#10;SRP y Fiebre Amarilla (12 a 23 meses)&#10;VPH (niñas y niños a partir de 10 años)&#10;Influenza estacional y vacunación antirrábica canina/felina según campaña">
                        + Calendario de vacunación
                    </button>
                    <button type="button" class="btn btn-xs btn-info js-add-preset-seccion"
                        data-titulo="Campañas de salud"
                        data-texto="Campañas preventivas y de detección oportuna programadas por el establecimiento:"
                        data-items="Campaña de vacunación (esquema regular PAI, influenza, antirrábica)&#10;Campaña de salud oral y odontología preventiva (fluorización y profilaxis)&#10;Campaña de toma de Papanicolaou (PAP) y prevención de cáncer cérvico-uterino">
                        + Campañas de salud (vacunación, odontología, PAP)
                    </button>
                    <button type="button" class="btn btn-xs btn-info js-add-preset-seccion"
                        data-titulo="Ferias de salud y brigadas en comunidades"
                        data-texto="Atención integral extramural en barrios, distritos, unidades educativas y comunidades:"
                        data-items="Ferias integrales de salud interinstitucionales y distritales&#10;Brigadas móviles de atención médica, odontológica y vacunación en comunidades&#10;Visitas domiciliarias de seguimiento (SAFCI - Mi Salud)">
                        + Ferias y brigadas en comunidades
                    </button>
                    <button type="button" class="btn btn-xs btn-info js-add-preset-seccion"
                        data-titulo="Charlas educativas"
                        data-texto="Sesiones de orientación y promoción de la salud dirigidas a pacientes, familias y unidades educativas:"
                        data-items="Planificación familiar y salud sexual y reproductiva&#10;Prevención de enfermedades transmisibles (tuberculosis, ITS/VIH, infecciones respiratorias y diarreicas)&#10;Prevención de enfermedades no transmisibles (hipertensión, diabetes, nutrición saludable y lactancia materna)">
                        + Charlas educativas
                    </button>
                </div>
                <div id="secciones-lista">
                    @foreach($secciones as $i => $seccion)
                        <div class="repeat-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin-bottom:10px;">
                            <div style="display:flex;gap:8px;margin-bottom:8px;">
                                <input type="text" name="secciones[{{ $i }}][titulo]" value="{{ $seccion['titulo'] ?? '' }}"
                                    class="form-control" placeholder="Título de la sección">
                                <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
                            </div>
                            <textarea name="secciones[{{ $i }}][texto]" class="form-control m-b-8" rows="2" placeholder="Texto introductorio (opcional)">{{ $seccion['texto'] ?? '' }}</textarea>
                            <textarea name="secciones[{{ $i }}][items]" class="form-control" rows="4" placeholder="Un ítem por línea">{{ $seccion['items'] ?? '' }}</textarea>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="btn btn-default btn-sm add-row" data-target="secciones-lista" data-template="tpl-seccion">
                    <i class="fa fa-plus"></i> Agregar sección vacía
                </button>
            </div>

            @if($esEdicion)
                <div role="tabpanel" class="tab-pane fade" id="tab-archivos">
                    <h4 style="font-size:16px;">Cronogramas y actividades publicadas por mes</h4>
                    @if($establecimiento->cronogramasMensuales->isEmpty())
                        <p class="text-muted">Todavía no hay cronogramas o actividades mensuales cargadas.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        <th>Categoría</th>
                                        <th>Servicio</th>
                                        <th>Título</th>
                                        <th>Archivo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($establecimiento->cronogramasMensuales as $cronograma)
                                        <tr>
                                            <td>{{ $cronograma->mes ? $cronograma->mes->format('m/Y') : '—' }}</td>
                                            <td><span class="label label-info">{{ $cronograma->tipo_corto }}</span></td>
                                            <td>{{ $cronograma->servicio ? $cronograma->servicio->nombre : 'General' }}</td>
                                            <td>{{ $cronograma->titulo ?: '—' }}</td>
                                            <td>
                                                @if($cronograma->archivo)
                                                    <a href="{{ $cronograma->archivo->urlPublica() }}" target="_blank" rel="noopener">
                                                        {{ $cronograma->archivo->nombre_original }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">Solo texto</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('salud.cronogramas.edit', $cronograma->id) }}" class="btn btn-xs btn-warning"><i class="fa fa-edit"></i> Editar</a>
                                                {!! Form::open(['route' => ['salud.cronogramas.destroy', $cronograma->id], 'method' => 'DELETE', 'style' => 'display:inline', 'onsubmit' => "return confirm('¿Eliminar este registro?');"]) !!}
                                                    <button class="btn btn-xs btn-danger"><i class="fa fa-trash"></i> Eliminar</button>
                                                {!! Form::close() !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:10px;">
                        <a href="{{ route('salud.cronogramas.create', ['establecimiento_id' => $establecimiento->id, 'tipo' => 'cronograma_atencion']) }}" class="btn btn-info btn-sm">
                            <i class="fa fa-calendar"></i> + Cronograma de atención
                        </a>
                        <a href="{{ route('salud.cronogramas.create', ['establecimiento_id' => $establecimiento->id, 'tipo' => 'calendario_vacunacion']) }}" class="btn btn-success btn-sm">
                            <i class="fa fa-medkit"></i> + Calendario de vacunación
                        </a>
                        <a href="{{ route('salud.cronogramas.create', ['establecimiento_id' => $establecimiento->id, 'tipo' => 'campana_salud']) }}" class="btn btn-primary btn-sm">
                            <i class="fa fa-bullhorn"></i> + Campaña de salud
                        </a>
                        <a href="{{ route('salud.cronogramas.create', ['establecimiento_id' => $establecimiento->id, 'tipo' => 'feria_brigada']) }}" class="btn btn-warning btn-sm">
                            <i class="fa fa-users"></i> + Feria / Brigada
                        </a>
                        <a href="{{ route('salud.cronogramas.create', ['establecimiento_id' => $establecimiento->id, 'tipo' => 'charla_educativa']) }}" class="btn btn-default btn-sm">
                            <i class="fa fa-comments"></i> + Charla educativa
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="ln_solid"></div>
<div class="form-group">
    <div class="col-md-12 text-right">
        <a href="{{ route('salud.index') }}" class="btn btn-primary">Cancelar</a>
        <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> {{ $esEdicion ? 'Actualizar establecimiento' : 'Guardar establecimiento' }}
        </button>
    </div>
</div>

<div id="tpl-telefono" style="display:none;">
    <div class="repeat-row" style="display:flex;gap:8px;margin-bottom:8px;">
        <input type="text" name="telefonos[__IDX__][numero]" class="form-control" placeholder="Número de teléfono">
        <select name="telefonos[__IDX__][etiqueta]" class="form-control" style="max-width:120px;">
            <option value="fijo">Fijo</option>
            <option value="celular">Celular</option>
            <option value="whatsapp">WhatsApp</option>
        </select>
        <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
    </div>
</div>

<div id="tpl-horario" style="display:none;">
    <div class="repeat-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:10px;">
        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
            @foreach($diasSemana as $dia => $nombreDia)
                <label class="btn btn-xs btn-default" style="margin:0;">
                    <input type="checkbox" name="horarios[__IDX__][dias][]" value="{{ $dia }}"> {{ $nombreDia }}
                </label>
            @endforeach
        </div>
        <div style="display:flex;gap:8px;margin-bottom:8px;">
            <input type="time" name="horarios[__IDX__][hora_inicio]" class="form-control">
            <input type="time" name="horarios[__IDX__][hora_fin]" class="form-control">
            <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
        </div>
        <input type="text" name="horarios[__IDX__][nota]" class="form-control" placeholder="Nota (ej. consulta mañana)">
    </div>
</div>

<div id="tpl-servicio" style="display:none;">
    <div class="repeat-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;margin-bottom:8px;">
        <div style="display:flex;gap:8px;margin-bottom:8px;">
            <input type="text" name="servicios[__IDX__][nombre]" class="form-control" placeholder="Nombre del servicio">
            <select name="servicios[__IDX__][categoria]" class="form-control" style="max-width:190px;">
                @foreach($categorias as $valor => $texto)
                    <option value="{{ $valor }}">{{ $texto }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
        </div>
        <textarea name="servicios[__IDX__][descripcion]" class="form-control" rows="2" placeholder="Detalle opcional: Responsable y prestaciones (una por línea)"></textarea>
    </div>
</div>

<div id="tpl-seccion" style="display:none;">
    <div class="repeat-row" style="border:1px solid #e5e7eb;border-radius:8px;padding:12px;margin-bottom:10px;">
        <div style="display:flex;gap:8px;margin-bottom:8px;">
            <input type="text" name="secciones[__IDX__][titulo]" class="form-control" placeholder="Título de la sección">
            <button type="button" class="btn btn-danger remove-row" title="Quitar"><i class="fa fa-times"></i></button>
        </div>
        <textarea name="secciones[__IDX__][texto]" class="form-control m-b-8" rows="2" placeholder="Texto introductorio (opcional)"></textarea>
        <textarea name="secciones[__IDX__][items]" class="form-control" rows="4" placeholder="Un ítem por línea"></textarea>
    </div>
</div>

<div id="tpl-fila" style="display:none;">
    <tr class="repeat-row">
        <td><input type="text" name="cronograma_filas[__IDX__][dia_o_fecha]" class="form-control" placeholder="Lunes a viernes"></td>
        <td><input type="text" name="cronograma_filas[__IDX__][servicio]" class="form-control"></td>
        <td><input type="text" name="cronograma_filas[__IDX__][profesional]" class="form-control"></td>
        <td><input type="text" name="cronograma_filas[__IDX__][horario]" class="form-control" placeholder="08:00 - 12:00"></td>
        <td><input type="text" name="cronograma_filas[__IDX__][observaciones]" class="form-control"></td>
        <td><button type="button" class="btn btn-danger btn-sm remove-row"><i class="fa fa-times"></i></button></td>
    </tr>
</div>

@section('script')
<script>
    (function () {
        var seq = 0;
        function nextIndex() {
            seq += 1;
            return 'n' + Date.now() + seq;
        }

        $('.add-row').on('click', function () {
            var target = $(this).data('target');
            var template = $('#' + $(this).data('template')).html();
            var html = template.replace(/__IDX__/g, nextIndex());
            $('#' + target).append(html);
        });

        $('.js-add-preset-seccion').on('click', function () {
            var idx = nextIndex();
            var template = $('#tpl-seccion').html().replace(/__IDX__/g, idx);
            var $row = $(template);
            $row.find('input[name="secciones[' + idx + '][titulo]"]').val($(this).data('titulo') || '');
            $row.find('textarea[name="secciones[' + idx + '][texto]"]').val($(this).data('texto') || '');
            $row.find('textarea[name="secciones[' + idx + '][items]"]').val($(this).data('items') || '');
            $('#secciones-lista').append($row);
        });

        $(document).on('click', '.remove-row', function () {
            $(this).closest('.repeat-row').remove();
        });

        var fotoInput = document.getElementById('foto-input');
        if (fotoInput) {
            fotoInput.addEventListener('change', function () {
                if (!this.files || !this.files[0]) {
                    return;
                }

                var archivo = this.files[0];
                var status = document.getElementById('foto-status');
                var data = new FormData();
                data.append('archivo', archivo);
                status.innerHTML = '<span class="text-muted"><i class="fa fa-spinner fa-spin"></i> Subiendo fotografía...</span>';

                fetch('{{ route('salud.archivos.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: data
                }).then(function (respuesta) {
                    return respuesta.json().then(function (json) {
                        if (!respuesta.ok) {
                            throw json;
                        }
                        return json;
                    });
                }).then(function (json) {
                    document.getElementById('foto_fachada_id').value = json.id;
                    var preview = document.getElementById('foto-preview');
                    var url = json.thumb || json.url;
                    preview.innerHTML = '<img src="' + url + '" alt="Fotografía subida" style="max-width:100%;border-radius:8px;border:1px solid #e5e7eb;">';
                    status.innerHTML = '<span class="text-success"><i class="fa fa-check"></i> Fotografía subida correctamente.</span>';
                }).catch(function (error) {
                    var mensaje = 'No se pudo subir la fotografía.';
                    if (error && error.errors && error.errors.archivo) {
                        mensaje = error.errors.archivo[0];
                    } else if (error && error.message) {
                        mensaje = error.message;
                    }
                    status.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> ' + mensaje + '</span>';
                });
            });
        }
    })();
</script>
@endsection
