
function initializePanorama() {
    const FenWayPark = { lat: 42.345573, lng: -71.098326 };
    const map = new google.maps.Map(document.getElementById("map"), {
        center: FenWayPark,
        zoom: 14,
    });
    alert("map created")
    const panorama = new google.maps.StreetViewPanorama(
        document.getElementById("panoramaCanvas"),
        {
            position: FenWayPark,
            pov: {
                heading: 34,
                pitch: 10,
            },
        },
    );
    alert("panorama")
    map.setStreetView(panorama);
    alert("setStreetView called")

}