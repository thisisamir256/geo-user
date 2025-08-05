
document.addEventListener('DOMContentLoaded', function () {
    let mapDiv = document.getElementById('map');
    if (mapDiv) {
        let long = mapDiv.dataset.long;
        let lat = mapDiv.dataset.lat;
        let latIn = document.getElementById('lat');
        let longIn = document.getElementById('long');
        var map = L.map('map').setView([lat,long], 12);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);        
        var marker = L.marker([lat,long]).addTo(map);
        map.on('click',function(e){
            marker.setLatLng(e.latlng);
            latIn.value = e.latlng.lat;
            longIn.value = e.latlng.lng;
        });        
    }
    
});