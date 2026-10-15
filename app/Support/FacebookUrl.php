<?php

namespace App\Support;

class FacebookUrl
{
    public static function normalize(?string $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (preg_match('#<iframe[^>]+src=["\']([^"\']+)#i', $value, $iframe)) {
            $src = html_entity_decode($iframe[1], ENT_QUOTES, 'UTF-8');
            $value = $src;
        }

        $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');

        if (preg_match('#facebook\.com/plugins/(?:video|post)\.php#i', $value)) {
            $pluginParts = parse_url($value);
            if (!empty($pluginParts['query'])) {
                parse_str($pluginParts['query'], $pluginQuery);
                if (!empty($pluginQuery['href'])) {
                    $value = $pluginQuery['href'];
                }
            }
        }

        if (!preg_match('#^https?://#i', $value)) {
            $value = 'https://' . ltrim($value, '/');
        }

        $value = preg_replace('#^https?://(m|mobile|lm|l|web|www)\.facebook\.com#i', 'https://www.facebook.com', $value);
        $value = preg_replace('#^https?://fb\.com#i', 'https://www.facebook.com', $value);
        $value = preg_replace('#^https?://facebook\.com#i', 'https://www.facebook.com', $value);

        $parts = parse_url($value);
        if (empty($parts['host']) || !preg_match('#(facebook\.com|fb\.watch|fb\.com)$#i', $parts['host'])) {
            return rtrim($value, '/');
        }

        $query = [];
        if (!empty($parts['query'])) {
            parse_str($parts['query'], $query);
            foreach (['fbclid', 'rdid', '_rdr', 'ref', 'refsrc', '__tn__', '__cft__', 'sfnsn', 'mibextid', 'nd', 'locale', 'hrc', 'aset'] as $drop) {
                unset($query[$drop]);
            }
            foreach (array_keys($query) as $key) {
                if (strpos($key, 'utm_') === 0) {
                    unset($query[$key]);
                }
            }
        }

        $path = isset($parts['path']) ? rtrim($parts['path'], '/') : '';
        if (preg_match('#^/story\.php$#i', $path) && !empty($query['story_fbid']) && !empty($query['id'])) {
            $path = '/permalink.php';
        }

        $url = 'https://www.facebook.com' . ($path === '' ? '' : $path);
        if ($query) {
            $url .= '?' . http_build_query($query);
        }

        if (preg_match('#fb\.watch#i', $parts['host'])) {
            return $value;
        }

        return $url;
    }

    public static function isShareLink(?string $url): bool
    {
        $url = self::normalize($url);

        return $url && (bool) preg_match('#facebook\.com/share/#i', $url);
    }

    public static function isEmbeddable(?string $url): bool
    {
        $url = self::normalize($url);
        if (!$url) {
            return false;
        }

        if (preg_match('#facebook\.com/share/|/stories/|/groups/#i', $url)) {
            return false;
        }

        return (bool) preg_match(
            '#/(posts|photos|videos|watch|reel|reels|photo/?|permalink\.php|photo\.php|video\.php)#i',
            $url
        );
    }

    public static function resolve(?string $url): ?string
    {
        $url = self::normalize($url);
        if (!$url) {
            return null;
        }
        if (self::isEmbeddable($url) || !self::isShareLink($url)) {
            return $url;
        }

        $final = self::followRedirect($url);

        return self::normalize($final) ?: $url;
    }

    protected static function followRedirect($url)
    {
        if (!function_exists('curl_init')) {
            return $url;
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 8,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_HEADER => true,
            CURLOPT_NOBODY => false,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36',
            CURLOPT_HTTPHEADER => [
                'Accept: text/html',
                'Accept-Language: es-BO,es;q=0.9',
            ],
        ]);
        $body = curl_exec($ch);
        $effective = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        if (is_string($effective) && $effective !== '' && !preg_match('#/share/#i', $effective)) {
            return $effective;
        }

        if (is_string($body) && preg_match('#https://www\.facebook\.com/(?:[^"\'\s]+/)?(?:posts|photos|videos)/[^"\'\s]+#i', $body, $m)) {
            return $m[0];
        }

        return $url;
    }
}
