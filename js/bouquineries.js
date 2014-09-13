var autocomplete;

function initializeAutocomplete() {
	autocomplete = new google.maps.places.Autocomplete(
		$('adresse_complete'),
		{ types: ['geocode'] }
	);
	google.maps.event.addListener(autocomplete, 'place_changed', fillInAddress);
}

function fillInAddress() {
	var place = autocomplete.getPlace();
	var form = $('form_bouquinerie');

	form.down('[name="coordX"]').value = place.geometry.location.lat();
	form.down('[name="coordY"]').value = place.geometry.location.lng();
}

var map;
var adresses=[];
var id_adresse_courante=0;
var infowindows=[];

function analyserAdresseSuivante() {
	if (id_adresse_courante < adresses.length) {
		adresses[id_adresse_courante].id=id_adresse_courante;
		localiser(id_adresse_courante);
		id_adresse_courante++;
		analyserAdresseSuivante();
	}
}

function initialize() {
	new Ajax.Request('Database.class.php', {
		method: 'post',
		parameters:'database=true&liste_bouquineries=true',
		onSuccess:function(transport) {
			adresses=JSON.parse(transport.responseText);
			analyserAdresseSuivante();
		}
	});
	var latlng = new google.maps.LatLng(46.754917, 1.73584);
	var myOptions = {
		zoom: 4,
		center: latlng
	};
	map = new google.maps.Map($("map_canvas"),  myOptions);
}

function localiser(id_adresse) {
	if (adresses[id_adresse].CoordX != '0') {
		creer_marqueur(adresses[id_adresse],
			new google.maps.LatLng(
				adresses[id_adresse].CoordX,
				adresses[id_adresse].CoordY));
	}
}

function creer_marqueur(adresse,position) {
	var marker = new google.maps.Marker({
		map: map,
		position: position,
		title: adresse.Nom
	});


	var contentString = '<div id="content">'+
		'<div id="siteNotice">'+
		'</div>'+
		'<h1 id="firstHeading" class="firstHeading">'+adresse.Nom+'</h1>'+
		'<div id="bodyContent">'+
		'<p>'+adresse.Commentaire+'</p>'+
		'<p>Adresse : </p>'+
		'<p>'+adresse.AdresseComplete+'<br />'
		+adresse.Signature+'<br />'+
		'</div>'+
		'</div>';

	infowindows[adresse.id] = new google.maps.InfoWindow({
		content: contentString
	});

	google.maps.event.addListener(marker, 'click', function() {
		for (id_adresse in adresses) {
			if (typeof(infowindows[id_adresse]) != 'undefined') {
				infowindows[id_adresse].close(map,marker);
				if (marker.title == adresses[id_adresse].Nom)
					infowindows[id_adresse].open(map,marker);
			}
		}
	});

}