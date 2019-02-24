var autocomplete;

function initializeAutocomplete() {
	autocomplete = new google.maps.places.Autocomplete(
		jQuery('#adresse_complete').get(0),
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

function initBouquineries() {
	jQuery.post('Database.class.php',
		{ database: 'true', liste_bouquineries: 'true' },
		function(response) {
			mapboxgl.accessToken = 'pk.eyJ1IjoiYnBlcmVsIiwiYSI6ImNqbmhubHVrdDBlZ20zcG8zYnQydmZwMnkifQ.suaRi8ln1w_DDDlTlQH0vQ';

			var carte = new mapboxgl.Map({
				container: 'map',
				style: 'mapbox://styles/mapbox/light-v10',
				center: [1.73584, 46.754917],
				zoom: 4
			});

			jQuery.each(response, function(i, adresse) {
				creer_marqueur(carte, adresse, [
					parseFloat(adresse.CoordY),
					parseFloat(adresse.CoordX)
				]);
			});
		}
	);
}

function creer_marqueur(carte, adresse,position) {
	var fields = ['Nom', 'Commentaire', 'Adresse', 'Signature'];

	var element = jQuery('.infoWindow.template').clone(true).removeClass('template');
	jQuery.each(fields, function(i, field) {
		element.find('.' + field).html(adresse[field]);
	});

	new mapboxgl.Marker($('<div>').addClass('marker')[0])
		.setLngLat(position)
		.setPopup(new mapboxgl.Popup() // add popups
			.setHTML(element.html()))
		.addTo(carte);
}
