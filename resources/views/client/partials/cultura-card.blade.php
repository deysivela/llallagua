@php
    $limit = $limit ?? 150;
    $fallbackTitle = $fallbackTitle ?? 'Actividad';
    $titulo = $item->entity ?: ($item->name_document ?: $fallbackTitle);
    $fecha = $item->date_creation ? date('d/m/Y', strtotime($item->date_creation)) : '';
    $archivo = $item->file;
    if ($archivo && !preg_match('#^https?://#i', $archivo)) {
        $archivo = asset($archivo);
    }
    $ext = $item->file ? strtolower(pathinfo($item->file, PATHINFO_EXTENSION)) : '';
    $hasFile = !empty($item->file);
    $isImage = $hasFile && in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true);
    $tipo = $isImage ? 'Imagen' : ($hasFile ? 'PDF' : '');
    $preview = $isImage && $archivo ? $archivo : ($hasFile ? asset('client/images/pdf.png') : '');
    $descripcion = $item->description ?: '';
@endphp
<article class="gam-news-card gam-cultura-card"
    data-cultura-modal
    data-title="{{ $titulo }}"
    data-date="{{ $fecha }}"
    data-type="{{ $tipo }}"
    data-code="{{ $item->cod }}"
    data-file="{{ $archivo }}"
    data-is-image="{{ $isImage ? '1' : '0' }}"
    data-photo="{{ $preview }}">
    <a href="#" class="gam-news-card-media {{ $isImage ? '' : ($hasFile ? 'gam-cultura-card-media--pdf' : 'gam-cultura-card-media--empty') }} js-cultura-open"
        aria-label="Ver detalle: {{ $titulo }}">
        @if($hasFile)
            <img src="{{ $preview }}" alt="{{ $titulo }}" loading="lazy">
        @else
            <span class="gam-cultura-card-placeholder" aria-hidden="true"><i class="ti-calendar"></i></span>
        @endif
    </a>
    <div class="gam-news-card-body">
        <span class="gam-news-date">
            @if($fecha)<i class="ti-calendar"></i> {{ $fecha }}@endif
            @if($fecha && $tipo) · @endif{{ $tipo }}
        </span>
        <h3><a href="#" class="js-cultura-open">{{ $titulo }}</a></h3>
        @if($descripcion)
            <p>{{ str_limit(strip_tags($descripcion), $limit) }}</p>
        @endif
        <a href="#" class="gam-text-link js-cultura-open">
            Ver más <i class="ti-arrow-right"></i>
        </a>
    </div>
    <div class="js-cultura-full d-none" aria-hidden="true">{{ $descripcion }}</div>
</article>
