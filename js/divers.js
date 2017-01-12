function supprimer_auteur (nom_auteur) {
	new Ajax.Request('Database.class.php', {
	   method: 'post',
	   parameters:'database=true&supprimer_auteur=true&nom_auteur='+nom_auteur,
	   onSuccess:function() {
	    	location.reload();
		}
	});
}