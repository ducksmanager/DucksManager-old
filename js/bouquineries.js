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

	var fields = ['Nom', 'Commentaire', 'Adresse', 'Signature'];

	var element = $$('.infoWindow.template')[0].clone(true).removeClassName('template');
	for (var i_field in fields) {
		if( fields.hasOwnProperty(i_field)) {
			var field = fields[i_field];
			element.down('.' + field).update(adresse[field]);
		}
	}

	infowindows[adresse.id] = new google.maps.InfoWindow({
		content: element.innerHTML
	});

	google.maps.event.addListener(marker, 'click', function() {
		for (var id_adresse in adresses) {
			if (infowindows.hasOwnProperty(id_adresse)) {
				infowindows[id_adresse].close(map,marker);
				if (marker.title === adresses[id_adresse].Nom)
					infowindows[id_adresse].open(map,marker);
			}
		}
	});

}