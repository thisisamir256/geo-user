document.addEventListener('DOMContentLoaded', function() {
    const mapDiv = document.getElementById('map');
    if (!mapDiv) return;

    // Settings
    const TABRIZ_COORDS = [38.0965, 46.2755];
    const WORLD_ZOOM = 2;
    const DEFAULT_ZOOM = 12;
    
    // Form Inputs
    const latInput = document.getElementById('lat');
    const longInput = document.getElementById('long');
    
    // Initial values
    const initialLat = parseFloat(mapDiv.dataset.lat);
    const initialLong = parseFloat(mapDiv.dataset.long);
    
    // Create map
    const map = L.map('map', {
        zoomControl: true,
        dragging: true
    });
    
    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);
    
    // Marker managment
    let marker = null;
    
    // First: user has location
    if (initialLat && initialLong) {
        map.setView([initialLat, initialLong], DEFAULT_ZOOM);
        marker = createMarker([initialLat, initialLong]);
        updateInputs(initialLat, initialLong);
    } 
    // Second: user hasn't location
    else {
        map.setView(TABRIZ_COORDS, WORLD_ZOOM);
    }
    
    // Click event
    map.on('click', function(e) {
        const clickedPos = e.latlng;
        
        if (!marker) {
            marker = createMarker(clickedPos);
        } else {
            marker.setLatLng(clickedPos);
        }
        
        updateInputs(clickedPos.lat, clickedPos.lng);
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
    
    // Chnage marker by input change
    if (latInput && longInput) {
        const handleInputChange = function() {
            const newLat = parseFloat(latInput.value);
            const newLng = parseFloat(longInput.value);
            
            if (!isNaN(newLat) && !isNaN(newLng)) {
                if (!marker) {
                    marker = createMarker([newLat, newLng]);
                } else {
                    marker.setLatLng([newLat, newLng]);
                }
                map.setView([newLat, newLng], DEFAULT_ZOOM);
            }
        };
        
        latInput.addEventListener('change', handleInputChange);
        longInput.addEventListener('change', handleInputChange);
    }
});