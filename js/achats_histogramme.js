function afficher_histogramme_achats(type) {
	type = type || '';
	$('iframe_graphique').writeAttribute({'src':'achats_histogramme.php?' 
											  + (type=='progressif' ? 'type=progressif&' : '')
											  +'&largeur_max='+$('body').getWidth()
											  +'&hauteur_max='+$('body').getHeight()});
	
}