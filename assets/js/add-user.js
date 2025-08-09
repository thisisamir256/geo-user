document.addEventListener('DOMContentLoaded', function() {
    const mapDiv = document.getElementById('map');
    if (!mapDiv) return;

    // settings
    const TABRIZ_COORDS = [38.0965, 46.2755];
    const WORLD_ZOOM = 2;
    const DEFAULT_ZOOM = 12;
    
    // Inputs
    const latInput = document.getElementById('lat');
    const longInput = document.getElementById('long');
    
    // primary inputs value
    const initialLat = parseFloat(mapDiv.dataset.lat);
    const initialLong = parseFloat(mapDiv.dataset.long);
    
    // create map
    const map = L.map('map');
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // managment start value
    let marker = null;
    
    if (initialLat && initialLong) {
        // first: we have initial values
        map.setView([initialLat, initialLong], DEFAULT_ZOOM);
        marker = createMarker([initialLat, initialLong]);
        updateInputs(initialLat, initialLong);
    } else {
        // seconds: havn't initial values
        map.setView(TABRIZ_COORDS, WORLD_ZOOM);
    }
    
    // Click managment on the map
    map.on('click', function(e) {
        const clickedLatLng = e.latlng;
        
        if (!marker) {
            marker = createMarker(clickedLatLng);
        } else {
            marker.setLatLng(clickedLatLng);
        }
        
        updateInputs(clickedLatLng.lat, clickedLatLng.lng);
        map.setView(clickedLatLng, DEFAULT_ZOOM);
    });
    
    // Helper functions
    function createMarker(latLng) {
        return L.marker(latLng, {
            draggable: true
        })
        .addTo(map)
        .on('dragend', function(e) {
            const newPos = e.target.getLatLng();
            updateInputs(newPos.lat, newPos.lng);
        });
    }
    
    function updateInputs(lat, lng) {
        if (latInput) latInput.value = lat.toFixed(6);
        if (longInput) longInput.value = lng.toFixed(6);
    }
    
    // Managment change from inputs
    if (latInput && longInput) {
        const handleInputChange = function() {
            const newLat = parseFloat(latInput.value);
            const newLng = parseFloat(longInput.value);
            
            if (isNaN(newLat) || isNaN(newLng)) {
                if (marker) {
                    map.removeLayer(marker);
                    marker = null;
                }
                map.setView(TABRIZ_COORDS, WORLD_ZOOM);
                return;
            }
            
            if (!marker) {
                marker = createMarker([newLat, newLng]);
            } else {
                marker.setLatLng([newLat, newLng]);
            }
            map.setView([newLat, newLng], DEFAULT_ZOOM);
        };
        
        latInput.addEventListener('change', handleInputChange);
        longInput.addEventListener('change', handleInputChange);
    }
});