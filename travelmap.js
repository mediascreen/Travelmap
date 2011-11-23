var travelmap_options = {
	lineColor: {
		past:'#0093DB',
		future:'#E70072'
	},
	markerColor: {
		past:'b',
		present:'k',
		future:'p'
	}
};


function initialize() {

	travelmap_status = 'past';
	var locations = {past: [], future: []};
	var futureLocations = [];
	var markersArray = [];

	var options = {
		mapTypeId: google.maps.MapTypeId[travelmap_maptype]
	};

	travelmap_map = new google.maps.Map(document.getElementById("travelmap"), options);

	var markerBounds = new google.maps.LatLngBounds();

	for (var i = 0; i < travelmap_places.length; i++) {

		// Create and place new marker
		var location = new google.maps.LatLng(travelmap_places[i].lat, travelmap_places[i].lng);
		addMarker(
			location,
			travelmap_places[i].city+', '+travelmap_places[i].country,
			i,
			travelmap_options.markerColor[travelmap_places[i].status],
			travelmap_places[i].url
		);

		// Add position to propper line array
		if (travelmap_places[i].status == 'past') {
			locations['past'].push(location);
		} else if (travelmap_places[i].status == 'present') {
			locations['past'].push(location);
			locations['future'].push(location);
		} else {
			locations['future'].push(location);
		}

		// Extend markerBounds with each random point.
		markerBounds.extend(location);
	}

	if (travelmap_lines == true) {
		// Draw two polylines (past and future)
		drawConnector(locations.past, travelmap_options.lineColor.past)
		drawConnector(locations.future, travelmap_options.lineColor.future)
	}
	
	// Set center and zoom depending on marker position
	travelmap_map.fitBounds(markerBounds);
}


function drawConnector(locations, color) {

	var connector = new google.maps.Polyline({
		path: locations,
		strokeColor: color,
		strokeOpacity: 0.75,
		strokeWeight: 3
	});

	connector.setMap(travelmap_map);
}


function addMarker(location, title, i, color, url) {
	if (travelmap_markers == false) return;

	var zindex = i;
	if (color == 'k') zindex = 999;

	if (travelmap_numbers === true && i < 100) {
		iconurl = travelmap_plugin_dir+'img/markers/'+color+(i+1)+'.png';
	} else {
		iconurl = travelmap_plugin_dir+'img/markers/'+color+'.png';
	}
	
	var icon = new google.maps.MarkerImage(iconurl, null, null, new google.maps.Point(2, 23));

	marker = new google.maps.Marker({
   	position: location,
   	map: travelmap_map,
		title: title,
		icon: icon,
		zIndex: zindex
	});
	marker.url = url;

	marker.setMap(travelmap_map);
	
	// Add event listener to marker
	google.maps.event.addListener(marker, "click", function() {
		if (this.url.length) {
			window.location = this.url;
		}
	});
}


initialize();