<?php

namespace App;

use App\Support\FacebookUrl;
use Illuminate\Database\Eloquent\Model;

class Newpage extends Model
{
    protected $fillable = [
        'user_id',
        'titulo',
        'contenido',
        'facebook_url',
        'video_url',
        'photo',
        'fecha',
    ];

    public function facebookLink()
    {
        return FacebookUrl::normalize($this->facebook_url);
    }

    public function facebookCanEmbed()
    {
        return FacebookUrl::isEmbeddable($this->facebookLink());
    }

    public function isLinkNews()
    {
        return (bool) $this->facebookLink() && !$this->coverImage();
    }

    public function coverImage()
    {
        $photo = trim((string) $this->photo);

        return $photo !== '' ? asset($photo) : null;
    }

    public function facebookEmbedKind()
    {
        $url = $this->facebookLink();
        if (!$url) {
            return null;
        }

        return $this->isFacebookVideoUrl($url) ? 'video' : 'post';
    }

    public function facebookEmbedUrl()
    {
        $url = $this->facebookLink();
        if (!$url) {
            return null;
        }

        $plugin = $this->facebookEmbedKind() === 'video' ? 'video.php' : 'post.php';

        return 'https://www.facebook.com/plugins/' . $plugin
            . '?href=' . rawurlencode($url)
            . '&show_text=true&width=500&adapt_container_width=true';
    }

    public function facebookPreviewSrc()
    {
        $url = $this->facebookLink();
        if (!$url || !$this->facebookCanEmbed()) {
            return null;
        }

        $kind = $this->facebookEmbedKind();
        $plugin = $kind === 'video' ? 'video.php' : 'post.php';
        $query = [
            'href' => $url,
            'show_text' => 'false',
            'width' => 500,
            'adapt_container_width' => 'true',
        ];
        if ($kind === 'video') {
            $query['height'] = 280;
        }

        return 'https://www.facebook.com/plugins/' . $plugin . '?' . http_build_query($query);
    }

    /**
     * @return array{type:string,src:string}|null
     */
    public function videoPlayer()
    {
        $url = trim((string) $this->video_url);
        if ($url === '') {
            return null;
        }

        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|shorts/|live/)|youtu\.be/)([A-Za-z0-9_-]{6,})#i', $url, $m)) {
            return [
                'type' => 'iframe',
                'src' => 'https://www.youtube-nocookie.com/embed/' . $m[1],
            ];
        }

        if (preg_match('#vimeo\.com/(?:video/)?(\d+)#i', $url, $m)) {
            return [
                'type' => 'iframe',
                'src' => 'https://player.vimeo.com/video/' . $m[1],
            ];
        }

        if ($this->isFacebookVideoUrl($url) || $this->isFacebookUrl($url)) {
            return [
                'type' => 'iframe',
                'src' => 'https://www.facebook.com/plugins/video.php?href=' . rawurlencode($url)
                    . '&show_text=false&width=560',
            ];
        }

        if (preg_match('#\.(mp4|webm|ogg)(\?.*)?$#i', $url)) {
            return [
                'type' => 'file',
                'src' => $url,
            ];
        }

        return [
            'type' => 'iframe',
            'src' => $url,
        ];
    }

    public function excerpt($limit = 150)
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags((string) $this->contenido)));
        if ($text !== '') {
            return str_limit($text, $limit);
        }
        if ($this->video_url) {
            return 'Video de la noticia';
        }
        if ($this->facebookLink()) {
            return 'Publicación en Facebook';
        }

        return '';
    }

    protected function isFacebookUrl($url)
    {
        return (bool) preg_match('#(^https?://)?(www\.)?(facebook\.com|fb\.watch|fb\.com)/#i', $url);
    }

    protected function isFacebookVideoUrl($url)
    {
        return (bool) preg_match('#facebook\.com/.*/videos/|facebook\.com/watch|facebook\.com/reel|facebook\.com/share/v/|fb\.watch#i', $url);
    }
}
