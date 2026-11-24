@php
    $establecimientoSeleccionado = old('establecimiento_id', $cronograma->establecimiento_id);
    $tipoSeleccionado = old('tipo', $cronograma->tipo ?: request('tipo', 'cronograma_atencion'));
    $tiposDisponibles = \App\Salud\SaludCronogramaMensual::TIPOS;
    $serviciosData = $establecimientos->mapWithKeys(function ($establecimiento) {
        return [$establecimiento->id => $establecimiento->servicios->map(function ($servicio) {
            return ['id' => $servicio->id, 'nombre' => $servicio->nombre];
        })->values()];
    });
    $archivoActual = $cronograma->archivo;
@endphp

<div class="row">
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="establecimiento_id">Establecimiento *</label>
                    <select name="establecimiento_id" id="establecimiento_id" class="form-control" required>
                        <option value="">Selecciona un establecimiento</option>
                        @foreach($establecimientos as $establecimiento)
                            <option value="{{ $establecimiento->id }}" {{ (string) $establecimientoSeleccionado === (string) $establecimiento->id ? 'selected' : '' }}>
                                {{ $establecimiento->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="tipo">Categoría (Cronograma o Actividad) *</label>
                    <select name="tipo" id="tipo" class="form-control" required>
                        @foreach($tiposDisponibles as $clave => $etiqueta)
                            <option value="{{ $clave }}" {{ (string) $tipoSeleccionado === (string) $clave ? 'selected' : '' }}>
                                {{ $etiqueta }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="servicio_id">Servicio o especialidad</label>
                    <select name="servicio_id" id="servicio_id" class="form-control">
                        <option value="">General / todos los servicios</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="mes">Mes / periodo *</label>
                    <input type="month" name="mes" id="mes" class="form-control" required
                        value="{{ old('mes', $cronograma->mes ? $cronograma->mes->format('Y-m') : now()->format('Y-m')) }}">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="fecha_fin">Fecha fin de la campaña / actividad</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                        value="{{ old('fecha_fin', $cronograma->fecha_fin ? $cronograma->fecha_fin->format('Y-m-d') : '') }}">
                    <small class="text-muted">Las campañas, ferias y actividades importantes (excepto roles/cronogramas de atención) se muestran en la portada principal hasta este día.</small>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="titulo">Título del cronograma o actividad</label>
            <input type="text" name="titulo" id="titulo" class="form-control" maxlength="255"
                value="{{ old('titulo', $cronograma->titulo) }}" placeholder="Ej. Campaña de vacunación antirrábica / Cronograma mensual por especialidad">
            <div style="margin-top:6px;display:flex;flex-wrap:wrap;gap:6px;">
                <span class="text-muted" style="font-size:12px;line-height:24px;">Sugerencias rápidas:</span>
                <button type="button" class="btn btn-xs btn-default js-preset-titulo" data-tipo="cronograma_atencion" data-titulo="Cronograma mensual de atención por especialidad">Atención por especialidad</button>
                <button type="button" class="btn btn-xs btn-default js-preset-titulo" data-tipo="calendario_vacunacion" data-titulo="Calendario de vacunación PAI">Calendario de vacunación</button>
                <button type="button" class="btn btn-xs btn-default js-preset-titulo" data-tipo="campana_salud" data-titulo="Campaña de vacunación">Campaña de vacunación</button>
                <button type="button" class="btn btn-xs btn-default js-preset-titulo" data-tipo="campana_salud" data-titulo="Campaña de odontología">Campaña de odontología</button>
                <button type="button" class="btn btn-xs btn-default js-preset-titulo" data-tipo="campana_salud" data-titulo="Campaña de toma de Papanicolaou">Campaña de Papanicolaou</button>
                <button type="button" class="btn btn-xs btn-default js-preset-titulo" data-tipo="feria_brigada" data-titulo="Feria de salud y brigada médica en comunidad">Feria / Brigada en comunidad</button>
                <button type="button" class="btn btn-xs btn-default js-preset-titulo" data-tipo="charla_educativa" data-titulo="Charla educativa: Planificación familiar y prevención de enfermedades">Charla educativa</button>
            </div>
        </div>

        <div class="form-group">
            <label for="nota">Detalle, fechas, lugar, requisitos u observaciones</label>
            <textarea name="nota" id="nota" rows="4" class="form-control" maxlength="1500" placeholder="Ej. Fechas de la campaña, horarios, comunidad o unidad educativa donde se realizará, población objetivo y requisitos.">{{ old('nota', $cronograma->nota) }}</textarea>
        </div>

        <input type="hidden" name="publicado" value="1">
    </div>

    <div class="col-md-4">
        <div class="form-group">
            <label for="archivo">Afiche, imagen o archivo PDF (opcional si llenas el detalle)</label>
            <input type="hidden" name="archivo_id" id="archivo_id" value="{{ old('archivo_id', $cronograma->archivo_id) }}">
            <input type="file" name="archivo" id="archivo" class="form-control" accept="image/jpeg,image/png,image/webp,image/gif,application/pdf">
            <small class="text-muted">JPG, PNG, WEBP, GIF o PDF. Máximo 10 MB. Puedes subir el rol de turnos, calendario o afiche de campaña.</small>

            @if($archivoActual)
                <div class="m-t-10">
                    <span class="label label-info">Archivo actual</span><br>
                    <a href="{{ $archivoActual->urlPublica() }}" target="_blank" rel="noopener">
                        {{ $archivoActual->nombre_original }}
                    </a>
                    @if($archivoActual->esImagen() && $archivoActual->urlThumb())
                        <div class="m-t-8">
                            <img src="{{ $archivoActual->urlThumb() }}" alt="Miniatura del cronograma"
                                style="max-width:100%;border-radius:6px;border:1px solid #e5e7eb;">
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<div class="ln_solid"></div>
<div class="form-group">
    <div class="col-md-12 text-right">
        <a href="{{ route('salud.cronogramas.index') }}" class="btn btn-primary">Cancelar</a>
        <button type="submit" class="btn btn-success">
            <i class="fa fa-save"></i> {{ $cronograma->exists ? 'Actualizar cronograma' : 'Guardar cronograma' }}
        </button>
    </div>
</div>

@section('script')
<script>
    (function () {
        var serviciosPorEstablecimiento = {!! $serviciosData->toJson() !!};
        var seleccionado = '{{ old('servicio_id', $cronograma->servicio_id) }}';

        function actualizarServicios() {
            var establecimiento = document.getElementById('establecimiento_id').value;
            var select = document.getElementById('servicio_id');
            var servicios = serviciosPorEstablecimiento[establecimiento] || [];

            select.innerHTML = '<option value="">General / todos los servicios</option>';

            servicios.forEach(function (servicio) {
                var option = document.createElement('option');
                option.value = servicio.id;
                option.textContent = servicio.nombre;
                if (String(servicio.id) === String(seleccionado)) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        document.getElementById('establecimiento_id').addEventListener('change', function () {
            seleccionado = '';
            actualizarServicios();
        });

        Array.prototype.forEach.call(document.querySelectorAll('.js-preset-titulo'), function (btn) {
            btn.addEventListener('click', function () {
                var tipo = this.getAttribute('data-tipo');
                var titulo = this.getAttribute('data-titulo');
                if (tipo) {
                    document.getElementById('tipo').value = tipo;
                }
                if (titulo) {
                    document.getElementById('titulo').value = titulo;
                }
            });
        });

        actualizarServicios();
    })();
</script>
@endsection
