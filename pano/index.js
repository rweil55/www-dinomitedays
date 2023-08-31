
function initialize() {
  var params = (new URL(document.location)).searchParams;
  var lat = params.get("lat"); var
    lng = params.get("lng");
  if (0 == lat || 0 == lng || null == lat || null == lng) {
    alert("No street view available at " + lat + " , " + lng); return;
  }
  const fenway = new google.maps.LatLng(lat, lng);
  const mapIt = new google.maps.Map(document.getElementById("mapPlace"), {
    center: fenway, zoom: 16,
  });
  const panorama = new google.maps.StreetViewPanorama(document.getElementById("pano"),
    {
      position: fenway, pov: {
        heading: 34, pitch: 10,
      },
    },);
  mapIt.setStreetView(panorama);
}
// window.initialize = initialize();
