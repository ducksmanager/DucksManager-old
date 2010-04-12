function ajouter_exemple() {
	var nouvelle_table=new Element('table',{'id':'table_nouvel_exemple','border':'0px'});
	
	var infos_exemple_l2=new Element('tr');
	var infos_exemple_l2c1=new Element('td').insert('Pays du magazine :');
	var input_pays=new Element('input',{'type':'text'});
	var infos_exemple_l2c2=new Element('td').insert('<select style="width:300px;" onclick="select_magazine()" id="liste_pays"><option id="vide">Chargement des pays...</select>');
	infos_exemple_l2.insert(infos_exemple_l2c1).insert(infos_exemple_l2c2);
	
	var infos_exemple_l3=new Element('tr');
	var infos_exemple_l3c1=new Element('td').insert('Nom du magazine :');
	var input_nom=new Element('input',{'type':'text'});
	var infos_exemple_l3c2=new Element('td').insert('<select style="width:300px;" onclick="select_numero()" id="liste_magazines" ><option id="vide">Chargement des magazines...</option></select>');
	infos_exemple_l3.insert(infos_exemple_l3c1).insert(infos_exemple_l3c2);
	
	var infos_exemple_l4=new Element('tr');
	var infos_exemple_l4c1=new Element('td').insert('Num&eacute;ro du magazine :');
	var input_nom=new Element('input',{'type':'text'});
	var infos_exemple_l4c2=new Element('td').insert('<select style="width:300px;" id="liste_numeros"><option id="vide">Chargement des num&eacute;ros...</option></select>');
	infos_exemple_l4.insert(infos_exemple_l4c1).insert(infos_exemple_l4c2);
	
	var infos_exemple_l5=new Element('tr');
	var infos_exemple_l5c1=new Element('td').insert('Etat du magazine :');
	var input_nom=new Element('input',{'type':'text'});
	var infos_exemple_l5c2=new Element('td').insert('<select style="width:300px;" id="liste_etats" ><option id="vide">Chargement des &eacute;tats...</option></select>');
	infos_exemple_l5.insert(infos_exemple_l5c1).insert(infos_exemple_l5c2);
	
	var infos_exemple_l6=new Element('tr');
	var infos_exemple_l6c1=new Element('td').insert('Prix du magazine :');
	var input_prix=new Element('input',{'id':'prix_numero','type':'text','size':4,'value':2});
	var infos_exemple_l6c2=new Element('td').insert(input_prix).insert('&nbsp;&euro;');
	infos_exemple_l6.insert(infos_exemple_l6c1).insert(infos_exemple_l6c2);
	
	var lien_enregistrer=new Element('a',{'href':'javascript:void(0)'}).update('Enregistrer');
	
	
	nouvelle_table.insert(infos_exemple_l2)
				  .insert(infos_exemple_l3)
				  .insert(infos_exemple_l4)
				  .insert(infos_exemple_l5)
				  .insert(infos_exemple_l6)
				  .insert(lien_enregistrer);
				  
	$('ajouter_exemple').update(nouvelle_table);
	lien_enregistrer.observe('click',function() {
		if (!etats_charges)
			return;
		var nom_magazine=$('liste_magazines').options[$('liste_magazines').options.selectedIndex].text;
		var nom_pays=$('liste_pays').options[$('liste_pays').options.selectedIndex].text;
		var numero=$('liste_numeros').options[$('liste_numeros').options.selectedIndex].text;
		var prix=$F('prix_numero');
		$('liste_exemples').insert(nom_magazine).insert(' (').insert(nom_pays).insert(') ')
						   .insert('n&deg;').insert(numero).insert(' &agrave; ')
						   .insert(prix).insert(' &euro;<br />');
		$('table_nouvel_exemple').remove();
		etats_charges=false;
	});
	
	initPays();
}