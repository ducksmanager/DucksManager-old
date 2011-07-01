var numeros_dispos=new Array();
new Ajax.Request(urls['numerosdispos']+['index',pays,magazine].join('/'), {
	method: 'post',
	onSuccess:function(transport) {
		if (transport.responseText.indexOf('Nombre d\'arguments insuffisant') != -1) {
			alert('Utilisez un nom de magazine valide');
			return;
		}
		numeros_dispos=transport.headerJSON.numeros_dispos;
		new Ajax.Request(urls['parametrageg']+'index/'+pays+'/'+magazine+'/null/null', {
			method: 'post',
			onSuccess:function(transport) {
				etapes=transport.headerJSON;

				// Toujours intégrer l'étape -1
				integrer_etape(Object.values(etapes)[0].Ordre);
			}
		});
	}
});

function integrer_etape(num_etape) {
	$('log').insert('Int&eacute;gration de l\'&eacute;tape '+num_etape+'...');
	new Ajax.Request(urls['parametrageg']+['index',pays,magazine,num_etape,'null'].join('/'), {
		method: 'post',
		parameters: 'etape='+num_etape,
		onSuccess:function(transport) {
			$('log').insert('OK<br />');
			options = transport.headerJSON;
			integrer_option(num_etape,Object.keys(options)[0]);
		},
		onFailure:function(transport) {
			$('log').insert('ECHEC : <br />'+transport.responseText);
		}
	});
}

function integrer_option(num_etape_courante, nom_option_courante) {
	$('log').insert('&nbsp;Int&eacute;gration de l\'option '+nom_option_courante+'...');
	new Ajax.Request(urls['modifierg']+['index',pays,magazine,num_etape_courante,numero,nom_option_courante,valeurs_options[num_etape_courante][nom_option_courante],[numero,numero].join('/'),'Dimensions',false].join('/'), {
		method: 'post',
		onSuccess:function(transport) {
			$('log').insert('OK<br />');
			var option_trouvee=false;
			for (var nom_option in options) {
				if (option_trouvee) {
					integrer_option(num_etape_courante,nom_option);
					return;
				}
				if (nom_option==nom_option_courante)
					option_trouvee=true;
			}
			var etape_trouvee=false;
			for (var etape=0;etape<etapes.length;etape++) {
				var num_etape=etapes[etape].Ordre;
				if (etape_trouvee && est_dans_intervalle(numero, etapes[etape].Numero_debut+'~'+etapes[etape].Numero_fin)) {
					integrer_etape(num_etape);
					return;
				}
				if (num_etape == num_etape_courante)
					etape_trouvee=true;
			}
			
			var parametrage=new Object();
			var src=urls['viewer']+'/'+[numero,'1.5','all',JSON.stringify(parametrage),'save','false'].join('/')+'/'+username;
			var image=new Element('img',{'id':'image'});
			$('section_image').update(image);
			image.writeAttribute({'src':src});
			
		},
		onFailure:function(transport) {
			$('log').insert('ECHEC : <br />&nbsp;'+transport.responseText);
		}
	});
}