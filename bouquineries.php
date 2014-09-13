<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<style type="text/css">
			html { height: 100% }
			body { height: 100%; margin: 0; padding: 0; }
			#map_canvas { height: 100% }
		</style>
		<?php
			require_once('JS.class.php');
			new JS('prototype.js');
		?>
		<script type="text/javascript"
				src="http://maps.google.com/maps/api/js?sensor=false">
		</script>
		<script type="text/javascript">
			var map;
			var geocoder;
			var adresses=[];
			var id_adresse_courante=0;
			var infowindows=[];
			
			function analyserAdresseSuivante() {
				if (id_adresse_courante < adresses.length) {
					adresses[id_adresse_courante].id=id_adresse_courante;
					localiser(id_adresse_courante);
					id_adresse_courante++;
					window.setTimeout(analyserAdresseSuivante,500);
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
				geocoder = new google.maps.Geocoder();
				var latlng = new google.maps.LatLng(46.754917, 1.73584);
				var myOptions = {
					zoom: 4,
					center: latlng,
					mapTypeId: google.maps.MapTypeId.ROADMAP,
					streetViewControl: true
				};
				map = new google.maps.Map($("map_canvas"),  myOptions);
			}

			function localiser(id_adresse) {
				if (adresses[id_adresse].CoordX != '0') {
					creer_marqueur(adresses[id_adresse],
							   new google.maps.LatLng(adresses[id_adresse].CoordX, 
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

		</script>
	</head>
	<body onload="initialize()">
		<div id="map_canvas" style="width:100%; height:100%"></div>
	</body>
</html>