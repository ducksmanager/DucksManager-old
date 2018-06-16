<!DOCTYPE html>
<html>
	<head>
		<meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
		<style type="text/css">
			html { height: 100% }
			body { height: 100%; margin: 0; padding: 0; }
			#map_canvas { height: 100% }
            .template { display: none; }
		</style>
        <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyC1NTnb7sx7wl1fuqiLbKfWkQo3hNxv2HQ&callback=initialize" async defer></script>
        <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/prototype/1.7.3/prototype.min.js"></script>
		<script type="text/javascript" src="js/bouquineries.js?VERSION"></script>
	</head>
	<body>
		<div id="map_canvas" style="width:100%; height:100%"></div>
        <div class="template infoWindow">
            <div id="siteNotice">
            </div>
            <h1 id="firstHeading" class="firstHeading Nom"></h1>
            <div id="bodyContent">
                <p class="Commentaire"></p>
                <p>Adresse : </p>
                <p class="Adresse"></p><br />
                <p class="Signature"></p>
            </div>
        </div>
	</body>
</html>