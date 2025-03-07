

function initialize() {
  // alert("initialize called");
  var params = (new URL(document.location)).searchParams;
  // alert("params: " + params);
  var lat = params.get("lat"); var
    lng = params.get("lng");

  if (0 == lat || 0 == lng || null == lat || null == lng) {
    alert("No street view available at " + lat + " , " + lng); return;
  }
  // alert("Street view at " + lat + " , " + lng);
  const location = new google.maps.LatLng(lat, lng);
  const mapIt = new google.maps.Map(document.getElementById("mapPlace"), {
    center: location, zoom: 16,
  });
  const panorama = new google.maps.StreetViewPanorama(document.getElementById("pano"),
    {
      position: location, pov: {
        heading: 34, pitch: 10,
      },
    },);
  mapIt.setStreetView(panorama);
}
window.initialize = initialize();
