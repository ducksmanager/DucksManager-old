var autocomplete;

function initializeAutocomplete() {
	autocomplete = new google.maps.places.Autocomplete(
		jQuery('#adresse_complete'),
		{ types: ['geocode'] }
	);
	google.maps.event.addListener(autocomplete, 'place_changed', fillInAddress);
}

function fillInAddress() {
	var place = autocomplete.getPlace();
	var form = jQuery('#form_bouquinerie');

	form.find('[name="coordX"]').val(place.geometry.location.lat());
	form.find('[name="coordY"]').val(place.geometry.location.lng());
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
	jQuery.post(
		'Database.class.php',
		{ database: 'true', liste_bouquineries: 'true' },
		function(response) {
			adresses=response;
			analyserAdresseSuivante();
		}
	);
	var latlng = new google.maps.LatLng(46.754917, 1.73584);
	var myOptions = {
		zoom: 4,
		center: latlng
	};
	map = new google.maps.Map(jQuery('#map_canvas'), myOptions);
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

	var element = jQuery('.infoWindow.template').clone(true).removeClass('template');
	jQuery.each(fields, function(i, field) {
		element.find('.' + field).update(adresse[field]);
	});

	infowindows[adresse.id] = new google.maps.InfoWindow({
		content: element.html()
	});

	google.maps.event.addListener(marker, 'click', function() {
		jQuery.each(fields, function(id, adresse) {
			infowindows[id].close(map, marker);
			if (marker.title === adresse.Nom)
				infowindows[id].open(map, marker);
		});
	});
}
