@extends('client.layouts.index')
@section('title', $establecimiento->nombre . ' | Salud GAM Llallagua')

@push('styles')
    <link href="{{ asset('client/css/salud.css') }}?v={{ @filemtime(public_path('client/css/salud.css')) }}" rel="stylesheet">
@endpush

@section('content')
@php
    $serviciosAgrupados = $establecimiento->servicios->groupBy(function ($servicio) {
        return $servicio->categoria ?: 'otro';
    });
    $mapaQuery = ($establecimiento->latitud && $establecimiento->longitud)
        ? $establecimiento->latitud . ',' . $establecimiento->longitud
        : $establecimiento->nombre . ', Llallagua, Potosí, Bolivia';
    $fotoHero = $establecimiento->fotoUrlGrande() ?: $establecimiento->fotoUrl();
@endphp

<header class="salud-detail-hero {{ $fotoHero ? 'salud-detail-hero--has-bg' : '' }}">
    @if($fotoHero)
        <div class="salud-detail-hero__backdrop" style="background-image: url('{{ $fotoHero }}');" aria-hidden="true"></div>
        <div class="salud-detail-hero__bg" style="background-image: url('{{ $fotoHero }}');" aria-hidden="true"></div>
    @endif
    <div class="container">
        <div class="salud-detail-hero__content">
            <h1 class="salud-title">{{ $establecimiento->nombre }}</h1>

            <div class="salud-detail-badges">
                <span class="salud-status salud-status--{{ $establecimiento->abierto ? 'open' : 'closed' }}">
                    <span class="salud-status__dot"></span>
                    {{ $establecimiento->abierto ? 'Abierto ahora' : 'Cerrado ahora' }}
                </span>
                @if($establecimiento->atiende_24h)
                    <span class="salud-badge-24h">Atención 24 horas</span>
                @endif
            </div>

            <ul class="salud-meta salud-meta--light">
                @if($establecimiento->direccion)
                    <li>
                        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                            <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z" fill="none" stroke="currentColor" stroke-width="1.8"></path>
                            <circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="1.8"></circle>
                        </svg>
                        <span>{{ $establecimiento->direccion }}@if($establecimiento->zona) · {{ $establecimiento->zona }}@endif</span>
                    </li>
                @endif
                @if($establecimiento->telefonos->isNotEmpty())
                    <li>
                        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                            <path d="M6.5 3.5l3 3-1.8 2.6a12 12 0 0 0 5.2 5.2l2.6-1.8 3 3-2 3.2c-7.6.6-13.2-5-12.6-12.6z" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"></path>
                        </svg>
                        <span>{{ $establecimiento->telefonos->pluck('numero')->implode(' · ') }}</span>
                    </li>
                @endif
            </ul>
        </div>
    </div>
</header>

<main class="salud-page salud-page--detail">
    <div class="container">
        <div class="salud-detail-layout">
            <div class="salud-detail-main">
                @if($establecimiento->descripcion)
                    <section class="salud-panel">
                        <h2 class="salud-panel__title">Sobre el establecimiento</h2>
                        <p class="salud-prose">{{ $establecimiento->descripcion }}</p>
                    </section>
                @endif

                @if($establecimiento->servicios->isNotEmpty())
                    @php
                        $tieneServiciosDetallados = $establecimiento->servicios->contains(function ($s) {
                            if (!$s->descripcion) {
                                return false;
                            }
                            $lineas = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $s->descripcion)));
                            return count($lineas) > 1;
                        });
                    @endphp
                    <section class="salud-panel">
                        <h2 class="salud-panel__title">Servicios disponibles</h2>
                        @if($tieneServiciosDetallados)
                            <div class="salud-service-groups">
                                @foreach($serviciosAgrupados as $categoria => $servicios)
                                    <div class="salud-service-group">
                                        <h3>{{ ($establecimiento->tipo === 'rehabilitacion' && $categoria === 'medicina_general') ? 'Medicina' : ($categorias[$categoria] ?? ucfirst($categoria)) }}</h3>
                                        <ul>
                                            @foreach($servicios as $servicio)
                                                @php
                                                    $lineasDesc = [];
                                                    if ($servicio->descripcion) {
                                                        $lineasDesc = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $servicio->descripcion)), function ($l) {
                                                            return $l !== '';
                                                        }));
                                                    }
                                                @endphp
                                                <li class="{{ count($lineasDesc) > 1 ? 'salud-service-item--detailed' : '' }}">
                                                    @if(count($lineasDesc) <= 1)
                                                        <strong>{{ $servicio->nombre }}</strong>
                                                    @endif
                                                    @if(count($lineasDesc) === 1)
                                                        <span>{{ $lineasDesc[0] }}</span>
                                                    @elseif(count($lineasDesc) > 1)
                                                        @php
                                                            $responsablesLineas = [];
                                                            $itemsLineas = [];
                                                            foreach ($lineasDesc as $linea) {
                                                                $limpia = trim(preg_replace('/^[•\-\*]\s*/u', '', $linea));
                                                                if (preg_match('/^Responsables?\s*:/iu', $limpia)) {
                                                                    $responsablesLineas[] = $limpia;
                                                                } elseif ($limpia !== '') {
                                                                    $itemsLineas[] = $limpia;
                                                                }
                                                            }
                                                        @endphp
                                                        @foreach($responsablesLineas as $respLinea)
                                                            <p class="salud-service-item__resp">{{ $respLinea }}</p>
                                                        @endforeach
                                                        @if(!empty($itemsLineas))
                                                            <ul class="salud-service-item__list">
                                                                @foreach($itemsLineas as $itemLinea)
                                                                    <li>{{ $itemLinea }}</li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="salud-service-group">
                                <ul class="salud-service-grid">
                                    @foreach($establecimiento->servicios as $servicio)
                                        <li>
                                            <strong>{{ $servicio->nombre }}</strong>
                                            @if($servicio->descripcion)
                                                <span>{{ $servicio->descripcion }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </section>
                @endif

                @if($establecimiento->secciones->isNotEmpty())
                    @foreach($establecimiento->secciones as $seccion)
                        <section class="salud-panel">
                            <h2 class="salud-panel__title">{{ $seccion->titulo }}</h2>
                            @if($seccion->texto)
                                <p class="salud-prose">{{ $seccion->texto }}</p>
                            @endif
                            @if(!empty($seccion->items))
                                @php
                                    $itemsNormalizados = array_map(function ($it) {
                                        return is_array($it) ? ($it['texto'] ?? implode(' · ', $it)) : (string) $it;
                                    }, $seccion->items);
                                    $esFormatoEspecialidad = count($itemsNormalizados) >= 3 && collect($itemsNormalizados)->every(function ($linea) {
                                        return strpos($linea, ':') !== false && mb_strlen( trim(explode(':', $linea, 2)[0]) ) <= 42;
                                    });
                                @endphp
                                @if($esFormatoEspecialidad)
                                    <div class="salud-specialty-grid">
                                        @foreach($itemsNormalizados as $linea)
                                            @php
                                                [$clave, $valor] = array_pad(explode(':', $linea, 2), 2, '');
                                            @endphp
                                            <div class="salud-specialty-card">
                                                <span class="salud-specialty-card__label">{{ trim($clave) }}</span>
                                                <span class="salud-specialty-card__value">{{ trim($valor) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <ul class="salud-list">
                                        @foreach($itemsNormalizados as $item)
                                            <li>{{ $item }}</li>
                                        @endforeach
                                    </ul>
                                @endif
                            @endif
                        </section>
                    @endforeach
                @endif

                @if($establecimiento->cronogramasFilas->isNotEmpty())
                    @php
                        $tieneObservacionesFilas = $establecimiento->cronogramasFilas->filter(function ($f) {
                            return !empty($f->observaciones);
                        })->isNotEmpty();
                    @endphp
                    <section class="salud-panel">
                        <h2 class="salud-panel__title">Cronograma semanal de atención por especialidad</h2>
                        <div class="salud-table-wrap">
                            <table class="salud-table">
                                <thead>
                                    <tr>
                                        <th>Día</th>
                                        <th>Área / Especialidad</th>
                                        <th>Profesional responsable</th>
                                        <th>Horario</th>
                                        @if($tieneObservacionesFilas)
                                            <th>Consultorio / Área</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($establecimiento->cronogramasFilas as $fila)
                                        <tr>
                                            <td>{{ $fila->dia_o_fecha ?: '—' }}</td>
                                            <td><strong>{{ $fila->servicio ?: '—' }}</strong></td>
                                            <td>{{ $fila->profesional ?: '—' }}</td>
                                            <td>{{ $fila->horario ?: '—' }}</td>
                                            @if($tieneObservacionesFilas)
                                                <td>{{ $fila->observaciones ?: '—' }}</td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                <section class="salud-panel" id="cronogramas">
                    <h2 class="salud-panel__title">Cronogramas y actividades</h2>

                    @if($establecimiento->cronogramasMensuales->isEmpty())
                        <p class="salud-prose">Aún no se publicaron cronogramas mensuales, calendarios de vacunación ni campañas para este establecimiento.</p>
                    @else
                        <div class="salud-downloads">
                            @foreach($establecimiento->cronogramasMensuales as $cronograma)
                                @if($cronograma->archivo && $cronograma->archivo->esImagen())
                                    <article class="salud-download salud-download--image">
                                        <div class="salud-download__body">
                                            <div class="salud-download__meta">
                                                <span class="salud-download__badge">{{ $cronograma->tipo_corto }}</span>
                                                @if($cronograma->fecha_fin)
                                                    <span class="salud-download__period">Hasta el {{ $cronograma->fecha_fin->format('d/m/Y') }}</span>
                                                @elseif($cronograma->mes)
                                                    <span class="salud-download__period">{{ $cronograma->mes->format('m/Y') }}</span>
                                                @endif
                                            </div>
                                            <h3>{{ $cronograma->titulo ?: $cronograma->tipo_corto }}</h3>
                                            @if($cronograma->servicio)
                                                <p class="salud-download__service">{{ $cronograma->servicio->nombre }}</p>
                                            @endif
                                            @if($cronograma->nota)
                                                <p class="salud-download__note">{{ $cronograma->nota }}</p>
                                            @endif
                                        </div>
                                        <a class="salud-download__preview" href="{{ $cronograma->archivo->urlPublica() }}" target="_blank" rel="noopener">
                                            <img src="{{ $cronograma->archivo->urlPublica() }}" alt="{{ $cronograma->titulo ?: 'Cronograma de atención' }}" loading="lazy">
                                        </a>
                                        <a class="salud-download__link" href="{{ $cronograma->archivo->urlPublica() }}" target="_blank" rel="noopener">
                                            Ver imagen completa
                                            <span aria-hidden="true">↗</span>
                                        </a>
                                    </article>
                                @elseif($cronograma->archivo)
                                    <article class="salud-download">
                                        <div class="salud-download__icon" aria-hidden="true">
                                            PDF
                                        </div>
                                        <div class="salud-download__body">
                                            <div class="salud-download__meta">
                                                <span class="salud-download__badge">{{ $cronograma->tipo_corto }}</span>
                                                @if($cronograma->fecha_fin)
                                                    <span class="salud-download__period">Hasta el {{ $cronograma->fecha_fin->format('d/m/Y') }}</span>
                                                @elseif($cronograma->mes)
                                                    <span class="salud-download__period">{{ $cronograma->mes->format('m/Y') }}</span>
                                                @endif
                                            </div>
                                            <h3>{{ $cronograma->titulo ?: $cronograma->tipo_corto }}</h3>
                                            @if($cronograma->servicio)
                                                <p class="salud-download__service">{{ $cronograma->servicio->nombre }}</p>
                                            @endif
                                            @if($cronograma->nota)
                                                <p class="salud-download__note">{{ $cronograma->nota }}</p>
                                            @endif
                                            <a class="salud-download__link" href="{{ $cronograma->archivo->urlPublica() }}" target="_blank" rel="noopener">
                                                Abrir archivo
                                                <span aria-hidden="true">↗</span>
                                            </a>
                                        </div>
                                    </article>
                                @else
                                    <article class="salud-download">
                                        <div class="salud-download__icon" aria-hidden="true">
                                            INFO
                                        </div>
                                        <div class="salud-download__body">
                                            <div class="salud-download__meta">
                                                <span class="salud-download__badge">{{ $cronograma->tipo_corto }}</span>
                                                @if($cronograma->fecha_fin)
                                                    <span class="salud-download__period">Hasta el {{ $cronograma->fecha_fin->format('d/m/Y') }}</span>
                                                @elseif($cronograma->mes)
                                                    <span class="salud-download__period">{{ $cronograma->mes->format('m/Y') }}</span>
                                                @endif
                                            </div>
                                            <h3>{{ $cronograma->titulo ?: $cronograma->tipo_corto }}</h3>
                                            @if($cronograma->servicio)
                                                <p class="salud-download__service">{{ $cronograma->servicio->nombre }}</p>
                                            @endif
                                            @if($cronograma->nota)
                                                <p class="salud-download__note">{{ $cronograma->nota }}</p>
                                            @endif
                                        </div>
                                    </article>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </section>
            </div>

            <aside class="salud-detail-aside">
                <section class="salud-panel">
                    <h2 class="salud-panel__title">Horarios de atención</h2>
                    @if($establecimiento->horarios->isEmpty())
                        <p class="salud-prose">Horarios no registrados. Consulta telefónicamente.</p>
                    @else
                        <ul class="salud-hours">
                            @foreach($establecimiento->horarios as $horario)
                                <li>
                                    <span class="salud-hours__days">{{ $horario->diasTexto() }}</span>
                                    <span class="salud-hours__time">{{ substr($horario->hora_inicio, 0, 5) }} – {{ substr($horario->hora_fin, 0, 5) }}</span>
                                    @if($horario->nota)
                                        <span class="salud-hours__note">{{ $horario->nota }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </section>

                <section class="salud-panel">
                    <h2 class="salud-panel__title">Ubicación</h2>
                    <div class="salud-map">
                        <iframe
                            src="https://www.google.com/maps?q={{ urlencode($mapaQuery) }}&amp;t=k&amp;z=17&amp;output=embed"
                            title="Mapa de ubicación de {{ $establecimiento->nombre }}"
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            allowfullscreen></iframe>
                    </div>
                    <a class="salud-btn" href="{{ $establecimiento->urlMapa() }}" target="_blank" rel="noopener">
                        Abrir en Google Maps
                    </a>
                </section>

                @if($establecimiento->responsable)
                    <section class="salud-panel salud-panel--compact">
                        <h2 class="salud-panel__title">Responsable</h2>
                        <p class="salud-prose">{{ $establecimiento->responsable }}</p>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</main>
@endsection
