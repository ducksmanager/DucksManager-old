<html>
<head>
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		#chargement {
			position:fixed;
			right:0;
			top:0;
		}
		#preview {
			float:left;
		}
		
		#numero_ajout_input input  {
			width:50px;
		}
		
		input,button {
			margin:2px;
		}
		
		input {
			font-family: Arial;
		}
		
		.numero_ajout input {
			width:100px;
		}
		
		.largeur_standard {
			width:170px;
		}
		.intervalle_validite {
			font-size:12px;
			border:2px dashed pink;
			white-space:nowrap;
		}
		
		td {
			padding-top: 2px;
			vertical-align: top;
		}
		ul {
			padding-left:15px;
		}
		
		li {
			padding-bottom: 15px;
		}
		
		li.intervalle_ajout {
			padding-bottom:0px;
		}
		
		a {
			text-decoration: none;
		}
		
		div.visu_gen {
			width:300px;
		}
		
		#generated_issues {
			white-space:nowrap;
		}
		
		a.toggleable:hover {
			border-bottom:1px blue dashed;
		}
		
		a:hover, a.actif {
			text-decoration: underline;
		}
		
		div.toggleable {
			display:none;
		}
		
		div.toggleable.actif {
			display:block;
		}
		
		.pointer {
			cursor:pointer;
		}
		
		.valeur {
			overflow-x: auto;
			width: 350px;
		}
		
		.decale {
			margin-left:29px;
		}
		
		.cache { display:none;}
		.montre { display:inline;}
	</style>
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/prototype.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/json2.js" ></script>
	<script type="text/javascript" src="<?=base_url()?>system/application/views/js/jscolor.js" ></script>
	<script type="text/javascript">
		var pays='<?=$pays?>';
		var magazine='<?=$magazine?>';
		var parametrage=new Object();
		var parametrage_complet=new Object();
		var parametres;
		var numeros_gen;
		var numero_gen_courant;
		var est_visu=true;
		
		function ajouter_intervalle(element) {
			var nouvel_intervalle=element.up().cloneNode(true);
			element.up().insert({'after':nouvel_intervalle});
			return nouvel_intervalle;
		}
		
		function supprimer_intervalle (element) {
			if (element.up().up().select('div, li, .debut').size() == 1)
				alert('Impossible de supprimer le dernier intervalle !');
			else
				element.up().remove();
		}

		function ajouter_expression() {
			var element=new Element('li')
				.update('Ajouter les num&eacute;ros')
				.insert(new Element('select')
					.insert(new Element('option',{'value':'match'}).update('respectant'))
					.insert(new Element('option',{'value':'no_match'}).update('ne respectant pas')))
				.insert('l\'expression ')
				.insert(new Element('input',{'type':'text', 'id':'regex'}).setStyle({'display': 'list-item'}));
			$('intervalles_ajout').insert(element);
			var validation_regex=new Element('button').update('OK');
			element.insert(validation_regex);
			validation_regex.observe('click',function(event) {
				var element=Event.element(event);
				var match=element.previous('select').selectedIndex==0;
				var regex=new RegExp(element.previous('input').value,'g');
				var numeros_trouves=new Array();
				var index_courant=-1;
				for (var i=0;i<$('preview_issue').childElementCount;i++) {
					if ((!match ^ regex.test($('preview_issue').options[i].value) && $('preview_issue').options[i].value!='Aucun')) {
						if (!(numeros_trouves[index_courant]) || $('preview_issue').options[i].index != numeros_trouves[index_courant][1]+1) {
							index_courant++;
							numeros_trouves[index_courant]
								=new Array($('preview_issue').options[i].index,$('preview_issue').options[i].index);
						}
						else {
							numeros_trouves[index_courant][1]=$('preview_issue').options[i].index;
						}
					}
				}
				var texte=new Array();
				numeros_trouves.each(function (numero_trouve) {
					texte.push(numero_trouve.join(' a '));
				});
				if (confirm('Numeros trouves : '+texte.join(' ; ')+"\n"+'Tous les ajouter ?')) {
					numeros_trouves.each(function (numero_trouve,i) {
						var nouvel_intervalle=ajouter_intervalle($('intervalles_ajout').down('li').down('a'));
						nouvel_intervalle.down('select').selectedIndex=numero_trouve[0];
						nouvel_intervalle.down('select',1).selectedIndex=numero_trouve[1];
					});
				}
			});
		}
		
		function executer_action(num_etape) {
			$('chargement').update('Chargement...');
			if ($('cloner_etape') && $('cloner_etape').checked) {
				var num_etape_courante=$$('[name="etapes_clonables"]')[0].options[$$('[name="etapes_clonables"]')[0].selectedIndex].value;
				new Ajax.Request('<?=site_url('cloner')?>/index/'+pays+'/'+magazine+'/'+num_etape_courante+'/'+num_etape, {
					method: 'post',
					onSuccess:function() {
						document.location.reload();
					}
				});
				return;
			}
			if ($('etendre_numero').checked) {
				var numero=$('extension1').options[$('extension1').selectedIndex].value;
				var nouveau_numero=$('extension2').options[$('extension2').selectedIndex].value;
				new Ajax.Request('<?=site_url('etendre')?>/index/'+pays+'/'+magazine+'/'+numero+'/'+nouveau_numero, {
					method: 'post',
					onSuccess:function() {
						//document.location.reload();
					}
				});
				return;
			}
			var nom_fonction=$('nouvelle_fonction').options[$('nouvelle_fonction').selectedIndex].text;
			var numeros_debut_fin=$$('[name=\"viewable_issue1\"], [name=\"viewable_issue2\"]');
			var numero_debut=new Array();
			var numero_fin=new Array();
			numeros_debut_fin.each(function (element) {
				if (element.readAttribute('name')=='viewable_issue1')
					numero_debut.push(element.options[element.selectedIndex].value);
				else
					numero_fin.push(element.options[element.selectedIndex].value);
				
			});
			
			new Ajax.Request('<?=site_url('ajout')?>/index/'+pays+'/'+magazine+'/'+num_etape+'/'+nom_fonction+'/'+numero_debut.join(';')+'/'+numero_fin.join(';'), {
				method: 'post',
				parameters: 'ordre='+num_etape+'&fonction='+nom_fonction+'&numero_debut='+numero_debut+'&numero_fin='+numero_fin,
				onSuccess:function(transport) {
				$('chargement').update();
					parametres=transport.request.parameters;
					$('parametrage').update('<h1>Ajout</h1>')
									.insert(transport.responseText).insert('<br /><br />')
									.insert(new Element('button',{'id':'appliquer'}).update('Appliquer'))
									.insert(new Element('button',{'id':'annuler'}).update('Annuler'))
									.insert('<br />');
					$('appliquer').observe('click', function() {
						remplir_parametrage();
						new Ajax.Request('<?=site_url('ajout')?>/index/'+pays+'/'+magazine+'/'+parametres.ordre+'/'+parametres.fonction+'/'+parametres.numero_debut+'/'+parametres.numero_fin+'/'+JSON.stringify(parametrage_complet)+'/true', {
							method:'post',
							onSuccess:function() {
								document.location.reload();
							}
						});
					});
					$('annuler').observe('click',function() {
						$('parametrage').update();
						parametrage=new Object();
						reload_preview();
						
					});
					jscolor.init();
					finaliser_affichage_parametrage();
					remplir_parametrage();
					reload_preview();
				}
			});
		}
		
		function reload_preview(val_numero) {
			if (typeof(val_numero) == 'undefined') {
				est_visu=true;
				reload_preview($('preview_issue').options[$('preview_issue').selectedIndex].value);
				return;
			}
			var val_zoom=$('zoom').options[$('zoom').selectedIndex].value;
			if (est_visu) {
				if ($('preview_issue').options[$('preview_issue').selectedIndex].value == 'Aucun') {
					$('preview').writeAttribute({'src':$('preview_prefix').readAttribute('src')+'/'+val_numero+'/'+val_zoom+'/_/_'});
					return;
				}
			}
			$('chargement').update('Chargement...');
			var etapes_preview=new Array();
			var etapes_checked=$$('[name="etape_active"]').pluck('checked');
			for (var i in etapes_checked)
				if (etapes_checked[i]==true)
					etapes_preview.push($$('[name="etape_active"]')[i].value);
			if (etapes_preview.length==0)
				etapes_preview.push(-1);
			charger_image($('preview_prefix').readAttribute('src')+'/'+val_numero+'/'+val_zoom+'/'+etapes_preview.join("-")+'/'+JSON.stringify(parametrage)+'/'+(est_visu?'false':'true'),val_zoom);
		}
		
		function charger_image(src,val_zoom) {
			var random=Math.random();
			src+='/'+random;
			if (est_visu) {
				var image=$('preview');
			}
			else { //gen
				var image=new Element('img');
				$('generated_issues').insert(image);
			}
			image.writeAttribute({'title':src,'src':src});
			image.observe('load',function() {
				if (est_visu) {
					$('regle').writeAttribute({'height':(300*val_zoom)});
					$('chargement').update();
					$('error_log').update();
				}
				else {
					numero_gen_courant++;
					if (numeros_gen[numero_gen_courant]) {
						est_visu=false;
						reload_preview(numeros_gen[numero_gen_courant]);
					}
				}
					
			});
			image.observe('error',function(event) {
				if (est_visu) {
					$('regle').writeAttribute({'height':0});
					$('chargement').update('Erreur !');
					$('error_log').update(new Element('iframe',{'src':Event.element(event).src+'/debug'}));
				}
				else {
					numero_gen_courant++;
					if (numeros_gen[numero_gen_courant]) {
						est_visu=false;
						reload_preview(numeros_gen[numero_gen_courant]);
					}
				}
			});
		}
		
		function sv_doublons(pays,magazine) {
			new Ajax.Request('<?=site_url('sv_doublons')?>/index/'+pays+'/'+magazine, {
				method: 'post',
				onSuccess:function(transport) {
					alert('Termine');
					$('sv_doublons_texte').insert(transport.responseText);
				}
			});
		}
		
		function reload_gen() {
			var index_debut=$('first_issue').selectedIndex;
			var index_fin=$('last_issue').selectedIndex;
			if (parseInt(index_fin-index_debut)>=100)
				if (!confirm('Vous allez generer + de 100 numeros. Etes vous sur(e) ?'))
					return;
			$('generated_issues').update();
			numeros_gen=new Array();
			numero_gen_courant=0;
			for (var i=index_debut;i<=index_fin;i++) {
				numeros_gen.push($('first_issue').options[i].value);
			}
			est_visu=false;
			reload_preview(numeros_gen[0]);
		}
		
		
		new Event.observe(window, 'load',function() {
			reload_preview();
			$$('[name="etape_active"]').invoke('observe','click',reload_preview);
			$$('a.toggleable').invoke('observe','click',toggle);
		});
		
		function toggle(ev) {
			var element=Event.element(ev);
			var classe_active=element.name;
			element.up('.toggleable_links').select('.toggleable').each (function(element) {
				element.removeClassName('actif');
			});
			$$('[name="'+classe_active+'"]').invoke('addClassName','actif');
			
		}
	
		function parametrage_etape(num_etape,nom_fonction,numero_debut,numero_fin) {
			$('chargement').update('Chargement...');
			parametrage=new Object();
			new Ajax.Request('<?=site_url('parametrage')?>/index/'+pays+'/'+magazine+'/'+num_etape+'/'+numero_debut+'/'+numero_fin+'/'+nom_fonction, {
				method: 'post',
				parameters: 'ordre='+num_etape+'&fonction='+nom_fonction+'&numero_debut='+numero_debut+'&numero_fin='+numero_fin,
				onSuccess:function(transport) {
					parametres=transport.request.parameters;
					$('chargement').update();
					$('parametrage').update('<h1>Param&eacute;trage</h1>')
									.insert(transport.responseText).insert('<br /><br />')
									.insert(new Element('button',{'id':'appliquer'}).update('Appliquer'))
									.insert(new Element('button',{'id':'annuler'}).update('Annuler'))
									.insert('<br />');
					$('appliquer').observe('click', function() {
						remplir_parametrage();
						new Ajax.Request('<?=site_url('parametrage')?>/index/'+pays+'/'+magazine+'/'+parametres.ordre+'/'+parametres.numero_debut+'/'+parametres.numero_fin+'/'+parametres.fonction+'/'+JSON.stringify(parametrage_complet)+'/true', {
							method:'post',
							onSuccess:function() {
								document.location.reload();
							}
						});
					});
					$('annuler').observe('click',function() {
						$('parametrage').update();
						parametrage=new Object();
						reload_preview();
					});
					jscolor.init();
					var revenir_par_defaut=new Element('a',{'href':'javascript:void(0)'}).update('Revenir aux valeurs par d&eacute;faut');
					$('parametrage').insert(revenir_par_defaut);
					revenir_par_defaut.observe('click',function() {
						parametrage_etape(parametres[0],parametres[1],parametres[2],parametres[3]);
						reload_preview();
					});
					finaliser_affichage_parametrage();

				}
			});
		}


		function supprimer_etape(num_etape,nom_fonction,numero_debut,numero_fin) {
			if (confirm('Vous allez supprimer cette fonction. Cette action n\'est pas reversible. Continuer ?')) {

				new Ajax.Request('<?=site_url('supprimer')?>/index/'+pays+'/'+magazine+'/'+num_etape+'/'+numero_debut+'/'+numero_fin+'/'+nom_fonction, {
					method: 'post',
					parameters: 'ordre='+num_etape+'&fonction='+nom_fonction+'&numero_debut='+numero_debut+'&numero_fin='+numero_fin,
					onSuccess:function() {
						document.location.reload();
					}
				});
			}  
		}
		
		function supprimer(element) {
			if (confirm('Etes-vous sur(e) ?'))
				element.up('div').remove();
		}

		function cloner(element) {
			var div_englobant=element.up('div');
			var div_englobant_clone=div_englobant.cloneNode(true);
			//div_englobant_clone.down('.cloner').remove();
			var id_div_englobant=div_englobant.readAttribute('id');
			var i=1;
			while($(id_div_englobant+i))
				i++;
			remplacer_ids(div_englobant_clone,i);
			div_englobant.insert({'after':div_englobant_clone});
			maj_evenements();
			jscolor.init();
			return div_englobant_clone;
		}

		function remplacer_ids(element_clone,i) {
			if ($(element_clone).readAttribute('id') != null)
				$(element_clone).writeAttribute({'id':$(element_clone).readAttribute('id')+i});
			if ($(element_clone).readAttribute('name') != null)
				$(element_clone).writeAttribute({'name':$(element_clone).readAttribute('name')+i});
			$(element_clone).addClassName('cloned');
			$(element_clone).childElements().each(function(element) {
				remplacer_ids($(element),i);
			});
		}
		
		function par_defaut(nom_option) {
			var numeros_non_renseignes=new Array();
			var numeros_renseignes=new Array();
			$$('.intervalle_validite[name^="'+nom_option+'"]').each(function(div_intervalle) {
				var liste1=div_intervalle.down('.debut');
				var liste2=div_intervalle.down('.fin');
				for (var i=liste1.selectedIndex;i<=liste2.selectedIndex;i++)
					numeros_renseignes.push(i);
			});
			var numeros_possibles=new Array();
			$$('[id^="numero_debut"]').each(function(debut_intervalle_possible) {
				var fin_intervalle_possible=debut_intervalle_possible.next('select');
				for (var i=debut_intervalle_possible.selectedIndex;i<=fin_intervalle_possible.selectedIndex;i++)
					numeros_possibles.push(i);
			});
			numeros_possibles.each(function(numero_possible) {
				if (numeros_renseignes.indexOf(numero_possible) == -1)
					numeros_non_renseignes.push(numero_possible);
			});
			if (numeros_non_renseignes.length==0) {
				alert('Tous les numeros sont renseignes');
				return;
			}
			
			var numeros_non_renseignes_groupes=new Array();
			var id_groupe=0;
			numeros_non_renseignes_groupes[0]=new Array(numeros_non_renseignes[0],numeros_non_renseignes[0]);
			numeros_non_renseignes_groupes[0][0]=numeros_non_renseignes[0];
			for (var i=1;i<numeros_non_renseignes.length;i++) {
				if (numeros_non_renseignes[i]==numeros_non_renseignes[i-1]+1)
					numeros_non_renseignes_groupes[id_groupe][1]=numeros_non_renseignes[i];
				else {
					id_groupe++;
					numeros_non_renseignes_groupes[id_groupe]=new Array();
					numeros_non_renseignes_groupes[id_groupe]=new Array(numeros_non_renseignes[i],numeros_non_renseignes[i]);
				}
			}
			var numeros_non_renseignes_groupes_str='Numeros restants trouves : ';
			
			for (i=0;i<numeros_non_renseignes_groupes.length;i++) {
				if (i>0)
					numeros_non_renseignes_groupes_str+=' ; ';
				numeros_non_renseignes_groupes_str+=$('preview_issue').options[numeros_non_renseignes_groupes[i][0]].value
												  + ' a '
												  + $('preview_issue').options[numeros_non_renseignes_groupes[i][1]].value;
			}
			if (null != (val_par_defaut=prompt(numeros_non_renseignes_groupes_str+"\n"+'Entrez la valeur par defaut a affecter'))) {
			
				for (i=0;i<numeros_non_renseignes_groupes.length;i++) {
					var clone=cloner($$('.intervalle_validite[name^="'+nom_option+'"]')[0]);
					clone.down('.intervalle_validite').down('.debut').selectedIndex=numeros_non_renseignes_groupes[i][0];
					clone.down('.intervalle_validite').down('.fin').selectedIndex=numeros_non_renseignes_groupes[i][1];
					clone.down('input').value=val_par_defaut;
				}
				jscolor.init();
			}
		}

		function finaliser_affichage_parametrage() {
			$$('.quantite').each(function(element) {
				var element_moins2=new Element('img',{'src':'<?=base_url()?>../images/icones/minus2.png', 'width':'18','alt':'--','id':element.readAttribute('id')+'_mm'})
					.addClassName('pointer');
				var element_moins1=new Element('img',{'src':'<?=base_url()?>../images/icones/minus.png', 'alt':'-','id':element.readAttribute('id')+'_m'})
					.addClassName('pointer');
				var element_plus1=new Element('img',{'src':'<?=base_url()?>../images/icones/plus.png', 'alt':'+','id':element.readAttribute('id')+'_p'})
					.addClassName('pointer');
				var element_plus2=new Element('img',{'src':'<?=base_url()?>../images/icones/plus2.png', 'width':'18','alt':'++','id':element.readAttribute('id')+'_pp'})
					.addClassName('pointer');
				element.up().previous('td').insert(element_moins2);
				element.up().previous('td').insert(element_moins1);
				element.up().next('td').insert(element_plus1);
				element.up().next('td').insert(element_plus2);
			});
			maj_evenements();
		}

		function maj_evenements() {
			$$('.modifiable, .liste, .color').invoke('stopObserving','blur');
			$$('.modifiable, .liste, .color').invoke('observe','blur',function(event) {
				var element=Event.element(event);
				if (element.hasClassName('image')) {
					var source=element.options[element.selectedIndex].value;
					element.up('td').previous().down('img')
						.writeAttribute({'src':'<?=base_url()?>../edges/'+pays+'/elements/'+source});
				}
				remplir_parametrage();
				reload_preview();
			}); 

			$$('.pointer').invoke('stopObserving','click');
			$$('.pointer').invoke('observe','click',function(event) {
				var element=Event.element(event);
				switch(element.alt) {
					case '--':
						changer_qte(element.readAttribute('id'),'--');
					break;
					case '-':
						changer_qte(element.readAttribute('id'),'-');
					break;
					case '+':
						changer_qte(element.readAttribute('id'),'+');
					break;
					case '++':
						changer_qte(element.readAttribute('id'),'++');
					break;
				}
				remplir_parametrage();
				reload_preview();
			});
		}

		function remplir_parametrage() {
			parametrage=new Object();
			parametrage_complet=new Object();
			var parametres_joints=parametersArray(parametres).join('~');
			parametrage[parametres_joints]=new Object();
			parametrage_complet[parametres_joints]=new Object();
			$$('.parametre').each(function(element) {
				if (typeof (element.up('.cache'))!='undefined')
					return;
				var trouve=false;
				var index_numero_preview=$('preview_issue').selectedIndex;
				element.up('.cellule_valeur').next('.cellule_intervalle_validite').down('.intervalle_validite').select('.debut').each(function (intervalle_debut) {
					var index_debut=intervalle_debut.selectedIndex;
					var index_fin=intervalle_debut.next('select').selectedIndex;
					if (index_numero_preview >= index_debut && index_numero_preview <= index_fin)
						trouve=true;
				});
				var numero_debut=getValeur(element.up('.cellule_valeur').next('.cellule_intervalle_validite').select('select.debut'));
				var numero_fin=getValeur(element.up('.cellule_valeur').next('.cellule_intervalle_validite').select('select.fin'));
				var option_nom=element.up('.ligne_option_intervalle').up().down('div').readAttribute('name')+'.'+numero_debut+'~'+numero_fin;
				
				parametrage_complet[parametres_joints][option_nom]
					=encodeURI(element.value)
						.replace(new RegExp('%','g'),'^')
						.replace(new RegExp('&','g'),'!amp!')
						.replace(new RegExp('/','g'),'!slash!')
						.replace(new RegExp('#','g'),'!sharp!');//element.value.replace(element_nettoye_reg,'^u$1');
						
				if (trouve)
					parametrage[parametres_joints][option_nom]=parametrage_complet[parametres_joints][option_nom];
			});
		}
		
		function getValeur(element) {
			if (Object.isElement(element)) // Element unique
				return element.tagName=='SELECT'?($(element).options[$(element).selectedIndex].value):$(element).value;
			else { // Enumerable
				var valeurs=new Array();
				element.each(function(sous_element) {
					valeurs.push(getValeur(sous_element));
				});
				return valeurs.join(';');
			}
		}

		function parametersArray(parametres) {
			return new Array(parametres.ordre,
							 parametres.fonction,
							 getValeur($('parametrage').select('[id^="numero_debut"]')),
							 getValeur($('parametrage').select('[id^="numero_fin"]')));
		}

		function changer_qte(id_image,operation) {
			var id=id_image.replace(new RegExp('_mm','g'),'').replace(new RegExp('_m','g'),'').replace(new RegExp('_pp','g'),'').replace(new RegExp('_p','g'),'');
			var qte = parseFloat($(id).value);
			switch(operation) {
				case '--':
					qte--;
				break;
				case '-':
					qte-=0.1;
				break;
				case '+':
					qte+=0.1;
				break;
				case '++':
					qte++;
				break;
			}
			$(id).value = parseFloat(qte) ;
		}
		
		function alterner_champ(element) {
			element.up('td').select('.cache, .montre')
				.invoke('toggleClassName','montre')
				.invoke('toggleClassName','cache');
		}
			
	</script>
<title><?=$title?></title>
</head>
<body id="body" style="margin:0;padding:0">