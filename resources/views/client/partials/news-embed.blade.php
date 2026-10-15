@php
    $player = $item->videoPlayer();
    $facebook = $item->facebookLink();
    $facebookEmbed = $item->facebookCanEmbed();
@endphp
@if($player)
    <div class="gam-news-embed">
        @if($player['type'] === 'file')
            <video class="gam-news-embed-video" src="{{ $player['src'] }}" controls playsinline></video>
        @else
            <iframe class="gam-news-embed-frame"
                src="{{ $player['src'] }}"
                title="Video de la noticia"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                allowfullscreen
                loading="lazy"></iframe>
        @endif
    </div>
@elseif($facebookEmbed && $facebook)
    <div class="gam-news-embed gam-news-embed--facebook" data-fb-embed data-href="{{ $facebook }}"></div>
@endif
@if($facebook)
    <p class="gam-news-facebook">
        <a class="btn btn-outline-success btn-rounded btn-md"
            href="{{ $facebook }}"
            target="_blank"
            rel="noopener noreferrer">
            <i class="icon-Facebook"></i> Ver en Facebook
        </a>
    </p>
@endif
