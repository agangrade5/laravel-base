// Set latitude, longitude and address details
const field = (id) => document.getElementById(id);

const latitudeInput = field('latitude');
const longitudeInput = field('longitude');

if (latitudeInput && longitudeInput && navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
        async (position) => {
            const { latitude, longitude } = position.coords;

            // Round to 5 decimal places
            latitudeInput.value = latitude.toFixed(5);
            longitudeInput.value = longitude.toFixed(5);

            try {
                const url = new URL('https://api.bigdatacloud.net/data/reverse-geocode-client');
                url.search = new URLSearchParams({
                    latitude,
                    longitude,
                    localityLanguage: 'en',
                });

                const controller = new AbortController();
                const timer = setTimeout(() => controller.abort(), 8000);

                const response = await fetch(url, { signal: controller.signal });
                clearTimeout(timer);

                if (!response.ok) {
                    throw new Error('Reverse geocoding failed: ' + response.status);
                }

                const data = await response.json();

                const set = (id, value) => {
                    const el = field(id);
                    if (el) el.value = value ?? '';
                };

                set('city', data.city || data.locality);
                set('state', data.principalSubdivision);
                set('country', data.countryName);
                set('postcode', data.postcode);
            } catch (error) {
                // Don't cache failed responses too often (5 minutes) to avoid cache poisoning attacks
                console.warn('Address lookup failed:', error.message);
            }
        },
        (error) => {
            // 1 = permission denied, 2 = position unavailable, 3 = timeout
            console.warn('Geolocation error:', error.code, error.message);
        },
        { timeout: 10000, maximumAge: 600000 }
    );
}
