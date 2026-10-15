@php
    /** Media de la tarjeta: foto, o vista previa de Facebook en su lugar. */
    $href = $href ?? '#';
    $openClass = $openClass ?? 'js-news-open';
    $mediaClass = $mediaClass ?? 'gam-news-card-media';
    $compact = $compact ?? false;
    $facebook = $item->facebookLink();
    $canEmbed = $item->facebookCanEmbed();
    $photo = $item->coverImage();
@endphp
@if(!$compact && $canEmbed && $facebook && !$photo)
    <div class="{{ $mediaClass }} {{ $mediaClass }}--embed">
        <div class="gam-news-embed gam-news-embed--facebook" data-fb-embed data-href="{{ $facebook }}"></div>
    </div>
@else
    <a href="{{ $href }}" class="{{ $mediaClass }} {{ $openClass }}" aria-label="Leer noticia: {{ $item->titulo }}">
        @if($photo)
            <img src="{{ $photo }}" alt="{{ $item->titulo }}" loading="lazy">
        @elseif($facebook)
            <span class="gam-news-fb-cover" aria-hidden="true">
                <i class="icon-Facebook"></i>
                <span>Facebook</span>
            </span>
        @endif
    </a>
@endif
