@extends('serve.layouts.index')
@section('content')

@php
$employeesJson = $employees->map(function ($e) {
    return [
        'id' => $e->id,
        'nombre' => trim($e->full_name),
        'cargo' => optional($e->charges)->charge_employee,
        'ci' => $e->ci,
        'foto' => asset($e->rutaFoto()),
        'ajuste' => $e->ajusteFoto(),
    ];
})->values();
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="cred-app" class="row" style="margin-left: 0; margin-right: 0;">
  <div class="col-md-12 col-sm-12 col-xs-12">
    <main class="layout">
      <section class="panel form-panel" aria-label="Datos de la credencial">
        <p class="alert-msg" id="alert-msg"></p>

        <div class="field employee-picker" id="employee-picker">
          <label for="employee-search">Empleado</label>
          <input type="text" id="employee-search" placeholder="Buscar por nombre o cargo…" autocomplete="off" role="combobox" aria-expanded="false" aria-controls="employee-results">
          <input type="hidden" id="employee" value="">
          <div class="employee-results" id="employee-results" role="listbox" hidden></div>
          <p class="field-hint">Nombre y cargo se toman del funcionario.</p>
        </div>

        <div class="field">
          <label for="foto-input">Foto</label>
          <div class="photo-choice">
            <div class="photo-choice__thumb">
              <img id="foto-preview" alt="">
            </div>
            <div class="photo-choice__actions">
              <label class="btn btn--ghost btn--sm">
                Cambiar foto
                <input type="file" id="foto-input" accept="image/png,image/jpeg" hidden>
              </label>
              <p class="field-hint">PNG o JPG, hasta 8 MB.</p>
            </div>
          </div>
        </div>

        <div class="field foto-ajuste" id="foto-ajuste" hidden>
          <label for="foto-zoom">Encuadre de la foto</label>
          <div class="foto-ajuste__fila">
            <input type="range" id="foto-zoom" min="1" max="4" step="0.01" value="1"
                   aria-label="Tamaño de la foto en la credencial">
            <output id="foto-zoom-salida" for="foto-zoom">100 %</output>
          </div>
          <div class="foto-ajuste__fila">
            <button type="button" class="btn btn--ghost btn--sm" id="foto-centrar">Centrar</button>
            <button type="button" class="btn btn--ghost btn--sm" id="foto-encuadre">Restablecer</button>
          </div>

          <p class="field-hint">Acerca primero con la rueda del mouse o el control de tamaño, y después arrastra la foto dentro de la credencial.</p>
          <p class="foto-ajuste__estado" id="foto-ajuste-estado" role="status" aria-live="polite"></p>
        </div>

        <div class="generated-hint" id="generated-hint">
          Credencial generada · código: <code id="generated-hash"></code><br>
          Verificación: <a id="generated-link" href="#" target="_blank" rel="noopener"></a>
        </div>

        <div class="actions">
          <button type="button" class="btn btn--primary" id="btn-generar">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
            <span id="btn-generar-text">Generar credencial</span>
          </button>
          <button type="button" class="btn btn--primary" id="btn-print-front">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Imp. Anverso
          </button>
          <button type="button" class="btn btn--primary" id="btn-print-back">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Imp. Reverso
          </button>
          {{-- La imagen sale del mismo HTML que el papel, con los mismos ajustes de
               impresion, asi que lo que se descarga es lo que se imprime. --}}
          <button type="button" class="btn btn--ghost" id="btn-descargar-img">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v12m0 0l-4.5-4.5M12 15l4.5-4.5"/><path d="M4 17v2a2 2 0 002 2h12a2 2 0 002-2v-2"/></svg>
            <span id="btn-descargar-img-text">Descargar frontal (PNG)</span>
          </button>
          <button type="button" class="btn btn--ghost" id="btn-reset">Limpiar</button>
        </div>
      </section>

      <section class="panel preview-panel" aria-label="Vista previa">
        <div class="preview-header">
          <h2>Vista previa</h2>
          <span class="badge">Formato · 54 × 86 mm</span>
        </div>

        <div class="preview-stage">
          <div class="preview-scale">
            <div class="cred-sheet" id="preview-sheet">
              <!-- FRENTE -->
              <article class="cred-page cred-page--front" id="preview-front" aria-label="Anverso de credencial">
                <img class="cred-cerro--top" src="{{ asset('client/images/credencial/arribaderecha.png') }}" alt="" onerror="this.hidden=true">
                <header class="cred-header">
                  <div class="cred-header__top">
                    <img class="cred-header__logo" src="{{ asset('client/images/credencial/logo.png') }}" alt="Escudo del municipio" onerror="this.hidden=true">
<p class="cred-header__title"><span>Gobierno Autónomo</span><span>Municipal de Llallagua</span></p>
                  </div>
<div class="cred-header__rule"><i></i><span>GAMLL</span><i></i></div>
                </header>

                <div class="cred-body">
                  <div class="cred-photo">
                    <img class="cred-photo__img" id="preview-foto" src="" alt="" hidden>
                    <div class="cred-photo__placeholder" id="preview-foto-placeholder">
                      <svg viewBox="0 0 24 24" fill="none" stroke="#005029" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                    </div>
                  </div>

                  <div class="cred-fields">
                    <div class="cred-field">
                      <span class="cred-field__label">Nombres y apellidos:</span>
                      <div class="cred-field__box">
                        <span class="cred-field__value" id="preview-nombre">—</span>
                      </div>
                    </div>
                    <div class="cred-field cred-field--cargo">
                      <span class="cred-field__label">Cargo:</span>
                      <div class="cred-field__box">
                        <span class="cred-field__value" id="preview-cargo">—</span>
                      </div>
                    </div>
                    <div class="cred-field cred-field--ci">
                      <span class="cred-field__label">C.I.:</span>
                      <div class="cred-field__box">
                        <span class="cred-field__value" id="preview-ci">—</span>
                      </div>
                    </div>
                  </div>
                </div>

                <img class="cred-cerro" src="{{ asset('client/images/credencial/cerro.png') }}" alt="" onerror="this.hidden=true">
                <p class="cred-city">Llallagua,</p>
                <div class="cred-slogan-wrap">
                  <p class="cred-slogan">Tierra de unidad y paz</p>
                  <span class="cred-slogan-line" aria-hidden="true"></span>
                </div>

                <div class="cred-qr">
                  <img id="preview-qr" alt="QR de verificación">
                </div>
                <p class="cred-verify"><span class="cred-verify__text">Verificación de credencial</span></p>

<footer class="cred-footer"><i></i><span>GAMLL</span><i></i></footer>
                <img class="cred-cerro-border" src="{{ asset('client/images/credencial/abajo.png') }}" alt="" onerror="this.hidden=true">
              </article>

              <!-- REVERSO -->
              <article class="cred-page cred-page--back" aria-label="Reverso de credencial">
                <img class="cred-page__bg" src="{{ asset('client/images/credencial/plantilla-reverso.png') }}" alt="" onerror="this.hidden=true">
                <p class="cred-legal">
                  El presente documento es intransferible y de uso exclusivo del portador. Se solicita a las autoridades civiles, militares, policiales y a toda persona particular prestar la cooperación requerida al portador de la presente <strong>CREDENCIAL</strong> para el desempeño de sus funciones.
                </p>
              </article>
            </div>
          </div>
        </div>

        <p class="preview-hint">Imprime anverso y reverso. Tamaño 54 × 86 mm, escala 100 %, sin márgenes y con «encabezados y pies de página» desmarcado.</p>
      </section>
    </main>
  </div>

  <div class="col-md-12 col-sm-12 col-xs-12">
    <section class="panel search-panel">
      <div class="search-header">
        <div>
          <h2>Credenciales emitidas</h2>
          <p class="search-count" id="search-count"></p>
        </div>
        <input type="search" id="search-input" placeholder="Buscar por nombre, cargo o código…">
      </div>
      <div class="search-results" id="search-results"></div>
    </section>
  </div>

  <!-- Zona de impresión -->
  <div class="print-zone" id="print-zone" aria-hidden="true">
    <img id="print-img-front" alt="Anverso de credencial">
    <img id="print-img-back" alt="Reverso de credencial">
    <article class="cred-page cred-page--front cred-page--print" id="print-front" aria-label="Anverso de credencial">
      <img class="cred-cerro--top" src="{{ asset('client/images/credencial/arribaderecha.png') }}" alt="" onerror="this.hidden=true">
      <header class="cred-header">
        <div class="cred-header__top">
          <img class="cred-header__logo" src="{{ asset('client/images/credencial/logo.png') }}" alt="Escudo del municipio" onerror="this.hidden=true">
          <p class="cred-header__title"><span>Gobierno Autónomo</span><span>Municipal de Llallagua</span></p>
        </div>
<div class="cred-header__rule"><i></i><i></i><span>GAMLL</span><i></i><i></i></div>
      </header>

      <div class="cred-body">
        <div class="cred-photo">
          <img class="cred-photo__img" id="print-foto" src="" alt="" hidden>
          <div class="cred-photo__placeholder" id="print-foto-placeholder">
            <svg viewBox="0 0 24 24" fill="none" stroke="#005029" stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
          </div>
        </div>

        <div class="cred-fields">
          <div class="cred-field">
            <span class="cred-field__label">Nombres y apellidos:</span>
<div class="cred-field__box">
          <span class="cred-field__value" id="print-nombre"></span>
            </div>
          </div>
          <div class="cred-field cred-field--cargo">
            <span class="cred-field__label">Cargo:</span>
            <div class="cred-field__box">
              <span class="cred-field__value" id="print-cargo"></span>
            </div>
          </div>
          <div class="cred-field cred-field--ci">
            <span class="cred-field__label">C.I.:</span>
            <div class="cred-field__box">
              <span class="cred-field__value" id="print-ci"></span>
            </div>
          </div>
        </div>
      </div>

      <img class="cred-cerro" src="{{ asset('client/images/credencial/cerro.png') }}" alt="" onerror="this.hidden=true">
      <p class="cred-city">Llallagua,</p>
      <div class="cred-slogan-wrap">
        <p class="cred-slogan">Tierra de unidad y paz</p>
        <span class="cred-slogan-line" aria-hidden="true"></span>
      </div>

      <div class="cred-qr">
        <img id="print-qr" alt="QR de verificación">
      </div>
      <p class="cred-verify"><span class="cred-verify__text">Verificación de credencial</span></p>

      <footer class="cred-footer"><i></i><span>GAMLL</span><i></i></footer>
      <img class="cred-cerro-border" src="{{ asset('client/images/credencial/abajo.png') }}" alt="" onerror="this.hidden=true">
    </article>

    <article class="cred-page cred-page--back cred-page--print">
      <img class="cred-page__bg" src="{{ asset('client/images/credencial/plantilla-reverso.png') }}" alt="" onerror="this.hidden=true">
      <p class="cred-legal">
        El presente documento es intransferible y de uso exclusivo del portador. Se solicita a las autoridades civiles, militares, policiales y a toda persona particular prestar la cooperación requerida al portador de la presente <strong>CREDENCIAL</strong> para el desempeño de sus funciones.
      </p>
    </article>
  </div>
</div>

@endsection

@section('script')
{{-- ?v= evita que el navegador sirva un credencial.js/css viejo: si se recarga
     solo el CSS, la impression sale con el recorte y las lineas del bug anterior. --}}
<link rel="stylesheet" href="{{ asset('css/credencial.css') }}?v={{ filemtime(public_path('css/credencial.css')) }}">
<script src="{{ asset('js/vendor/qrcode.min.js') }}"></script>
<script>
window.GAMCredencial = {
  employees: @json($employeesJson, JSON_UNESCAPED_UNICODE),
  generateUrl: "{{ route('credenciales.generate') }}",
  fotoUrl: "{{ route('credenciales.foto') }}",
  ajusteUrl: "{{ route('credenciales.foto.ajuste') }}",
  dataUrl: "{{ route('credenciales.data') }}",
  deleteUrl: "{{ route('credenciales.destroy', '__id__') }}",
  assetsBase: "{{ asset('client/images/credencial') }}",
  cssUrl: "{{ asset('css/credencial.css') }}?v={{ filemtime(public_path('css/credencial.css')) }}",
  plantilla: "frente1"
};
</script>
<script src="{{ asset('js/credencial.js') }}?v={{ filemtime(public_path('js/credencial.js')) }}"></script>
@endsection