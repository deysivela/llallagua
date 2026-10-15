@extends('client.layouts.index')
@section('title', 'Actividades | GAM Llallagua')
@section('content')
    @include('client.partials.page-banner', [
        'title' => 'Actividades',
        'subtitle' => 'Convocatorias, cursos y talleres del municipio de Llallagua',
        'image' => asset('client/images/actividades/banner-bg.jpg'),
        'align' => 'left',
    ])

    <section class="gam-inner-section gam-actividades-page">
        <div class="container">
            <div class="gam-actividades-intro" data-aos="fade-up" data-aos-duration="800">
                <div class="gam-actividades-intro-copy">
                    <h2 class="gam-heading">Aprender, participar y crecer en comunidad</h2>
                    <p class="gam-lead">
                        El GAM Llallagua publica aquí las convocatorias y actividades formativas
                        abiertas a la ciudadanía, para fortalecer capacidades y promover el desarrollo local.
                    </p>
                </div>
                <nav class="gam-actividades-jump" aria-label="Ir a una sección">
                    <span class="gam-side-label">Ir a</span>
                    <div class="gam-actividades-jump-list">
                        <a class="gam-chip" href="#agenda">Agenda</a>
                        <a class="gam-chip" href="#participar">Información</a>
                    </div>
                </nav>
            </div>
        </div>
    </section>

    <section class="gam-inner-section gam-section--white" id="agenda" aria-labelledby="agenda-titulo">
        <div class="container">
            <div class="mb-4" data-aos="fade-up" data-aos-duration="800">
                <span class="gam-eyebrow">Agenda oficial</span>
                <h2 class="gam-heading" id="agenda-titulo">Actividades publicadas</h2>
            </div>

            @if(count($cronogramas))
                <div class="gam-news-secondary-grid" data-aos="fade-up" data-aos-duration="900">
                    @foreach($cronogramas as $item)
                        @include('client.partials.cultura-card', [
                            'item' => $item,
                            'limit' => 150,
                            'fallbackTitle' => 'Actividad municipal',
                        ])
                    @endforeach
                </div>
            @else
                <div class="gam-empty-state">
                    <i class="ti-calendar"></i>
                    <h2 class="gam-heading-sm">Todavía no hay actividades publicadas</h2>
                    <p>Pronto publicaremos las convocatorias, fechas y lugares de cada actividad municipal.</p>
                </div>
            @endif
        </div>
    </section>

    @include('client.partials.cultura-detail-modal', ['showDownload' => false])

    <section class="gam-inner-section" id="participar" aria-labelledby="participar-titulo">
        <div class="container">
            <div class="gam-actividades-cta" data-aos="fade-up" data-aos-duration="900">
                <div>
                    <span class="gam-eyebrow">Información</span>
                    <h2 class="gam-heading" id="participar-titulo">Mantente atento</h2>
                    <p class="gam-lead text-left">
                        Las inscripciones se realizan en ventanilla municipal o según
                        la convocatoria de cada actividad publicada.
                    </p>
                </div>
                <ul class="gam-subalc-facts gam-actividades-facts">
                    <li>
                        <span class="gam-subalc-fact-icon" aria-hidden="true">
                            <i class="icon-Map-Marker2"></i>
                        </span>
                        <span class="gam-subalc-fact-copy">
                            <span class="gam-side-label">Atención</span>
                            <strong>Av. 10 de Noviembre · Edificio Central</strong>
                        </span>
                    </li>
                    <li>
                        <span class="gam-subalc-fact-icon" aria-hidden="true">
                            <i class="ti-info-alt"></i>
                        </span>
                        <span class="gam-subalc-fact-copy">
                            <span class="gam-side-label">Importante</span>
                            <strong>Revise esta sección para conocer las nuevas convocatorias</strong>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </section>
@endsection
