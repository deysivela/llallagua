@extends('client.layouts.index')
@section('title', 'Nómina de Autoridades | GAM Llallagua')
@section('content')
    @php
        $prioridadCargo = [
            'ALCALDE MUNICIPAL' => 1,
            'PRESIDENTE DEL CONCEJO' => 2,
        ];

        $porCargo = $employees
            ->groupBy(function ($autoridad) {
                return optional($autoridad->charges)->charge_employee ?: 'Autoridad municipal';
            })
            ->sortBy(function ($grupo, $cargo) use ($prioridadCargo) {
                $clave = mb_strtoupper(trim($cargo));
                $orden = $prioridadCargo[$clave] ?? 100;
                return sprintf('%03d-%s', $orden, $clave);
            });

        $autoridades = $porCargo->flatMap(function ($grupo) {
            return $grupo;
        });
    @endphp

    <section class="gam-inner-section gam-autoridades-page">
        <div class="container">
            <header class="gam-autoridades-head" data-aos="fade-up" data-aos-duration="700">
                <h1 class="gam-heading">Nómina de Autoridades</h1>
            </header>

            @if ($autoridades->isNotEmpty())
                <div class="gam-gen-grid" data-aos="fade-up" data-aos-duration="800">
                    @foreach($autoridades as $autoridad)
                        <article class="gam-gen-card">
                            @if($autoridad->photo)
                                <div class="gam-gen-photo-wrap">
                                    <img class="gam-gen-photo"
                                        src="{{ asset($autoridad->photo) }}"
                                        alt="{{ trim($autoridad->name . ' ' . $autoridad->last_name) }}"
                                        loading="lazy"
                                        decoding="async">
                                </div>
                            @else
                                <div class="gam-gen-photo-wrap gam-gen-photo-wrap--empty">
                                    <i class="icon-Business-Mens" aria-hidden="true"></i>
                                </div>
                            @endif
                            <span class="gam-gen-role">{{ mb_strtoupper(trim($autoridad->charge_employee)) }}</span>
                            <h3 class="gam-gen-name">
                                {{ trim($autoridad->name . ' ' . $autoridad->last_name) }}
                            </h3>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="gam-autoridades-empty-static" data-aos="fade-up" data-aos-duration="800">
                    <i class="icon-Business-Mens" aria-hidden="true"></i>
                    <p>Pronto publicaremos la nómina de autoridades municipales.</p>
                </div>
            @endif

            <aside class="gam-alcalde-links" aria-label="Secciones relacionadas" data-aos="fade-up"
                data-aos-duration="900">
                <span class="gam-side-label">También puede consultar</span>
                <div class="gam-alcalde-links-row">
                    <a class="gam-alcalde-link" href="{{ route('alcalde') }}">
                        <i class="icon-Administrator" aria-hidden="true"></i>
                        <span>
                            <strong>Alcalde Municipal</strong>
                            <em>Autoridad ejecutiva</em>
                        </span>
                        <i class="ti-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a class="gam-alcalde-link" href="{{ route('personal') }}">
                        <i class="icon-Business-Mens" aria-hidden="true"></i>
                        <span>
                            <strong>Personal dependiente</strong>
                            <em>Nómina municipal</em>
                        </span>
                        <i class="ti-arrow-right" aria-hidden="true"></i>
                    </a>
                    <a class="gam-alcalde-link" href="{{ route('escala-salarial') }}">
                        <i class="icon-Coins" aria-hidden="true"></i>
                        <span>
                            <strong>Escala salarial</strong>
                            <em>Remuneraciones</em>
                        </span>
                        <i class="ti-arrow-right" aria-hidden="true"></i>
                    </a>
                </div>
            </aside>
        </div>
    </section>
@endsection
