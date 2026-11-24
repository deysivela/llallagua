@extends('client.layouts.index')
@section('title', 'Salud | GAM Llallagua')

@push('styles')
    <link href="{{ asset('client/css/salud.css') }}?v={{ @filemtime(public_path('client/css/salud.css')) }}" rel="stylesheet">
@endpush

@section('content')
<header class="salud-hero">
    <div class="container">
        <div class="salud-hero__inner">
            <p class="salud-eyebrow">Dirección de Salud</p>
            <h1 class="salud-title">Establecimientos de salud de Llallagua</h1>
            <p class="salud-lead">
                Encuentra el centro de salud más cercano, revisa sus horarios de atención y descarga
                los cronogramas mensuales de servicios.
            </p>
        </div>
    </div>
</header>

<main class="salud-page">
    <div class="container">
        @if($establecimientos->isEmpty())
            <div class="salud-empty">
                <h2>Aún no hay establecimientos publicados</h2>
                <p>La información se actualizará próximamente.</p>
            </div>
        @else
            <section class="salud-toolbar" aria-label="Buscar establecimientos">
                <div class="salud-search">
                    <label class="sr-only" for="salud-buscar">Buscar por nombre, zona o servicio</label>
                    <svg class="salud-search__icon" viewBox="0 0 24 24" aria-hidden="true" width="18" height="18">
                        <circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"></circle>
                        <path d="M16.5 16.5L21 21" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"></path>
                    </svg>
                    <input type="search" id="salud-buscar" placeholder="Buscar por nombre, zona o servicio…" autocomplete="off">
                </div>

                <div class="salud-filters" role="group" aria-label="Filtrar establecimientos">
                    <button type="button" class="salud-chip is-active" data-filter="todos">Todos</button>
                    <button type="button" class="salud-chip" data-filter="abierto">Abierto ahora</button>
                    @foreach($servicios as $clave => $etiqueta)
                        <button type="button" class="salud-chip" data-filter="{{ $clave }}">{{ $etiqueta }}</button>
                    @endforeach
                </div>
            </section>

            <div class="salud-grid" id="salud-grid">
                @foreach($establecimientos as $establecimiento)
                    @php
                        $categoriasCard = $establecimiento->servicios->pluck('categoria')->filter()->unique()->values();
                        $busqueda = mb_strtolower(implode(' ', array_filter([
                            $establecimiento->nombre,
                            $establecimiento->tipo_texto,
                            $establecimiento->zona,
                            $establecimiento->direccion,
                            $establecimiento->servicios->pluck('nombre')->implode(' '),
                        ])));
                    @endphp
                    <article
                        class="salud-card"
                        data-search="{{ $busqueda }}"
                        data-categorias="{{ $categoriasCard->implode(' ') }}"
                        data-abierto="{{ $establecimiento->abierto ? '1' : '0' }}"
                    >
                        <a class="salud-card__media" href="{{ route('salud.public.show', $establecimiento->slug) }}">
                            @if($establecimiento->fotoUrl())
                                <img src="{{ $establecimiento->fotoUrl() }}" alt="Fachada de {{ $establecimiento->nombre }}" loading="lazy">
                            @else
                                <span class="salud-card__placeholder" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" width="42" height="42">
                                        <path d="M12 3l9 7v11h-6v-6H9v6H3V10z" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"></path>
                                        <path d="M12 8v4M10 10h4" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"></path>
                                    </svg>
                                </span>
                            @endif

                            <span class="salud-status salud-status--{{ $establecimiento->abierto ? 'open' : 'closed' }}">
                                <span class="salud-status__dot"></span>
                                {{ $establecimiento->abierto ? 'Abierto ahora' : 'Cerrado' }}
                            </span>
                        </a>

                        <div class="salud-card__body">
                            <p class="salud-card__type">
                                <span>{{ $establecimiento->tipo_texto }}</span>
                                @if($establecimiento->nivel)
                                    <span class="salud-card__level">{{ $establecimiento->nivel }}</span>
                                @endif
                                @if($establecimiento->atiende_24h)
                                    <span class="salud-badge-24h">24 h</span>
                                @endif
                            </p>
                            <h2 class="salud-card__title">
                                <a href="{{ route('salud.public.show', $establecimiento->slug) }}">{{ $establecimiento->nombre }}</a>
                            </h2>

                            <ul class="salud-meta">
                                @if($establecimiento->direccion)
                                    <li>
                                        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                                            <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z" fill="none" stroke="currentColor" stroke-width="1.7"></path>
                                            <circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor" stroke-width="1.7"></circle>
                                        </svg>
                                        <a class="salud-card__address-link" href="{{ $establecimiento->urlMapa() }}" target="_blank" rel="noopener" title="Ver ubicación en Google Maps">
                                            {{ $establecimiento->direccion }}@if($establecimiento->zona) · {{ $establecimiento->zona }}@endif
                                        </a>
                                    </li>
                                @endif
                                @if($establecimiento->telefonos->isNotEmpty())
                                    <li>
                                        <svg viewBox="0 0 24 24" width="16" height="16" aria-hidden="true">
                                            <path d="M6.5 3.5l3 3-1.8 2.6a12 12 0 0 0 5.2 5.2l2.6-1.8 3 3-2 3.2c-7.6.6-13.2-5-12.6-12.6z" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"></path>
                                        </svg>
                                        <span>{{ $establecimiento->telefonos->pluck('numero')->implode(' · ') }}</span>
                                    </li>
                                @endif
                            </ul>

                            @if($establecimiento->servicios->isNotEmpty())
                                <ul class="salud-services">
                                    @foreach($establecimiento->servicios->take(3) as $servicio)
                                        <li>{{ $servicio->nombre }}</li>
                                    @endforeach
                                    @if($establecimiento->servicios->count() > 3)
                                        <li class="salud-services__more">+{{ $establecimiento->servicios->count() - 3 }} más</li>
                                    @endif
                                </ul>
                            @endif

                            <div class="salud-card__actions">
                                <a class="salud-card__link" href="{{ route('salud.public.show', $establecimiento->slug) }}">
                                    Ver horarios y cronogramas
                                    <span aria-hidden="true">→</span>
                                </a>
                                <a class="salud-card__map-btn" href="{{ $establecimiento->urlMapa() }}" target="_blank" rel="noopener" title="Abrir ubicación en Google Maps">
                                    <svg viewBox="0 0 24 24" width="15" height="15" aria-hidden="true">
                                        <path d="M12 21s7-5.4 7-11a7 7 0 1 0-14 0c0 5.6 7 11 7 11z" fill="none" stroke="currentColor" stroke-width="1.9"></path>
                                        <circle cx="12" cy="10" r="2.4" fill="none" stroke="currentColor" stroke-width="1.9"></circle>
                                    </svg>
                                    <span>Maps</span>
                                </a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <div class="salud-empty" id="salud-sin-resultados" hidden>
                <h2>Sin resultados</h2>
                <p>Prueba con otro nombre, zona o servicio.</p>
            </div>
        @endif
    </div>
</main>
@endsection

@section('script')
<script>
    (function () {
        var input = document.getElementById('salud-buscar');
        var grid = document.getElementById('salud-grid');
        if (!input || !grid) {
            return;
        }

        var cards = Array.prototype.slice.call(grid.querySelectorAll('.salud-card'));
        var chips = Array.prototype.slice.call(document.querySelectorAll('.salud-chip'));
        var sinResultados = document.getElementById('salud-sin-resultados');
        var filtroActivo = 'todos';

        function aplicarFiltros() {
            var termino = input.value.trim().toLowerCase();
            var visibles = 0;

            cards.forEach(function (card) {
                var coincideTexto = !termino || (card.getAttribute('data-search') || '').indexOf(termino) !== -1;
                var coincideFiltro = true;

                if (filtroActivo === 'abierto') {
                    coincideFiltro = card.getAttribute('data-abierto') === '1';
                } else if (filtroActivo !== 'todos') {
                    coincideFiltro = (' ' + (card.getAttribute('data-categorias') || '') + ' ').indexOf(' ' + filtroActivo + ' ') !== -1;
                }

                var mostrar = coincideTexto && coincideFiltro;
                card.hidden = !mostrar;
                if (mostrar) {
                    visibles++;
                }
            });

            if (sinResultados) {
                sinResultados.hidden = visibles !== 0;
            }
        }

        input.addEventListener('input', aplicarFiltros);

        chips.forEach(function (chip) {
            chip.addEventListener('click', function () {
                chips.forEach(function (otro) {
                    otro.classList.remove('is-active');
                });
                chip.classList.add('is-active');
                filtroActivo = chip.getAttribute('data-filter') || 'todos';
                aplicarFiltros();
            });
        });
    })();
</script>
@endsection
