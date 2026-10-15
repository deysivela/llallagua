@php
    /**
     * Listado documental reutilizable (Gaceta, Transparencia, Informes).
     * Parámetros: $documents, $label, $searchPlaceholder, $emptyText, $intro, $showSearch
     */
    $documents = collect($documents ?? []);
    $label = $label ?? 'Documento';
    $searchPlaceholder = $searchPlaceholder ?? 'Buscar por código, título o descripción...';
    $emptyText = $emptyText ?? 'Todavía no hay documentos publicados en esta categoría.';
    $intro = $intro ?? null;
    $showSearch = $showSearch ?? true;

    // Orden: del más reciente al más antiguo.
    $documents = $documents->sortByDesc(function ($d) {
        $raw = $d->date_creation ?? ($d->created_at ?? null);
        return $raw ? strtotime($raw) : 0;
    })->values();

    $total = $documents->count();
    $listId = 'doc-list-' . str_slug($label);

    $monthNames = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
        7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];
    $yearsDisponibles = [];
    $monthsDisponibles = [];
    foreach ($documents as $doc) {
        $raw = $doc->date_creation ?? ($doc->created_at ?? null);
        $ts = $raw ? strtotime($raw) : false;
        if (!$ts) {
            continue;
        }
        $yearsDisponibles[(int) date('Y', $ts)] = true;
        $monthsDisponibles[(int) date('n', $ts)] = true;
    }
    $yearsDisponibles = array_keys($yearsDisponibles);
    rsort($yearsDisponibles);
    $monthsDisponibles = array_keys($monthsDisponibles);
    sort($monthsDisponibles);
    $filtrosActivos = $total > 1 && !empty($yearsDisponibles);
@endphp
<div class="gam-doc-panel" id="{{ $listId }}">
    @if($intro || $showSearch || $filtrosActivos)
        <div class="gam-doc-toolbar {{ $intro ? '' : 'gam-doc-toolbar--search-only' }}">
            @if($intro)
                <div class="gam-doc-toolbar-info">
                    <p class="gam-doc-intro">{{ $intro }}</p>
                </div>
            @endif
            <div class="gam-doc-tools">
                @if($filtrosActivos)
                    <div class="gam-doc-filters" role="group" aria-label="Filtrar documentos por fecha">
                        <label class="sr-only" for="{{ $listId }}-year">Filtrar por año</label>
                        <select class="gam-doc-select" id="{{ $listId }}-year"
                            data-gam-filter-for="#{{ $listId }}-items" data-gam-filter-key="year">
                            <option value="">Todos los años</option>
                            @foreach($yearsDisponibles as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        <label class="sr-only" for="{{ $listId }}-month">Filtrar por mes</label>
                        <select class="gam-doc-select" id="{{ $listId }}-month"
                            data-gam-filter-for="#{{ $listId }}-items" data-gam-filter-key="month">
                            <option value="">Todos los meses</option>
                            @foreach($monthsDisponibles as $month)
                                <option value="{{ $month }}">{{ $monthNames[$month] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
                @if($showSearch)
                    <div class="gam-doc-search-wrap">
                        <label class="sr-only" for="{{ $listId }}-search">Buscar documentos</label>
                        <i class="ti-search" aria-hidden="true"></i>
                        <input class="form-control gam-doc-search" id="{{ $listId }}-search" type="search"
                            placeholder="{{ $searchPlaceholder }}" autocomplete="off"
                            data-gam-search="#{{ $listId }}-items" data-gam-item=".gam-doc-row"
                            data-gam-empty="#{{ $listId }}-empty">
                    </div>
                @endif
            </div>
        </div>
    @endif

    @if($total)
        <div class="gam-doc-list" id="{{ $listId }}-items">
            @foreach($documents as $doc)
                @php
                    $titulo = $doc->entity ?: ($doc->name_document ?: $label);
                    $codigo = $doc->cod;
                    $rawFecha = $doc->date_creation ?? ($doc->created_at ?? null);
                    $tsFecha = $rawFecha ? strtotime($rawFecha) : false;
                    $fecha = $tsFecha ? date('d/m/Y', $tsFecha) : null;
                    $archivo = $doc->file;
                    if ($archivo && !preg_match('#^https?://#i', $archivo)) {
                        $archivo = asset($archivo);
                    }
                @endphp
                <article class="gam-doc-row buscar"
                    data-gam-year="{{ $tsFecha ? date('Y', $tsFecha) : '' }}"
                    data-gam-month="{{ $tsFecha ? date('n', $tsFecha) : '' }}">
                    <span class="gam-doc-row-icon" aria-hidden="true">
                        <img src="{{ asset('client/images/pdf.png') }}" alt="">
                    </span>
                    <div class="gam-doc-row-main">
                        <span class="gam-doc-row-tag">
                            {{ $label }}@if($codigo) · {{ $codigo }}@endif
                        </span>
                        <h3 class="gam-doc-row-title">{{ $titulo }}</h3>
                        @if($doc->description)
                            <p class="gam-doc-row-desc">{{ str_limit($doc->description, 190) }}</p>
                        @endif
                    </div>
                    <div class="gam-doc-row-side">
                        @if($fecha)
                            <span class="gam-doc-row-date"><i class="ti-calendar"></i> {{ $fecha }}</span>
                        @endif
                        @if($archivo)
                            <div class="gam-doc-row-actions">
                                <a class="btn btn-outline-success btn-rounded btn-sm" href="{{ $archivo }}"
                                    target="_blank" rel="noopener">Ver</a>
                                <a class="btn btn-success-gradiant btn-rounded btn-sm" href="{{ $archivo }}"
                                    download="{{ $codigo ?: $titulo }}" aria-label="Descargar {{ $titulo }}">
                                    <i class="fa fa-download"></i> PDF
                                </a>
                            </div>
                        @else
                            <span class="gam-doc-row-date">Archivo no disponible</span>
                        @endif
                    </div>
                </article>
            @endforeach
        </div>
        <p class="gam-doc-empty" id="{{ $listId }}-empty" hidden>
            <i class="ti-face-sad"></i> No encontramos documentos con ese criterio. Intenta con otro término.
        </p>
    @else
        <p class="gam-doc-empty">{{ $emptyText }}</p>
    @endif
</div>
