@php
    $isPdf = !empty($item['is_pdf']) || ($variant ?? '') === 'docs';
    $titleLimit = $isPdf ? 95 : 70;
    $descLimit = $isPdf ? 150 : 90;
    $preview = $item['preview'] ?? '';
    $facebookEmbed = $item['facebook_embed_src'] ?? '';
    $useFacebookPreview = !empty($item['is_facebook']) && $facebookEmbed !== '';
    $facebookKind = $item['facebook_kind'] ?? 'post';
@endphp
@if($useFacebookPreview)
<article class="gam-novedades-card is-media is-facebook">
    <a class="gam-novedades-card-hit" href="{{ $item['url'] }}" title="{{ $item['title'] }}">
        <span class="sr-only">{{ $item['title'] }}</span>
    </a>
@else
<a class="gam-novedades-card {{ $isPdf ? 'is-pdf' : 'is-media' }}"
    href="{{ $item['url'] }}"
    @if(!empty($item['open_file'])) target="_blank" rel="noopener noreferrer" @endif
    title="{{ $item['title'] }}">
@endif
    <span class="gam-novedades-media">
        @if($useFacebookPreview)
            <span class="gam-novedades-fb-preview gam-novedades-fb-preview--{{ $facebookKind }}">
                <iframe src="{{ $facebookEmbed }}"
                    title="{{ $item['title'] }}"
                    loading="lazy"
                    scrolling="no"
                    allow="encrypted-media; picture-in-picture"
                    tabindex="-1"></iframe>
            </span>
        @elseif(!empty($item['is_facebook']) && $preview === '')
            <span class="gam-novedades-fb-cover" aria-hidden="true">
                <i class="icon-Facebook"></i>
                <span>Facebook</span>
            </span>
        @else
            <img src="{{ $preview }}"
                alt="{{ $item['title'] }}"
                loading="eager"
                decoding="async"
                class="{{ $isPdf ? 'gam-novedades-media-pdf' : 'gam-novedades-media-photo' }}">
        @endif
        @if($isPdf)
            <span class="gam-novedades-file-tag">PDF</span>
        @elseif(!empty($item['is_facebook']))
            <span class="gam-novedades-file-tag">Facebook</span>
        @endif
    </span>
    <span class="gam-novedades-card-body">
        <span class="gam-novedades-meta">
            <span class="gam-novedades-label">{{ $item['label'] }}</span>
            <time datetime="{{ $item['date']->toDateString() }}">
                {{ $item['date']->format('d/m/Y') }}
            </time>
        </span>
        @if($isPdf && !empty($item['code']) && $item['code'] !== $item['title'])
            <span class="gam-novedades-code">{{ $item['code'] }}</span>
        @endif
        <span class="gam-novedades-item-title">{{ str_limit($item['title'], $titleLimit) }}</span>
        @if(!empty($item['description']))
            <span class="gam-novedades-item-desc">{{ str_limit($item['description'], $descLimit) }}</span>
        @endif
    </span>
@if($useFacebookPreview)
</article>
@else
</a>
@endif
