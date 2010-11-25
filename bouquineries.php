<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="initial-scale=1.0, user-scalable=no" />
        <style type="text/css">
            html { height: 100% }
            body { height: 100%; margin: 0px; padding: 0px }
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
            var adresses=new Array();
            var id_adresse_courante=0;
            var infowindows=new Array();
            
            function analyserAdresseSuivante() {
                if (id_adresse_courante < adresses.length) {
                    adresses[id_adresse_courante].id=id_adresse_courante;
                    var adresse=adresses[id_adresse_courante];
                    localiser(adresse.AdresseGoogle);
                    id_adresse_courante++;
                    window.setTimeout(analyserAdresseSuivante,500);
                }
            }

            function initialize() {
                var myAjax = new Ajax.Request('Database.class.php', {
                   method: 'post',
                   parameters:'database=true&liste_bouquineries=true',
                   onSuccess:function(transport,json) {
                       adresses=transport.headerJSON;
                       analyserAdresseSuivante();
                   }
                });
                geocoder = new google.maps.Geocoder();
                var latlng = new google.maps.LatLng(46.754917, 1.73584);
                var myOptions = {
                    zoom: 6,
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    streetViewControl: true
                };
                map = new google.maps.Map(document.getElementById("map_canvas"),  myOptions);
            }

            function localiser(adresse) {
                geocoder.geocode( { 'address': adresse}, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        map.setCenter(results[0].geometry.location);
                        var trouve=false
                        for (id_adresse in adresses) {
                            if (adresses[id_adresse].AdresseGoogle == results[0].formatted_address) {
                                creer_marqueur(adresses[id_adresse],results[0].geometry.location);
                                trouve=true;
                                break;
                            }
                        }
                        if (!trouve) {
                            alert(results[0].formatted_address+' ne correspond pas à l\'adresse de la base de données');
                        }
                    } else {
                        switch(status) {
                            case 'ZERO_RESULTS':
                                alert('Adresse introuvable');
                            break;
                            default:
                                alert("Geocode was not successful for the following reason: " + status);
                            break;
                        }
                    }
                });
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
                    '</div>'+
                    '</div>';

                infowindows[adresse.id] = new google.maps.InfoWindow({
                    content: contentString
                });
                
                google.maps.event.addListener(marker, 'click', function() {
                  for (id_adresse in adresses)
                    if (marker.title == adresses[id_adresse].Nom)
                        infowindows[id_adresse].open(map,marker);
                });

            }

        </script>
    </head>
    <body onload="initialize()">
        <div id="map_canvas" style="width:100%; height:100%"></div>
    </body>
</html>