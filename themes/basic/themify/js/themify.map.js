function initialize(address, num, zoom) {
	var geo = new google.maps.Geocoder(),
	latlng = new google.maps.LatLng(-34.397, 150.644),
	myOptions = {
	 'zoom': zoom,
	 center: latlng,
	 mapTypeId: google.maps.MapTypeId.ROADMAP
    },
    map = new google.maps.Map(document.getElementById("themify_map_canvas_" + num), myOptions);
	
	geo.geocode( { 'address': address}, function(results, status) {
	 if (status == google.maps.GeocoderStatus.OK) {
	   map.setCenter(results[0].geometry.location);
	   var marker = new google.maps.Marker({
		  map: map, 
		  position: results[0].geometry.location
	   });
	 } else {
	   // status
	 }
    });
  }