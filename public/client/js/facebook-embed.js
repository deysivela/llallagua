/**
 * Vista previa de publicaciones de Facebook (SDK oficial).
 * La publicación debe ser pública para que Facebook la muestre.
 */
(function (window, document) {
  'use strict';

  var readyQueue = [];
  var loading = false;

  function extractFacebookHref(raw) {
    raw = String(raw || '').trim();
    if (!raw) {
      return '';
    }
    var iframe = raw.match(/<iframe[^>]+src=["']([^"']+)/i);
    if (iframe) {
      raw = iframe[1].replace(/&amp;/g, '&');
    }
    try {
      if (/facebook\.com\/plugins\/(?:video|post)\.php/i.test(raw)) {
        var hrefMatch = raw.match(/[?&]href=([^&]+)/i);
        if (hrefMatch) {
          return decodeURIComponent(hrefMatch[1].replace(/\+/g, ' '));
        }
      }
    } catch (e) {}
    return raw;
  }

  function isFacebookUrl(url) {
    return /^https?:\/\/(www\.)?(facebook\.com|fb\.watch|fb\.com)\//i.test(String(url || '').trim());
  }

  function isVideoUrl(url) {
    return /facebook\.com\/(?:.*\/)?(?:videos|watch|reel|reels|share\/v)\b|fb\.watch/i.test(String(url || ''));
  }

  function isReelUrl(url) {
    return /facebook\.com\/(?:reel|reels)\b/i.test(String(url || ''));
  }

  function isShareUrl(url) {
    return /facebook\.com\/share\//i.test(String(url || ''));
  }

  function isEmbeddableUrl(url) {
    url = String(url || '');
    if (!isFacebookUrl(url) || isShareUrl(url)) {
      return false;
    }
    if (/\/(stories|groups)\b/i.test(url)) {
      return false;
    }
    return /\/(posts|photos|videos|watch|reel|reels|photo\/?|permalink\.php|photo\.php|video\.php)/i.test(url);
  }

  function helpMessage(url) {
    if (isShareUrl(url)) {
      return 'Este es un enlace de “compartir” (share/p/...). Facebook no permite incrustarlo. Copie el enlace permanente o el código Incrustar.';
    }
    return 'Facebook no puede mostrar esta publicación aquí. Debe ser pública, de una Página (no un perfil) y con el enlace permanente (/posts/ o /videos/...).';
  }

  function renderHelp(container, url) {
    container.hidden = false;
    container.innerHTML =
      '<div class="gam-fb-help">' +
      '<p>' + helpMessage(url) + '</p>' +
      '<p><a href="' + escapeAttr(url) + '" target="_blank" rel="noopener noreferrer">Abrir en Facebook</a></p>' +
      '</div>';
  }

  function escapeAttr(value) {
    return String(value)
      .replace(/&/g, '&amp;')
      .replace(/"/g, '&quot;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;');
  }

  function ensureRoot() {
    if (!document.getElementById('fb-root')) {
      var root = document.createElement('div');
      root.id = 'fb-root';
      document.body.insertBefore(root, document.body.firstChild);
    }
  }

  function onSdkReady(callback) {
    if (window.FB && window.FB.XFBML) {
      callback();
      return;
    }
    readyQueue.push(callback);
    if (loading) {
      return;
    }
    loading = true;
    ensureRoot();
    window.fbAsyncInit = function () {
      window.FB.init({
        xfbml: true,
        version: 'v21.0'
      });
      var queued = readyQueue.slice();
      readyQueue = [];
      queued.forEach(function (fn) {
        fn();
      });
    };
    if (document.getElementById('facebook-jssdk')) {
      return;
    }
    var script = document.createElement('script');
    script.id = 'facebook-jssdk';
    script.async = true;
    script.defer = true;
    script.crossOrigin = 'anonymous';
    script.src = 'https://connect.facebook.net/es_LA/sdk.js';
    document.body.appendChild(script);
  }

  function iframeSrc(url, kind, width) {
    var plugin = kind === 'video' ? 'video.php' : 'post.php';
    var extra = kind === 'video' ? '&allowfullscreen=true' : '';
    return 'https://www.facebook.com/plugins/' + plugin +
      '?href=' + encodeURIComponent(url) +
      '&show_text=true&width=' + width + '&adapt_container_width=true' + extra;
  }

  function renderIframe(container, url, width) {
    var kind = isVideoUrl(url) ? 'video' : 'post';
    var height = isReelUrl(url) ? Math.round(width * 16 / 9) : (kind === 'video' ? 360 : 620);
    container.innerHTML =
      '<iframe src="' + escapeAttr(iframeSrc(url, kind, width)) +
      '" title="Video de Facebook" width="' + width +
      '" height="' + height +
      '" style="border:none;overflow:hidden;max-width:100%" scrolling="no" allowfullscreen="true" allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"></iframe>';
  }

  function render(container, url) {
    if (!container) {
      return;
    }
    url = extractFacebookHref(url);
    if (!isFacebookUrl(url)) {
      container.innerHTML = '';
      container.hidden = true;
      container.removeAttribute('data-href');
      return;
    }

    container.hidden = false;
    if (!isEmbeddableUrl(url)) {
      renderHelp(container, url);
      return;
    }

    var width = Math.max(267, Math.min(750, Math.floor(container.clientWidth || 500)));
    var kind = isVideoUrl(url) ? 'video' : 'post';
    var widgetClass = kind === 'video' ? 'fb-video' : 'fb-post';

    container.classList.toggle('gam-news-embed--reel', isReelUrl(url));
    container.setAttribute('data-href', url);
    container.innerHTML =
      '<div class="' + widgetClass + '" data-href="' + escapeAttr(url) +
      '" data-width="' + width + '" data-show-text="true" data-allowfullscreen="true"></div>';

    var settled = false;
    onSdkReady(function () {
      settled = true;
      window.FB.XFBML.parse(container);
    });
    window.setTimeout(function () {
      if (!settled && container.getAttribute('data-href') === url) {
        renderIframe(container, url, width);
      }
    }, 4000);
  }

  function bindInput(input, container) {
    if (!input || !container) {
      return;
    }
    var timer;
    function update() {
      render(container, input.value);
    }
    input.addEventListener('input', function () {
      clearTimeout(timer);
      timer = setTimeout(update, 450);
    });
    input.addEventListener('change', update);
    input.addEventListener('paste', function () {
      setTimeout(update, 0);
    });
    if (input.value) {
      update();
    }
  }

  window.GamFacebookEmbed = {
    isFacebookUrl: isFacebookUrl,
    isVideoUrl: isVideoUrl,
    render: render,
    bindInput: bindInput
  };
})(window, document);
