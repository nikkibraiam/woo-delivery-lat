var geoXml = null;
var map = null;
var geocoder = null;
var toggleState = 1;
var infowindow = null;
var marker = null;

function inicialize(locationdev, divLocation, reload, callback) {
    console.log("inicialize");
    geocoder = new google.maps.Geocoder();
    if (locationdev && locationdev.latitude && locationdev.longitude) {
        console.log("Entro coordenadas");
        initialize_map(locationdev.latitude, locationdev.longitude, divLocation);
        callback(map, locationdev);
    } else if (locationdev && locationdev.address) {
        geocoder.geocode({ 'address': locationdev.address }, function(results, status) {
            if (status == google.maps.GeocoderStatus.OK && results.length > 0) {
                var point = results[0].geometry.location;
                console.log("Entro Dirección");
                locationdev.latitude = point.lat();
                locationdev.longitude = point.lng();
                initialize_map(locationdev.latitude, locationdev.longitude, divLocation);
                callback(map, locationdev);
            }
        });
    } else if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            console.log("currentposition");
            initialize_map(position.coords.latitude, position.coords.longitude, divLocation);
            locationdev.latitude = position.coords.latitude;
            locationdev.longitude = position.coords.longitude;
            callback(map, locationdev);
        });
    } else {
        locationdev.latitude = 4.679813869726361;
        locationdev.longitude = -74.10780700036405;
        initialize_map(locationdev.latitude, locationdev.longitude, divLocation);
        callback(map, locationdev);
    }
}

function initialize_map(latitude, longitude, divLocation) {
    console.log("initialize_map");
    if (!map) {
        infowindow = new google.maps.InfoWindow({ size: new google.maps.Size(150, 50) });
        var myOptions = {
            zoom: 9,
            center: new google.maps.LatLng(latitude, longitude),
            mapTypeControl: true,
            mapTypeControlOptions: { style: google.maps.MapTypeControlStyle.DROPDOWN_MENU },
            navigationControl: true,
            mapTypeId: google.maps.MapTypeId.ROADMAP
        }
        map = new google.maps.Map(document.getElementById(divLocation),
            myOptions);
    }
    console.log(map);
}

function initialize_GeoCoverage(map, kmlPath, reload, show = true) {
    console.log("initialize_GeoCoverage");
    if (reload && geoXml) {
        geoXml.hideDocument();
        geoXml = null;
    }
    if (!geoXml)
        geoXml = new geoXML3.parser({ map: show ? map : null, singleInfoWindow: true, infoWindow: infowindow });

    if (!geoXml.docs.map(function(x) { return x.url; }).includes(kmlPath)) {
        geoXml.parse(kmlPath);
    }

}

function addPointerMap(point) {
    console.log(point);
    map.setCenter(point);
    if (marker && marker.setMap) marker.setMap(null);
    marker = new google.maps.Marker({
        map: map,
        position: point
    });
}

function searchAddress(address, location, callback) {
    var contentString = address + "<br>No tenemos cobertura para esa ubicación";

    geocoder.geocode({ 'address': address }, function(results, status) {
        if (status == google.maps.GeocoderStatus.OK) {
            var point = results[0].geometry.location;
            addPointerMap(point);
            contentString += "<br>" + point;
            for (var j = 0; j < geoXml.docs.length; j++) {
                for (var i = 0; i < geoXml.docs[j].gpolygons.length; i++) {
                    if (geoXml.docs[j].gpolygons[i].contains(point)) {
                        contentString = address + "<br>" + geoXml.docs[j].placemarks[i].name;
                        contentString += "<br>" + point;
                        callback(geoXml.docs[j].url, geoXml.docs[j].placemarks[i].name, point);
                        continue;
                    }
                }
            }
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.setContent(contentString);
                infowindow.open(map, marker);
            });
            google.maps.event.trigger(marker, "click");
        }
    });
}
setTimeout(function() {
    google.maps.Polygon.prototype.contains = function(point) {
        var j = 0;
        var oddNodes = false;
        var x = point.lng();
        var y = point.lat();
        for (var i = 0; i < this.getPath().getLength(); i++) {
            j++;
            if (j == this.getPath().getLength()) { j = 0; }
            if (((this.getPath().getAt(i).lat() < y) && (this.getPath().getAt(j).lat() >= y)) ||
                ((this.getPath().getAt(j).lat() < y) && (this.getPath().getAt(i).lat() >= y))) {
                if (this.getPath().getAt(i).lng() + (y - this.getPath().getAt(i).lat()) /
                    (this.getPath().getAt(j).lat() - this.getPath().getAt(i).lat()) *
                    (this.getPath().getAt(j).lng() - this.getPath().getAt(i).lng()) < x) {
                    oddNodes = !oddNodes
                }
            }
        }
        return oddNodes;
    }
}, 1000);