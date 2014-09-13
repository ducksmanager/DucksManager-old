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