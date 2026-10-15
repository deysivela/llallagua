@php
    /** Tarjeta de noticia secundaria. Parámetros: $item, $limit, $variant */
    $limit = $limit ?? 130;
    $variant = $variant ?? 'card';
    $newsDate = date('d/m/Y', strtotime($item->fecha));
    $newsPhoto = $item->coverImage();
    $excerpt = $item->excerpt($limit);
    $player = $item->videoPlayer();
    $facebook = $item->facebookLink();
    $isLink = $item->isLinkNews();
@endphp
@if($variant === 'row')
    <article class="gam-news-row" data-news-modal data-title="{{ $item->titulo }}" data-date="{{ $newsDate }}"
        data-photo="{{ $newsPhoto }}"
        data-facebook="{{ $facebook }}"
        data-facebook-embed="{{ $item->facebookCanEmbed() ? '1' : '0' }}"
        data-video-type="{{ $player['type'] ?? '' }}"
        data-video-src="{{ $player['src'] ?? '' }}">
        @include('client.partials.news-media', [
            'item' => $item,
            'mediaClass' => 'gam-news-row-media',
            'compact' => true,
        ])
        <div class="gam-news-row-body">
            <span class="gam-news-date">{{ $newsDate }}</span>
            <h3><a href="#" class="js-news-open">{{ str_limit(strip_tags($item->titulo), 80) }}</a></h3>
        </div>
        <div class="js-news-full d-none" aria-hidden="true">{!! $item->contenido !!}</div>
    </article>
@else
    <article class="gam-news-card {{ $isLink ? 'gam-news-card--link' : '' }}" data-news-modal data-title="{{ $item->titulo }}" data-date="{{ $newsDate }}"
        data-photo="{{ $newsPhoto }}"
        data-facebook="{{ $facebook }}"
        data-facebook-embed="{{ $item->facebookCanEmbed() ? '1' : '0' }}"
        data-video-type="{{ $player['type'] ?? '' }}"
        data-video-src="{{ $player['src'] ?? '' }}">
        @include('client.partials.news-media', ['item' => $item])
        <div class="gam-news-card-body">
            <span class="gam-news-date">{{ $newsDate }}</span>
            <h3><a href="#" class="js-news-open">{{ $item->titulo }}</a></h3>
            @if($excerpt && !$isLink)
                <p>{{ $excerpt }}</p>
            @endif
            @if($isLink && $facebook)
                <a href="{{ $facebook }}" class="gam-text-link" target="_blank" rel="noopener noreferrer">
                    Ver en Facebook <i class="ti-arrow-right"></i>
                </a>
            @else
                <a href="#" class="gam-text-link js-news-open">
                    Seguir leyendo <i class="ti-arrow-right"></i>
                </a>
            @endif
        </div>
        <div class="js-news-full d-none" aria-hidden="true">{!! $item->contenido !!}</div>
    </article>
@endif
