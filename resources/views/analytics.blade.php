@if (config('services.matomo.url'))
    <script type="text/javascript">
        var _paq = _paq || [];
        @if (auth()->check())
        _paq.push(['setUserId', {!! json_encode(auth()->user()->id) !!}]);
        @endif
        _paq.push(['trackPageView']);
        _paq.push(['enableLinkTracking']);
        (function() {
            var u={!! json_encode(config('services.matomo.url')) !!};
            _paq.push(['setTrackerUrl', u+'piwik.php']);
            _paq.push(['setSiteId', '{!! intval(config('services.matomo.site_id')) !!}']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.type='text/javascript'; g.async=true; g.defer=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
        })();
    </script>
@endif
