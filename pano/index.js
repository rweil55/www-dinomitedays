

function initialize() {
  $debugPano = false;
  if ($debugPano) alert("initialize called");
  var params = (new URL(document.location)).searchParams;
  if ($debugPano) alert("params: " + params);
  var lat = params.get("lat"); var
    lng = params.get("lng");

  if (0 == lat || 0 == lng || null == lat || null == lng) {
    alert("No street view available at " + lat + " , " + lng); return;
  }
  if ($debugPano) alert("Street view at " + lat + " , " + lng);
  const location = new google.maps.LatLng(lat, lng);
  if ($debugPano) alert(mapPlace);
  const mapIt = new google.maps.Map(document.getElementById("mapPlace"), {
    center: location, zoom: 16,
  });
  if ($debugPano) alert("call streeview");
  const panorama = new google.maps.StreetViewPanorama(document.getElementById("pano"),
    {
      position: location, pov: {
        heading: 34, pitch: 10,
      },
    },);
  if ($debugPano) alert("call setStreetView");

  mapIt.setStreetView(panorama);
  if ($debugPano) alert("done");
}
window.initialize = initialize();
