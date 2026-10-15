@extends('client.layouts.index')
@section('title', 'Unidad de Zoonosis | GAM Llallagua')
@section('content')
@php
    $servicios = [
        [
            'icon' => 'ti-heart',
            'titulo' => 'Vacunación antirrábica',
            'texto' => 'Jornadas periódicas para perros y gatos en barrios y subalcaldías del municipio.',
        ],
        [
            'icon' => 'ti-reload',
            'titulo' => 'Esterilización',
            'texto' => 'Control poblacional responsable de animales de compañía, con campañas coordinadas.',
        ],
        [
            'icon' => 'ti-shield',
            'titulo' => 'Control de zoonosis',
            'texto' => 'Prevención de enfermedades que pueden transmitirse de animales a personas.',
        ],
        [
            'icon' => 'ti-announcement',
            'titulo' => 'Tenencia responsable',
            'texto' => 'Charlas y actividades educativas para el cuidado y bienestar de las mascotas.',
        ],
    ];
    $zoonosisGps = "18°24'51.1\"S 66°33'59.9\"W";
    $zoonosisMapsQuery = '-18.414194,-66.566639';
@endphp

    @include('client.partials.page-banner', [
        'title' => 'Unidad de Zoonosis',
        'subtitle' => 'Campañas municipales de vacunación, esterilización y tenencia responsable',
        'image' => asset('client/images/actividades/zoonosis.png'),
        'align' => 'left',
        'extraClass' => 'gam-page-banner--media gam-page-banner--zoonosis',
        'aspect' => '2172 / 724',
    ])

    <section class="gam-inner-section gam-zoonosis-page">
        <div class="container">
            <div class="gam-zoonosis-intro" data-aos="fade-up" data-aos-duration="800">
                <div class="gam-zoonosis-intro-copy">
                    <h2 class="gam-heading">Cuidado animal en el municipio</h2>
                    <p class="gam-lead">
                        La Unidad de Zoonosis del GAM Llallagua impulsa campañas de vacunación,
                        esterilización y educación ciudadana para proteger la salud de las familias
                        y el bienestar de los animales del municipio.
                    </p>
                </div>
                <nav class="gam-zoonosis-jump" aria-label="Ir a una sección">
                    <span class="gam-side-label">Ir a</span>
                    <div class="gam-zoonosis-jump-list">
                        <a class="gam-chip" href="#servicios">Servicios</a>
                        <a class="gam-chip" href="#cronogramas">Cronogramas</a>
                        <a class="gam-chip" href="#ubicacion">Ubicación</a>
                    </div>
                </nav>
            </div>
        </div>
    </section>

    <section class="gam-section gam-section--white" id="servicios" aria-label="Campañas y servicios">
        <div class="container">
            @include('client.partials.section-heading', [
                'title' => 'Campañas y servicios',
                'subtitle' => 'Acciones permanentes orientadas a la salud animal y la prevención de zoonosis.',
                'align' => 'left',
            ])

            <div class="gam-zoonosis-services">
                @foreach($servicios as $i => $servicio)
                    <article class="gam-zoonosis-service" data-aos="fade-up" data-aos-duration="{{ 700 + ($i * 60) }}">
                        <span class="gam-zoonosis-service-icon" aria-hidden="true">
                            <i class="{{ $servicio['icon'] }}"></i>
                        </span>
                        <h3>{{ $servicio['titulo'] }}</h3>
                        <p>{{ $servicio['texto'] }}</p>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="gam-inner-section gam-section--white" id="cronogramas" aria-labelledby="cronogramas-titulo">
        <div class="container">
            <div class="mb-4" data-aos="fade-up" data-aos-duration="800">
                <span class="gam-eyebrow">Agenda oficial</span>
                <h2 class="gam-heading" id="cronogramas-titulo">Cronogramas y documentos</h2>
            </div>

            @if(count($cronogramas))
                <div class="gam-news-secondary-grid" data-aos="fade-up" data-aos-duration="900">
                    @foreach($cronogramas as $item)
                        @include('client.partials.cultura-card', [
                            'item' => $item,
                            'limit' => 150,
                            'fallbackTitle' => 'Cronograma de zoonosis',
                        ])
                    @endforeach
                </div>
            @else
                <div class="gam-empty-state">
                    <i class="ti-calendar"></i>
                    <h2 class="gam-heading-sm">Todavía no hay cronogramas ni documentos publicados</h2>
                    <p>Pronto publicaremos las campañas y jornadas de la Unidad de Zoonosis.</p>
                </div>
            @endif
        </div>
    </section>

    @include('client.partials.cultura-detail-modal', ['showDownload' => false])

    <section class="gam-inner-section" id="ubicacion" aria-labelledby="ubicacion-titulo">
        <div class="container">
            <div class="gam-zoonosis-location" data-aos="fade-up" data-aos-duration="900">
                <div class="gam-zoonosis-location-copy">
                    <span class="gam-eyebrow">Cómo llegar</span>
                    <h2 class="gam-heading" id="ubicacion-titulo">Unidad de Zoonosis</h2>
                    <p class="gam-lead text-left">
                        La oficina atiende en Catavi, junto al Estadio Serafín Ferreira
                        y frente a la parada de taxis 19 de Marzo.
                    </p>

                    <ul class="gam-subalc-facts gam-zoonosis-facts">
                        <li>
                            <span class="gam-subalc-fact-icon" aria-hidden="true">
                                <i class="icon-Map-Marker2"></i>
                            </span>
                            <span class="gam-subalc-fact-copy">
                                <span class="gam-side-label">Distrito</span>
                                <strong>Catavi</strong>
                            </span>
                        </li>
                        @include('client.partials.contacto-por-cargo', [
                            'personas' => $responsables ?? collect(),
                            'etiqueta' => 'Responsable',
                        ])
                        <li>
                            <span class="gam-subalc-fact-icon" aria-hidden="true">
                                <i class="ti-flag-alt"></i>
                            </span>
                            <span class="gam-subalc-fact-copy">
                                <span class="gam-side-label">Referencia</span>
                                <strong>Estadio Serafín Ferreira</strong>
                            </span>
                        </li>
                        <li>
                            <span class="gam-subalc-fact-icon" aria-hidden="true">
                                <i class="ti-car"></i>
                            </span>
                            <span class="gam-subalc-fact-copy">
                                <span class="gam-side-label">Cómo llegar</span>
                                <strong>Frente a la parada de taxis 19 de Marzo</strong>
                            </span>
                        </li>
                        <li>
                            <span class="gam-subalc-fact-icon" aria-hidden="true">
                                <i class="ti-location-pin"></i>
                            </span>
                            <span class="gam-subalc-fact-copy">
                                <span class="gam-side-label">GPS</span>
                                <a class="gam-zoonosis-gps"
                                    href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($zoonosisMapsQuery) }}"
                                    target="_blank" rel="noopener noreferrer">{{ $zoonosisGps }}</a>
                            </span>
                        </li>
                    </ul>

                    <a class="gam-zoonosis-maps-link"
                        href="https://www.google.com/maps/search/?api=1&amp;query={{ urlencode($zoonosisMapsQuery) }}"
                        target="_blank" rel="noopener noreferrer">
                        Abrir en Google Maps <i class="ti-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>

                <div class="gam-zoonosis-location-map">
                    <iframe
                        src="https://maps.google.com/maps?q={{ urlencode($zoonosisMapsQuery) }}&amp;hl=es-419&amp;z=18&amp;output=embed"
                        title="Mapa GPS de la Unidad de Zoonosis: {{ $zoonosisGps }}"
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"
                        allowfullscreen></iframe>
                </div>
            </div>
        </div>
    </section>
@endsection
