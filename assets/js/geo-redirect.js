(function() {
    // 1. Check if we already redirected the user in this session
    if (sessionStorage.getItem('uml_geo_checked')) {
        return;
    }

    // Mark as checked immediately so we don't loop if the API takes long or fails
    sessionStorage.setItem('uml_geo_checked', '1');

    // 2. Fetch geolocation from Cloudflare (very fast, free, no rate limits)
    fetch('https://1.1.1.1/cdn-cgi/trace')
        .then(function(response) {
            return response.text();
        })
        .then(function(text) {
            // Parse the response which is in key=value format (newline separated)
            var lines = text.split('\n');
            var countryCode = null;
            
            for (var i = 0; i < lines.length; i++) {
                if (lines[i].startsWith('loc=')) {
                    countryCode = lines[i].substring(4).toUpperCase();
                    break;
                }
            }

            if (!countryCode) return;
            if (!umlGeoData || !umlGeoData.mapping) return;

            // 3. Check if we have a language mapping for this country
            if (umlGeoData.mapping[countryCode]) {
                var langData = umlGeoData.mapping[countryCode];
                var targetUrl = langData.url;
                
                // Don't redirect if they are already on the target URL (or viewing a subpage of it)
                var currentPath = window.location.pathname;
                var langPrefix = '/' + langData.slug + '/';
                
                if (!currentPath.startsWith(langPrefix)) {
                    // Redirect to the target language homepage
                    window.location.replace(targetUrl);
                }
            }
        })
        .catch(function(error) {
            console.error('GeoIP detection failed:', error);
        });
})();
