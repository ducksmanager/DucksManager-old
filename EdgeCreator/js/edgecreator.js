jQuery.fn.pluck = function(key) {
  var plucked = [];
  this.each(function() {
    plucked.push(this[key]);
  });
  return plucked;
};

jQuery.fn.getElementsWithData = function(key,val) {
  var data = new Array();
  this.each(function(i,element) {
    if (typeof(val) == 'undefined' || $(element).data(key) == val)
    	data.push($(element)[0]);
  });
  return $(data);
};

jQuery.fn.getData = function(key) {
  var data = new Array();
  this.each(function(i,element) {
    data.push($(element).data(key));
  });
  return data;
};

var first_cell=null;
var zoom=2;
var numeros_dispos;
var selecteur_cellules='#table_numeros tr:not(.ligne_entete)>td:not(.intitule_numero):not(.cloner)';
var colonne_ouverte=false;
var url_viewer='viewer';
var valeurs_possibles_zoom = [1, 1.5, 2, 4, 6, 8];

function element_to_numero(elements) {
    var numeros=new Array();
    $.each(elements,function(index,element) {
        var id=$(element).attr('id');
        numeros.push(id.substring(id.lastIndexOf('_')+1,id.length));
    });
    return numeros;
}

function reload_observers_cells() {
    if (privilege =='Affichage')
        return;
    
    $(selecteur_cellules).unbind();
    $(selecteur_cellules)
        .mousedown(function() {                                    
            if ($(this).hasClass('cloner'))
                return;
            first_cell=$(this);
            if (first_cell.prop('nodeType')=='DIV')
                first_cell=first_cell.parent();
            marquer_cellules(first_cell,first_cell);
          })
          
          .mousemove(function() {
            if (first_cell != null) {
                var element=$(this);
                var this_cell=element;
                if (this_cell.prop('nodeType')=='DIV')
                    this_cell=this_cell.parent();
                if (first_cell.prevAll().length == this_cell.prevAll().length) { // Meme colonne
                    marquer_cellules(first_cell,this_cell);
                }
            }
        });
    
    $('.option_etape span').mouseover(function() {
    	$(this).parent().children('.desc').removeClass('cache');
    });
    
    $('.option_etape span').mouseout(function() {
    	$(this).parent().children('.desc').addClass('cache');
    });

    $(window).mouseup(function() {
        $('.tmp').removeClass('tmp');
        first_cell=null;
    });
}

function reload_observers_options() {
    $('.lien_option').unbind('click');
}

function reload_observers_etapes() {

    $('#table_numeros .lien_etape>span').unbind('click')
                         				.click(function () {
        var element=$(this);
        if (element.prop('nodeType')!='TH')
            element=element.parent('th');
        var num_etape=element.data('etape');
        charger_etape(num_etape);
    });
    
    if (privilege =='Affichage')
        return;

    $('#table_numeros .supprimer_etape').unbind('click')
                         				.click( function () {
        var element=$(this).parent('th');
        if (element.hasClass('nouvelle')) {
            charger_liste_numeros(magazine);
			return;
        }
        var num_etape_a_supprimer=element.data('etape');
        var message = 'Etes vous sur(e) de vouloir supprimer ' + (num_etape_a_supprimer == -1 ? 'les informations sur les dimensions de tranches ?' : ('l\'etape '+num_etape_a_supprimer+' ?')); 
        if (confirm(message)) {
            $('#chargement').html('Suppression de l\'&eacute;tape '+num_etape_a_supprimer+'...');
            $.ajax({
                url: urls['supprimerg']+['index',pays,magazine,num_etape_a_supprimer].join('/'),
                type: 'post',
                success:function(data) {
        			if (typeof(data.erreur) != 'undefined')
                        jqueryui_alert(data.erreur);
                    else
                        charger_liste_numeros(magazine);
				}
            });
        }
    });

    $('#table_numeros .ajouter_etape').unbind('click')
    								  .click(function () {
        if ($('.nouvelle').length > 0) {
            jqueryui_alert('Une &eacute;tape est d&eacute;j&agrave; en train d\'&ecirc;tre ajout&eacute;e');
            return;
        }
        var element=$(this).parent('th');
        num_etape_avant_nouvelle=element.data('etape');
        if (typeof(num_etape_avant_nouvelle) == 'undefined')
            charger_liste_numeros(magazine);
		fermer_etapes();
        var liste_possibilites=$('<select>',{id:'liste_possibilites_fonctions'});
        if ($('[name="entete_etape_-1"]').length > 0) {
            liste_possibilites.append($('<option>',{title:'Remplir'}).html('Remplir une zone avec une couleur'))
                              .append($('<option>',{title:'Degrade'}).html('Remplir une zone avec un d&eacute;grad&eacute;'))
                              .append($('<option>',{title:'Agrafer'}).html('Agrafer la tranche'))
                              .append($('<option>',{title:'DegradeTrancheAgrafee'}).html('Remplir la tranche avec un d&eacute;grad&eacute; et l\'agrafer'))
                              .append($('<option>',{title:'Texte'}).html('Ajouter du texte'))
                              .append($('<option>',{title:'Image'}).html('Ins&eacute;rer une image'))
                              .append($('<option>',{title:'Rectangle'}).html('Dessiner un rectangle'))
                              .append($('<option>',{title:'Polygone'}).html('Dessiner un polygone'))
                              .append($('<option>',{title:'Arc_cercle'}).html('Dessiner un arc de cercle'));
        }
        else
            liste_possibilites.html($('<option>',{title:'Dimensions'}).html('Sp&eacute;cifier les dimensions d\'une tranche'));
        var bouton_ok=$('<button>').html('OK');
        $('#helpers').html('Si ce n\'est pas encore fait, prenez en photo avec un appareil photo num&eacute;rique la tranche que vous souhaitez recr&eacute;er.')
                     .append($('<br>'))
                     .append($('<span>').html('Stockez cette photo sur votre ordinateur, vous allez en avoir besoin !'))
                     .append($('<br>'))
                     .append($('<span>').html('Que voulez-vous faire ? '))
                     .append($('<br>'))
                     .append(liste_possibilites)
                     .append(bouton_ok);
        $('#infos').removeClass('cache');
        bouton_ok.click(function() {
            if ($('.nouvelle').length > 0 ) {
                jqueryui_alert('Une &eacute;tape est d&eacute;j&agrave; en train d\'&ecirc;tre ajout&eacute;e');
                return;
            }
            var name_sel=$('#liste_possibilites_fonctions :selected').attr('title');
            var nom_helper='';
            switch(name_sel) {
                case 'Texte':
                    nom_helper='whatthefont';
                break;
                default:
                    nom_helper=name_sel.toLowerCase();
                break;
            }
            charger_helper(nom_helper+'_1','helper_'+nom_helper,name_sel);
        });
    });
}

function reload_observers_tranches() {
    $('.ligne_previews .image_etape img')
      .mouseover (function() {
        if (drag_regle)
			return;
		$('#chargement').css({'display':'block'});
        var tranche=$(this);
        tranche.css({'cursor':'crosshair'});
        var nom_option_sel=get_nom_option_sel();
        if (nom_option_sel != null) {
           var element_modif=$('#valeur_modifiee').children('input,select').first();
           if (!isNaN(parseFloat(element_modif.val()))) {
               var valeur_modifiee_actuelle=element_modif.val() * zoom;
               if (nom_option_sel.match(/_x$/) != null) {
                   $('#viewer').append($('<div>').addClass('repere')
                                        .css({position:'absolute',
                                              top:tranche.offset().top+'px',
                                              width:'1px', 
                                              borderLeft:'2px solid black', 
                                              height: tranche.height+'px',
                                              left: parseInt(tranche.offset().left+valeur_modifiee_actuelle)+'px'}));
               }
               else  {
                   if (nom_option_sel.match(/_y$/) != null) {
                       $('#viewer').append($('<div>').addClass('repere')
                                            .css({position:'absolute',
                                                  left:tranche.offset().left+'px',
                                                  height: '1px', 
                                                  borderTop:'2px solid black', 
                                                  width: tranche.width()+'px',
                                                  top: parseInt(tranche.offset().top+valeur_modifiee_actuelle)+'px'}));
                   }
               }
           }
           adapter_scroll_reperes();
       }
       inserer_elements_coord();
    })
      .mouseout(function() {
        
        $('#chargement').html('');
        $(this).css({'cursor':''});
        $('.repere').remove();
    })
      .mousemove(function() {
        if (drag_regle)
			return;
		var tranche=$(this);
        var x =event.pageX - tranche.offset().left;
        var y =event.pageY - tranche.offset().top;
       
        var x_valeur = toAlwaysFloat(parseInt(10 * x/zoom)/10);
        var y_valeur = toAlwaysFloat(parseInt(10 * y/zoom)/10);
        var x_pct = toAlwaysFloat(parseInt(1000 * x / tranche.width())/10);
        var y_pct = toAlwaysFloat(parseInt(1000 * y / tranche.height())/10);
        if ($('#X').length == 0)
            inserer_elements_coord();
        $('#X').html(x_valeur+'mm (Largeur x '+x_pct+'%)');
        $('#Y').html(y_valeur+'mm (Hauteur x '+y_pct+'%)');
    });
}

function reload_observers_filtres() {
	$('#filtre_numeros button').click(function() {
		plage[0]=$('#filtre_debut').val();
		plage[1]=$('#filtre_fin').val();
		
		charger_liste_numeros(magazine);
	});
}

function inserer_elements_coord() {
    $('#chargement').html('X = ')
      .append($('<span>',{id:'X'}))
      .append($('<br>'))
      .append('Y = ')
      .append($('<span>',{id:'Y'}));
}

var chargements=new Array();
var chargement_courant;
var numero_chargement;

function preview_numero(element) {
	supprimer_regles(true);
    var numero=element.parent('tr').data('numero');
    
    var table=$('<table>',{cellspacing:'0',cellpadding:'0'})
        .css({marginLeft:(3*zoom)+'px'});
        
    switch(onglet_sel) {
       case 'Builder':
    	   if (privilege == 'Admin' || privilege == 'Edition')
    	        $('#contenu_builder .save').css({'display':'block'});
    	    
           $('#numero_preview').data('numero',numero);
           $('#numero_preview').html('N&deg; '+numero);
           
        for (var i=0;i<=4;i++) {
            var tr=$('<tr>');
            var td=$('<td>');
            if (i==3) {
                tr.addClass('ligne_previews');
            }
            tr.append(td);
            
            $.each(element.parent('tr').find('.num_checked'),function(index_ligne,td_etape) {
                var num_etape=$(td_etape).data('etape');
                if (num_etape != -1) {
                    var td=$('<td>').data('etape',num_etape+'');
                    switch(i) {
                        case 0:
                            td.addClass('reload')
                              .click(function() {
                                reload_etape($(this).data('etape'),true);
                            });
                            break;
                        case 1:
                            td.addClass('num_etape_preview')
                              .html(num_etape);
                            break;
                        case 2:
                            break;
                        case 3:
                            td.addClass('image_etape');
                            break;
                        case 4:
                            td.addClass('fond_noir_inactive')
                              .attr({id:'fond_noir_'+num_etape,
                                     title:'Voir sous fond noir'})
                              .click(function(event) {
                                var element=$(this);
                                var num_etape=element.data('etape');
                                element.toggleClass('fond_noir_active').toggleClass('fond_noir_inactive');
                                reload_etape(num_etape, false);
                            });
                            break;
                        }
                        tr.append(td);
                    }
                });
                var tranche_finale=$('<td>').data('etape','final').addClass('image_etape');
                if (i==1)
                    tranche_finale.html('Tranche')
                                  .addClass('num_etape_preview final');
                tr.append(tranche_finale);
                table.append(tr);
            }
            $('#contenu_'+onglet_sel.toLowerCase()).children('.previews:first').html(table);
            
            chargements=new Array();
            numero_chargement=numero;
            $.each($('.num_etape_preview'),function(index,td_num_etape) {
                if ($(td_num_etape).hasClass('final')) {
                	num_etape=new Array();
                    $.each($('.num_etape_preview:not(.final)'),function(i,td) {
                    	num_etape.push($(td).data('etape'));
                    });
                }
                else {
                    num_etape=$(td_num_etape).data('etape');
                }
                
                chargements.push(num_etape+'');
            });
            chargement_courant=0;
            charger_preview_etape(chargements[chargement_courant],true);
       break;
       case 'Previews':
           if (typeof($('#numero_preview_debut').data('numero')) == 'undefined') {
               changer_titres_images_view('Selectionner le dernier numero a previsualiser');
               
               jqueryui_alert('Vous allez pr&eacute;visualiser les tranches &agrave; partir du numero '+numero+'\n'
                    +'Cliquez sur le lien <img src="'+base_url+'/images/view.png"> du dernier num&eacute;ro &agrave; pr&eacute;visualiser');
               $('#numero_preview_debut').data('numero',numero)
                                         .html('N&deg; '+numero);
           }
           else {
               changer_titres_images_view('Selectionner le premier numero a previsualiser');

	       	   if (privilege == 'Admin' || privilege == 'Edition')
	       	        $('#contenu_previews .save, #contenu_previews .options').css({'display':'block'});
	       	    
               $('#numero_preview_fin').data('numero',numero)
                                       .html('N&deg; '+numero);
                                        
               var numero_debut = $('#numero_preview_debut').data('numero');
               var numero_fin = $('#numero_preview_fin').data('numero');
               for (var ligne=0;ligne<=2;ligne++) {
                var tr=$('<tr>');
                var td=$('<td>');
                if (ligne==2) {
                    tr.addClass('ligne_previews');
                }
                tr.append(td);
                var numero=numero_debut;
                var numero_fin_depasse=false;
                do {
                    numero_fin_depasse = (numero==numero_fin);
                    var td=$('<td>').data('numero',numero);
                    switch(ligne) {
                        case 0:
                            td.addClass('reload')
                              .click(function(event) {
                                var numero=$(this).data('numero');
                                reload_numero(numero);
                            });
                        break;
                        case 1:
                            td.addClass('numero_preview')
                              .css({'textAlign':'center'})
                              .html(numero);
                        break;
                        case 2:
                            td.addClass('image_numero');
                        break;
                    }
                    tr.append(td);
                    if (typeof ($('#ligne_'+numero).next()) == 'undefined' || numero_debut == numero_fin)
                        break;
                    numero = $('#ligne_'+numero).next().data('numero');
                	
                } while (!numero_fin_depasse);
                
                table.append(tr);
            }
            
            $('#contenu_'+onglet_sel.toLowerCase()).children('.previews:first').html(table);
            
            numero_chargement=null;
            chargements=new Array();
            $.each($('.numero_preview'),function(index,td_numero) {
                var numero=$(td_numero).data('numero');
                chargements.push(numero.toString());
            });
            chargement_courant=0;
            charger_previews_numeros(chargements[chargement_courant],true);
            
            $('#numero_preview_debut').removeData('numero');
        }
           
       break;
    }
    
}

var drag_regle=false;
var positionsDepartRegles;
var decalage_regle;
function fixer_regles(creer) {
	if (zoom <= 1)
		return;
	
	var premier_numero=get_onglet_courant().find('.image_preview').first();
	if (premier_numero.length == 0)
		return;
    var pos_premier_numero=premier_numero.offset();
	if (!creer) { // Changement de la position uniquement
		$('.regles.'+onglet_sel).css({'marginTop':pos_premier_numero.top-decalage_regle});
		return;
	}
	
    // La règle fait 300mm de hauteur
	var etendue= 300*zoom;
	
	$('.regles').remove();
    decalage_regle=0;
    positionsDepartRegles=new Array();
    var div_regles=$('<div>').addClass('regles '+onglet_sel).css({'position':'absolute'});
    $('#body').append(div_regles);
    
    $.each(['horizontale','verticale'],function(i,orientation_regle) {
    	var type_regle = (zoom <= 2 ? 'imprecise' : 'normale')
    					+(orientation_regle == 'horizontale' ? '_h':'');

    	var image_regle = $('<img>',{src:base_url+'/images/regle_'+type_regle+'.png'}).addClass('regle '+orientation_regle);
    	div_regles.append(image_regle);
    	image_regle.load(function() {
    		
    		if ($(this).hasClass('horizontale')) {
        		decalage_regle = parseInt(etendue*$(this).height()/$(this).width());
        		div_regles.css({'marginTop':pos_premier_numero.top-decalage_regle, 'marginLeft':pos_premier_numero.left-decalage_regle});
            	positionsDepartRegles.push({'top':div_regles.css('marginTop'), 'left': div_regles.css('marginLeft')});
            	
    			$(this).css({'marginLeft':decalage_regle,
            		  		 'width': etendue,
            		  		 'clip':'rect(0px '+Math.max(premier_numero.width()*1.1,10*zoom)+'px auto 0px)'});
    		}
    		else {
    			$(this).css({'marginTop':decalage_regle,
    						 'height': etendue,
            		  		 'clip':'rect(0px auto '+premier_numero.height()+'px 0px)'});
    		}
    	});
    	

        div_regles.draggable({
            axis:'x',
    		start:function(event,ui) { 
    			drag_regle=true;
			},
			drag:function(event,ui) {
			},
			stop: function() { 
				// Recherche de la tranche la plus proche
				var x_bordure_droite_regle_verticale = $(this).offset().left+decalage_regle;
				var tranche_proche=null;
				var minDistance=null;
				$.each($('.image_preview'),function() {
					var distance = Math.abs($(this).offset().left - x_bordure_droite_regle_verticale);
					if (minDistance == null || distance < minDistance) {
						tranche_proche=$(this);
						minDistance=distance;
					}
				});
				$('.regles.'+onglet_sel).css({'marginLeft':(tranche_proche.offset().left - decalage_regle),'left':0});
				
				/*var autre_regle=$('.regle.'+($(this).hasClass('verticale') ? 'horizontale' : 'verticale'));
				autre_regle.css({'left':parseInt(autre_regle.css('marginLeft').replace(/px/,''))+autre_regle.position().left,
								 'marginLeft':'0'});*/
				drag_regle=false;
			}
        });
        
        $('.regle').dblclick(function() {
        	$('.regles.'+onglet_sel).css({'marginLeft':positionsDepartRegles[0].left, 'left':0});
        });
    });
}

function supprimer_regles() {
	$('.regles.'+onglet_sel).remove();
}

function cacher_regles_sauf_onglet_courant() {
	$('.regles').addClass('cache');
	$('.regles.'+onglet_sel).removeClass('cache');
}

function reload_etape(num_etape,recharger_finale) {
    if ($(selecteur_cellules_preview).length == 2)
        recharger_finale=false;
    var num_etapes_final=$('.num_etape_preview:not(.final)').getData('etape');
    chargements=new Array();
    chargements[0]=num_etape;
    if (typeof(recharger_finale) == 'undefined' || recharger_finale)
        chargements.push(num_etapes_final);
    chargement_courant=0;
    charger_preview_etape(chargements[chargement_courant],true);
}

function reload_numero(numero) {
    chargements=new Array();
    chargements.push(numero);
    chargement_courant=0;
    charger_previews_numeros(chargements[chargement_courant],true);
}

function charger_previews_numeros(numero,est_visu) {
    numero_chargement=numero;
    var parametrage=new Object();
    var zoom_utilise= est_visu ? zoom : 1.5;
        
    $('#chargement').html('Chargement de la preview de la tranche');
    charger_image('numero',urls[url_viewer]+['index',pays,magazine,numero,zoom_utilise,'all',URLEncode(JSON.stringify(parametrage)),(est_visu?'false':'save'),'false'].join('/'),numero);
}

function charger_preview_etape(etapes_preview,est_visu, parametrage, callback) {
	if (parametrage==undefined)
		parametrage='_';
    var zoom_utilise= est_visu ? zoom : 1.5;
    if (etapes_preview == '')
        etapes_preview=-1;
	var fond_noir='false';
    if ((typeof(etapes_preview) == 'string' && etapes_preview.indexOf(',') == -1)
     || typeof(etapes_preview) == 'number')  {
        $('#chargement').html('Chargement de la preview de l\'&eacute;tape '+etapes_preview);
        fond_noir=($('#fond_noir_'+etapes_preview) 
					&& $('#fond_noir_'+etapes_preview).hasClass('fond_noir_active')) ? 'true':'false';
        var etapes_preview2 = etapes_preview;
        etapes_preview=new Array();
        etapes_preview.push(etapes_preview2);
    }
    else {
        $('#chargement').html('Chargement de la preview de la tranche');
        if (typeof(etapes_preview) == 'string')
            etapes_preview=etapes_preview.split(/,/g);
    }
    charger_image('etape',urls[url_viewer]+['index',pays,magazine,numero_chargement,zoom_utilise,etapes_preview.join("-"),parametrage,(est_visu?'false':'save'),fond_noir].join('/'),etapes_preview.join("-"),callback);
}

var selecteur_cellules_preview=null;


function charger_image(type_chargement,src,num,callback) {
	callback= callback || function(){};
		
	var est_etape_ouverte = modification_etape != null && modification_etape.data('etape') == num;
    var image=$('<img>')
    	.addClass('image_preview'+(est_etape_ouverte ? ' cache':''))
    	.data(type_chargement,num);
    var est_visu=src.indexOf('/save') == -1;
    if (est_visu) {
        var random=Math.random();
        src+='/'+random;
    }
    else {
        src+='/'+username;
        switch(privilege) {
            case 'Admin':break;
            case 'Edition':
                if (!confirm('Votre modele de tranche va etre envoye au webmaster pour validation. Continuer ?'))
                    return;
            break;
            default:
                jqueryui_alert('Vous ne poss&eacute;dez pas les droits n&eacute;cessaires pour cette action');
                return;
            break;
        }
    }
    if (type_chargement == 'etape') {
        var etapes_corresp=$(selecteur_cellules_preview).getElementsWithData('etape',num);
        if (etapes_corresp.length == 0) {// Numéro d'étape non trouvé
        	jqueryui_alert("Num&eacute;ro d'&eacute;tape non trouv&eacute; lors du chargement de la preview : " + num, "Erreur");
        	charger_image_suivante(null,callback,type_chargement,est_visu);
    	}
        else {
        	etapes_corresp.html(image);
        }
    }
    else {
    	$(selecteur_cellules_preview).getElementsWithData('numero',num).html(image);
    }
    image.load(function() {
    	if (!est_visu && chargement_courant >= chargements.length) {
	        switch(privilege) {
		        case 'Admin':
		            if (type_chargement=='etape')
		                jqueryui_alert($('<div>').html('Image enregistr&eacute;e')
		                		.after($('<a>',{'href':'../../edges/'+pays+'/gen/'+magazine+'.'+numero+'.png','target':'_blank'}).html('&gt; Voir l\'image enregistr&eacute;e')));
		            else
		                jqueryui_alert('Images enregistr&eacute;es');
		            $('#ligne_'+numero_chargement).addClass('cree_par_moi');
		            
		        break;
		        case 'Edition':
		            if (type_chargement=='etape')
		                jqueryui_alert('Votre proposition de mod&egrave;le a ete envoy&eacute;e au webmaster pour validation. Merci !');
		            else
		                jqueryui_alert('Vos propositions de mod&egrave;les ont ete envoy&eacute;es au webmaster pour validation. Merci !');
		            $('#ligne_'+numero_chargement).addClass('tranche_en_validation');
		            
		        break;
		    }
		}
        charger_image_suivante($(this),callback,type_chargement,est_visu);
        callback(image);
    });
    
    image.error(function() {
    	var num_etape=chargements[chargement_courant];
    	if (num_etape != 'all') { // Si erreur sur l'étape finale c'est qu'il y a eu erreur sur une étape intermédiaire ; on ne l'affiche pas de nouveau
	        var texte_erreur=$('<p>')
	        	.append($('<p>').html("La g&eacute;n&eacute;ration de l'image pour l'&eacute;tape "+num_etape+" a &eacute;chou&eacute;"))
	        	.append($('<br>'))
	        	.append($('<p>').html("La g&eacute;n&eacute;ration des images des &eacute;tapes suivantes a &eacute;t&eacute; annul&eacute;e."))
	        	.append($('<p>').html("Merci de reporter ce probl&egrave;me au webmaster en indiquant le message d'erreur suivant :"))
	        	.append($('<br>'))
	        	.append($('<iframe>',{'src':$(this).attr('src')+'/debug'}));
	        jqueryui_alert(texte_erreur, "Erreur de g&eacute;n&eacute;ration d'image");
    	}
        charger_image_suivante($(this),callback,type_chargement,est_visu);
        callback(image);
    });
    image.attr({'src':src});
}

function charger_image_suivante(image,callback,type_chargement,est_visu) {
	chargement_courant++;
    
    if ($(selecteur_cellules_preview).length == 2 && chargement_courant == 1)
        $(selecteur_cellules_preview).last().html(image.clone(false));
    
    $('#chargement').html('');
    $('#erreurs').html('');
    if (chargement_courant < chargements.length) {
        if (chargement_courant == 1)
        	fixer_regles(true);
        
        if (type_chargement=='etape')
            charger_preview_etape(chargements[chargement_courant],est_visu, undefined, callback);
        else
            charger_previews_numeros(chargements[chargement_courant],est_visu);
    }
    else {
    	chargement_courant=0;
    	chargements=[];
        reload_observers_tranches();
        if (type_chargement=='numero')
        	$('#numero_preview_debut').data('numero',null);
    }
}

function toAlwaysFloat(val) {
    return parseInt(val) == parseFloat(val) ? val+'.0' : val;
}

function marquer_cellules(first_cell,last_cell) {
    if (!colonne_ouverte)
        etape_en_cours=$('.ligne_etapes:first>th:nth-child('+(first_cell.prevAll().length+1)+')').data('etape');
    $('.selected.tmp').removeClass('selected tmp');
       
    if ($('.selected').length > 0 && (first_cell.prevAll().length != $('.selected').first().prevAll().length))
    	$('.selected').removeClass('selected');
    
    var pos_colonne=first_cell.prevAll().length+1;
    if (first_cell.parent('tr').prevAll().length > last_cell.parent('tr').prevAll().length) { 
        // Echange de la 1ere et derniere cellule
        var temp_cell=first_cell;
        first_cell=last_cell;
        last_cell=temp_cell;
    }
    var current_cell=first_cell;
    while (true) {
        if (!(current_cell.hasClass('tmp'))) {
            current_cell.toggleClass('selected');
            current_cell.addClass('tmp');
        }
        if (current_cell.parent().attr('id') == last_cell.parent().attr('id')) // Derniere cellule de la selection
            break;
        if (current_cell.parent('tr').next().length==0) // Derniere ligne du tableau
            break;
        current_cell=current_cell.parent('tr').next().find('td:nth-child('+pos_colonne+')');
    }
    assistant_cellules_sel();
}

var nom_option = null; // Nom d'option en cours de modif

function assistant_cellules_sel() {
    if ($('td.selected').length > 0) {
    	$('#toggle_helpers,#infos').removeClass('cache');
    	var texte=$('<div>').append($('<span>').css({fontWeight:'bold'})
                                               .html($('td.selected').length+' num&eacute;ro(s) s&eacute;lectionn&eacute;(s)'))
                            .append($('<br>'));
        nom_option=get_nom_option_sel();
        var lien_annuler_selection=$('<a>',{href:'javascript:void(0)'})
            .html('Annuler la s&eacute;lection')
            .click(function() {
	            $('td.selected').removeClass('selected');
	        });
        
        
        var liste_valeurs=$('<ul>');

        var texte_erreurs=new Array('Erreur : ');
        var liste_valeurs_tab=new Array();
        $.each($('td.selected'),function(index,td_sel) {
        	var valeur=$(td_sel).data('valeur_reelle') == null ? '[Non d&eacute;fini]' : $(td_sel).data('valeur_reelle');
        	if (liste_valeurs_tab.indexOf(valeur) == -1) {
        		liste_valeurs.append($('<li>').append(valeur));
        		liste_valeurs_tab.push(valeur);
        	}
        });
        
        texte.append(lien_annuler_selection)
             .append($('<br>'))
             .append($('<br>'))
        	 .append('Etape : '+etape_en_cours+'&nbsp;')
             .append($('<br>'))
        	 .append('Option : '+(nom_option == 'Actif' ? 'Etape active' : nom_option));
        if (typeof(descriptions_options[nom_option]) != 'undefined') {
        	texte.append(' (')
        		 .append($('<i>').append(descriptions_options[nom_option]))
        		 .append(')');
        }
        texte.append($('<br>'))
        	 .append('Valeurs actuelles :')
        	 .append(liste_valeurs);
        var section_modifier_valeur=$('<div>').attr({id:'modifier_valeur'})
                                              .append('Modifier la valeur : ')
                                              .append($('<br>'))
                                              .append($('<div>',{id:'valeur_modifiee'}));
        
        if (privilege != 'Affichage')
            $('#helpers').html(texte).append(section_modifier_valeur);
        section_modifier_valeur_terminee=false;
        var succes_formatage=formater_modifier_valeur(nom_option);
        if (succes_formatage) {
            section_modifier_valeur.append($('<button>',{id:'modifier_valeur_ok'})
                                   .html('OK'));
            $('#modifier_valeur_ok').click(valider_modifier_valeur);
        }
        else {
            section_modifier_valeur.append('L\'un au moins des num&eacute;ros s&eacute;lectionn&eacute;s n\'est pas actif pour cette &eacute;tape.')
                                   .append($('<br>'))
                                   .append('Commencez par d&eacute;finir l\'&eacute;tape comme active pour ce num&eacute;ro.');
        }
        
        if (texte_erreurs.length == 1)
            $('#erreurs').html('');
        else
            $('#erreurs').html(texte_erreurs.join('<br />'));
    }
    else if (privilege !='Affichage') {
        $('#helpers').html('');
        $('#toggle_helpers,#infos').addClass('cache');
    }
}

function valider_modifier_valeur() {
    $('#modifier_valeur_ok').attr({'disabled':'disabled'});
    $('#chargement').html('Enregistrement des param&egrave;tres...');
    var numeros=element_to_numero($('td.selected').parent('tr'));
    var nouvelle_valeur=get_nouvelle_valeur(nom_option).replace(/\\.','g/,'[pt]').replace(/\#/g,'');
    var est_nouvelle_fonction=etape_temporaire_to_definitive() ? 'true':'false';
    $.ajax({
        url: urls['modifierg']+['index',pays,magazine,etape_en_cours,numeros.join('~'),nom_option,nouvelle_valeur,plage.join('/'),nom_nouvelle_fonction==null?'Dimensions':nom_nouvelle_fonction,est_nouvelle_fonction].join('/'),
        type: 'post',
        success:function(data) {
            if (typeof(data.erreur) !='undefined') {
                jqueryui_alert(data);
                return;
            }
                
            $('#chargement').html('');
            var recharger_etape = nom_option != 'Actif';
            
            reload_observers_etapes();
            
            if (nom_option=='Actif') {
                if (nouvelle_valeur=='on')
                    $('td.selected')
                        .addClass('num_checked');
                else
                    $('td.selected')
                        .removeClass('num_checked');
                if ($('.etape_ouverte').length > 0 && $('.etape_ouverte:first').data('etape') == etape_en_cours) {
                    fermer_etapes();
                    charger_etape(etape_en_cours);
                }
            }
            else {
                if (numeros.indexOf($('#numero_preview').data('numero')) != -1 && recharger_etape) {
                    if (etape_en_cours == -1) {
                    	chargements=new Array();
                        $.each($('.num_etape_preview'),function(index,td_etape) {
                        	chargements.push(td_etape.data('etape'));
                        });
                        chargement_courant=0;
                        charger_preview_etape(chargements[chargement_courant],true);
                    }
                    $.each($('.num_etape_preview'),function(index,etape_preview) {
                    	if ($(etape_preview).data('etape') == (etape_en_cours+''))
                    		reload_etape(etape_en_cours+'');
                    });
                }
            }
            etape_temporaire_to_definitive();
            
            if (recharger_etape) {
                charger_etape(etape_en_cours, numeros, nom_option, true);
            }
            $('#modifier_valeur_ok').attr({'disabled':''});
        }
    });
  }

function get_nom_option_sel() {
    if ($('td.selected').length==0)
        return null;
    var pos_colonne_sel=$('td.selected:first').prevAll().length+1;
    var nom_option=$('.ligne_noms_options:first').find('th:nth-child('+pos_colonne_sel+')').data('nom_option');
    if (typeof(nom_option) == 'undefined' || nom_option == '')
        nom_option='Actif';
    return nom_option;
}

function etape_temporaire_to_definitive() {
    var etapes_maj=false;
    $.each($('.lien_etape'),function(index,td_etape) {
        var etape=$(td_etape).data('etape');
        if (parseInt(etape) != etape) {
            etape=parseInt(etape+.5);
            $(td_etape).attr({name:'entete_etape_'+etape})
                    .data('etape',etape)
                    .find('.numero_etape').html('Etape '+etape);
            etapes_maj=true;
        }
    });
    if (parseInt(etape_en_cours) != etape_en_cours)
        etape_en_cours+=0.5;
    
    $.each($('.num_checked'),function(index,td) {
        var etape=$(td).data('etape');
        if (parseInt(etape) != etape)
            $(td).data('etape',parseInt(etape+.5));
    });
    
    $('.nouvelle').removeClass('nouvelle');
    
    if (etapes_maj) {
        reload_observers_etapes();
        return true;
    }
    return false;
}

function sans_doublons(tab){
    NvTab= new Array();
    var q=0;
    $.each(tab,function(i,x){
        if (NvTab[q] && NvTab[q].data('valeur_reelle').indexOf(x.data('valeur_reelle')) == -1)
            NvTab[q++]=x;
    });
    return NvTab;
}

var types_options=new Array();
types_options['Actif']='actif';

function formater_valeur(td,nom_option,valeur) {
    if (valeur == null || typeof (valeur) == 'undefined')
        valeur='[Non d&eacute;fini]';

    else if (nom_option.indexOf('Couleur') != -1) {
		var rgb=null;
        if (valeur.indexOf(',') == -1)
            rgb=[hexToR(valeur),hexToG(valeur),hexToB(valeur)];
        else
            rgb=valeur.split(/,/g);
        var couleur_texte =  0.213 * rgb[0] +
                             0.715 * rgb[1] +
                             0.072 * rgb[2]
                             < 0.5 ? '#FFF' : '#000';
        td.css({backgroundColor:'rgb('+rgb.join(',')+')',
                color:couleur_texte});
    }
    else if (nom_option.indexOf('Dimension') != -1 || nom_option.indexOf('Decalage') != -1 || nom_option.indexOf('Pos_x') != -1 || nom_option.indexOf('Pos_y') != -1 || nom_option.indexOf('Y1') != -1  || nom_option.indexOf('Y2') != -1)
        valeur+=' mm';
    else if (nom_option.indexOf('Compression') != -1)
        valeur=parseInt(valeur*100)+'%';
    else if (nom_option.indexOf('Rotation') != -1)
        valeur+='&deg;';
    td.html(valeur);
    return td;
}

function hexToR(h) {return parseInt((cutHex(h)).substring(0,2),16);}
function hexToG(h) {return parseInt((cutHex(h)).substring(2,4),16);}
function hexToB(h) {return parseInt((cutHex(h)).substring(4,6),16);}
function cutHex(h) {return (h.charAt(0)=="#") ? h.substring(1,7):h;}

var section_modifier_valeur_terminee=false;
function formater_modifier_valeur(nom_option) {
    if (nom_option=='Actif') {
        $('#valeur_modifiee').html($('<input>',{type:'checkbox',checked:'checked'}))
                             .append('&nbsp;Etape active');
        return true;
    }
    if ($('td.selected:not(.non_concerne').length > 0) { // Au moins un des numeros n'est pas defini pour cette etape
        return false;
    }
    var premiere_valeur_sel=$('td.selected:first').data('valeur_reelle');
    if (typeof(premiere_valeur_sel) == 'undefined')
        premiere_valeur_sel='';

    var input_valeur = null;
    switch(types_options[nom_option]) {
        case 'couleur':
        	$('#valeur_modifiee').append($('<div>',{'id':'picker'}));
        	if (premiere_valeur_sel == undefined)
            	$('#valeur_modifiee').append($('<input>').val('#ff0000'));
        	else
	        	$('#valeur_modifiee').append($('<input>').val(premiere_valeur_sel));
            $('#picker').farbtastic('#valeur_modifiee input');
        break;
        case 'liste': case 'fichier_ou_texte':
            var arg=nom_option=='Source' ? pays : '_';
            $.ajax({
                url: urls['listerg']+['index',nom_option,arg].join('/'),
                data:'select=true',
                dataType:'json',
                type: 'post',
                success:function(data) {
                    if (section_modifier_valeur_terminee)
                        return;
                    var select = $('<select>').addClass('switchable');
                    var valeur_trouvee=false;
                    for(var nom in data) {
                        var option=$('<option>').val(nom).html(data[nom]);
                        if (option.val()==premiere_valeur_sel) {
                            option.attr({'selected':'selected'});
                            valeur_trouvee=true;
                        }
                        select.append(option);
                    }
                    $('#valeur_modifiee').html(select);
                    if (types_options[nom_option] == 'fichier_ou_texte' && !$('section_texte_variable')) {
                        var div_texte_variable=$('<div>',{id:'section_texte_variable'});
                        $('#valeur_modifiee').append({'after':div_texte_variable});
                        var lien_texte_variable=$('<a>').html('nom de fichier variable').addClass('switchable');
                        var lien_nom_fichier=$('<a>').html('nom de fichier fixe').addClass('switchable cache');
                        var input_valeur=$('<input>',{type:'text'})
                                            .val(premiere_valeur_sel)
                                            .addClass('switchable cache');
                        div_texte_variable.append(input_valeur)
                                          .append('&nbsp;ou&nbsp;')
                                          .append(lien_texte_variable)
                                          .append(lien_nom_fichier);
                        [lien_texte_variable,lien_nom_fichier].click(toggleSwitchables);
                        
                        if (!valeur_trouvee && premiere_valeur_sel != '')
                            toggleSwitchables();
                        section_modifier_valeur_terminee=true;
                    }
                }
            });
        break;
        default:
            input_valeur = $('<input>',{type:'text'}).val(premiere_valeur_sel);
            $('#valeur_modifiee').html(input_valeur);
        break;
    }
    
    if (input_valeur != null) {
        input_valeur.keydown(function() {
            if (event.keyCode == 13)
                valider_modifier_valeur();
        });
        //input_valeur.focus();
    }

    return true;
}

function toggleSwitchables(ev) {
    if (!$('valeur_modifiee').children('select:first').hasClass('cache') && !$('section_texte_variable').children('input:first').hasClass('cache'))
        $('#valeur_modifiee').children('select:first').addClass('cache');
    else
        $('#modifier_valeur').find('.switchable').toggleClass('cache');
}

function get_nouvelle_valeur(nom_option) {
    switch(types_options[nom_option]) {
        case 'fichier_ou_texte':
            var element= $('#valeur_modifiee').children().first().hasClass('cache') ? $('#section_texte_variable').children().first() : $('#valeur_modifiee').children().first();
            return element.val();
        break;
        default:
            return $('#valeur_modifiee').children('select,input').first().val();
        break;
    }
}

var valeurs_defaut_options;
var etapes_utilisees=new Array();
var etapes_valides=new Array();
var etape_en_cours=null;

var nb_lignes=null;

var image_ajouter=$('<img>',{title:'Ajouter une etape',
                             src:base_url+'images/ajouter.png'})
                    .addClass('ajouter_etape');
var image_supprimer=$('<img>',{title:'Supprimer l\'etape',
                               src:base_url+'images/supprimer.png'})
                    .addClass('supprimer_etape');

var num_etape_avant_nouvelle=null;

var parametres_helper=new Object();

var onglet_sel='builder';

var pays_sel=null;

$(window).load(function() {
    if (!$('#viewer'))
        return;
    
    $('#connexion,#deconnexion').button();
    $('.tip').tooltip();
	
    $('#tabs').tabs({
    	show:function(event,ui) {
    		onglet_sel=$(ui.tab).text();
    		cacher_regles_sauf_onglet_courant();
    		fixer_regles(true);
    	    var type_chargement=onglet_sel=='Builder' ? 'etape' : 'numero';
    	    selecteur_cellules_preview='#contenu_'+onglet_sel.toLowerCase()+' .ligne_previews .image_'+type_chargement;

    	    if (onglet_sel=='Builder')
    	        var titre_image_view='Voir la composition de cette tranche';
    	    else
    	        titre_image_view='Selectionner le premier numero a previsualiser';
    	    changer_titres_images_view(titre_image_view);
    	}
    });
     
    $('#viewer').resizable({
        handles: 'e',
        minWidth: 200,
        stop: function(event, ui) {
        	$('#corps').css({'marginLeft':($(this).width()-200)+'px'});
        }
    });
    
    $('#liste_pays').change(function() {
        var element=$(this);
        var nouveau_pays=element.val();
        charger_liste_magazines(nouveau_pays);
	});
	
	$('#liste_magazines').change(function() {
		charger_liste_numeros($(this).val());
	});
	
	reload_observers_filtres();
	
	$('#chargement').html('Chargement des pays...');
    
    $.ajax({
        url: urls['numerosdispos']+['index'].join('/'),
        dataType:'json',
        type: 'post',
        success:function(data) {
            if (privilege != 'Affichage') {
                var toggle_iframe_upload=$('<span>',{id:'toggle_iframe_upload'}).html('^');
                var lien_upload=$('<a>',{href:'javascript:void(0)'})
                    .css({'float':'right'})
                    .html('Envoyer une image &agrave; EdgeCreator&nbsp;')
                    .append(toggle_iframe_upload);
                lien_upload.click(function(event) {
                    if ($('#iframe_upload').length > 0) {
                        $('#iframe_upload').remove();
                        $('#toggle_iframe_upload').html('^');
                    }
                    else {
                        var iframe_upload=$('<iframe>',{id:'iframe_upload',
                                                        src:base_url+'index.php/helper/index/image_upload.php'});
                        $('#upload_fichier').html(iframe_upload);
                        $('#toggle_iframe_upload').html('v');
                    }
                });
                $('#upload_fichier').append(lien_upload).append($('<br>'));
            }
            for (var i in data.pays) {
                $('#liste_pays')
                    .append($('<option>').val(i)
                                .html(data.pays[i]));
            }
            pays_sel = pays == '' || typeof($('#liste_pays').children('[value="'+pays+'"]:first')) == 'undefined' ? 'fr' : pays;
            $('#liste_pays').prop('selectedIndex',$('#liste_pays').children('[value="'+pays_sel+'"]:first').index());
            
			charger_liste_magazines(pays_sel);
        }
    });
    
    if (pays != "" && magazine != "")
        charger_liste_numeros(magazine);

    $('#zoom_slider').slider({
        value:1 /* Valeur n°1 du tableau, donc = 1.5*/,
        min:0,
        max:valeurs_possibles_zoom.length-1,
        step:1,
        change: function(event,ui) {
            zoom=valeurs_possibles_zoom[ui.value];
            $('#zoom_value').html(zoom);
        	if (onglet_sel == 'Builder') {
                if ($('#numero_preview').data('numero') != null)
                    preview_numero($('#ligne_'+$('#numero_preview').data('numero')).children('.intitule_numero:first'));
            }
            else {
            	var premier_numero=get_onglet_courant().find('.numero_preview').first().html();
            	var dernier_numero=get_onglet_courant().find('.numero_preview').last().html();
               
            	var numero=premier_numero;
            	var chargements=new Array();
            	do {
            		chargements.push(numero);
            		var ligne=$('#ligne_'+numero).next();
            		numero = ligne.data('numero');
            	} while (numero != dernier_numero || typeof(numero) == 'undefined');
               
            	chargement_courant=0;
            	charger_previews_numeros(chargements[chargement_courant],true);
            }
          },
          slide: function(event,ui) {
            $('#zoom_value').html(valeurs_possibles_zoom[ui.value]);
          }
    });
    
    
    $('.option_previews input').click(function() {
        var element=$(this);
        switch (element.attr('id')) {
            case 'option_details':
                $('#contenu_previews')
                    .find('.numero_preview, .reload')	
                    .css({display:element.is(':checked') ? 'block' : 'none'});
                fixer_regles(false);
            break;
            case 'option_pretes_seulement':
                
            break;
        }
    });
    
    if (privilege == 'Admin' || privilege == 'Edition') {
        $('#save_png').click(function() {
           if (typeof (numero_chargement) != 'undefined') {
        	   $.ajax({
                   url: urls['listerg']+['index','Utilisateurs',[pays,magazine,numero_chargement].join('_')].join('/'),
                   dataType:'json',
                   type: 'post',
                   success:function(data) {
                	   var boite_save_png=$('<div>',{'id':'dialog_save_png','title':'Enregistrement de la tranche'});
                	   boite_save_png.append($('<p>').html('Veuillez s&eacute;lectionner les photographes (utilisateurs qui ont photographi&eacute; la tranche) '
                			   							  +'et les designers (utilisateurs qui ont recr&eacute;&eacute; la tranche via EdgeCreator) :'));

                	   var span_photographes=$('<span>',{'id':'photographes'})
                	   		.append('Photographes');
                	   
                	   var span_designers=$('<span>',{'id':'designers'})
                	   		.css({'marginLeft':'30px'})
                	   		.append('Designers');
                	   
                	   
                	   boite_save_png.append($('<form>',{'id':'form_save_png'}).append(span_photographes).append(span_designers));
                	   
                	   $.each(boite_save_png.find('span'),function(i,span) {
                		   var div=$('<div>');
                		   for (var username in data) {
                			   var option = $('<input>',{'name':$(span).attr('id'),'type':'checkbox'}).val(username);
                			   var coche=(data[username].indexOf('p') != -1 && $(span).attr('id') == 'photographes')
		   					    	  || (data[username].indexOf('d') != -1 && $(span).attr('id') == 'designers');
                			   option.prop({'checked': coche, 'disabled': coche});
                			   $(div).append($('<div>').css({'font-weight':coche?'bold':'normal'}).append(option).append(username));
                		   }
                		   $(span).append(div);
                	   });
                	   
                	   $('#body').append(boite_save_png);
                	   boite_save_png.dialog({
                		   modal:true,
                		   width: 450,
                		   buttons: {
                			   OK:function() {
                				   var a=$('#form_save_png').serialize();
                				   var b;
                			   }
                		   }
                	   });
                   }
        	   });
        	   	/*var num_etapes_final=$('.num_etape_preview:not(.final)').getData('etape');
                chargements=new Array();
                chargements.push(num_etapes_final);
                chargement_courant=0;
                charger_preview_etape(chargements[chargement_courant],false);*/
           }
        });
        $('#save_pngs').click(function() {
            numero_chargement=null;
            chargements=new Array();
            $.each($('.numero_preview'),function(i,td_numero) {
                var numero=$(td_numero).data('numero');
                chargements.push(numero.toString());
            });
            chargement_courant=0;
            charger_previews_numeros(chargements[chargement_courant],false);
        });
    }
    if (privilege != 'Affichage') {
        $('#toggle_helpers').click(function() {
            $('#toggle_helpers').html(
                ($('#infos').hasClass('cache') ? 'Cacher':'Montrer')
                +' l\'assistant');
           $('#infos').toggleClass('cache');
        });
    }
    
    $('#viewer_inner').scroll(function() {
		adapter_scroll_reperes();
		fixer_regles(false);
	});
});

function charger_liste_magazines(pays_sel) {
    $('#chargement').html('Chargement des magazines...');
	pays=pays_sel;
	$('#liste_magazines').children().remove();
	$.ajax({
		url: urls['numerosdispos']+['index',pays].join('/'),
		type:'post',
		dataType: 'json',
		success:function(data) {
			for (var i in data.magazines) {
				$('#liste_magazines')
					.append($('<option>').val(i)
						  .html(data.magazines[i]));
			}
			if (typeof(magazine) != 'undefined' && magazine != null)
				$('#liste_magazines').prop('selectedIndex',$('#liste_magazines').children('[value="'+magazine+'"]').first().index());

            $('#chargement').html('');
			
			if (username == 'demo') {
				afficher_dialogue_accueil();
			}
			else {
				if (!mode_expert) { // Lancement de l'assistant
				    $('.wizard button').button();
				    launch_wizard('wizard-1');
				    init_action_bar();
				}
			}
		}
	});
}


function charger_liste_numeros(magazine_sel) {
	magazine=magazine_sel;
	$('#chargement').html('Chargement de la liste des num&eacute;ros...');
	$.ajax({
		url: urls['numerosdispos']+['index',pays,magazine].join('/'),
		type: 'post',
		dataType: 'json',
		success:function(data) {
			if (typeof(data.erreur) != 'undefined' && data.erreur=='Nombre d\'arguments insuffisant') {
				$('#nom_magazine').html('Utilisez un nom de magazine valide');
				return;
			}
			numeros_dispos=data.numeros_dispos;
			var tranches_pretes=data.tranches_pretes;

			$.each($('#filtre_debut,#filtre_fin'),function(index,filtre_select) {
				$(filtre_select).html('');
				for (var numero_dispo in numeros_dispos)
					if (numero_dispo != 'Aucun') {
						var option=$('<option>').val(numero_dispo).html(numero_dispo);
						var est_dispo=typeof(tranches_pretes[numero_dispo]) != 'undefined';
						if (est_dispo) {
							option.addClass(tranches_pretes[numero_dispo] == 'par_moi'
											 ? 'cree_par_moi'
											 : 'tranche_prete');
						}
						$(filtre_select).append(option);
					}
			});
			
			if (typeof($('#filtre_fin').find('option:last')) !='undefined')
				$('#filtre_fin').prop('selectedIndex',$('#filtre_fin').find('option:last').index());
	
			recharger_selects_filtres();
			
			var nb_numeros_plage=$('#filtre_fin').prop('selectedIndex')-$('#filtre_debut').prop('selectedIndex');
			if (nb_numeros_plage >= 1000)
				if (restriction_plage())
					return;
		

			$('#table_numeros').remove();
			
			if ($('#filtre_fin').children().length == 0 ){
				$('#corps').append($('<div>',{'id':'table_numeros'})
								.html('Pas de numero r&eacute;f&eacute;renc&eacute; pour ce magazine !')
						  );
				return;
			}
			
			var table=$('<table>',{id:'table_numeros','border':'1'})
						.addClass('bordered')
						.append($('<tr>').addClass('ligne_entete ligne_etapes')
										 .append($('<th>'))
										 .append($('<th>'))
										 .append($('<th>')) // Cellule temporaire
							   )
						.append($('<tr>').addClass('ligne_entete ligne_noms_options')
										 .append($('<th>'))
										 .append($('<th>'))
										 .append($('<th>'))
							   )
						.disableTextSelect();
			$('#corps').append(table);

			
			var debut_plage_atteint=false;
			var fin_plage_atteint=false;
			for (var numero_dispo in numeros_dispos) {
				if (plage[0] != 'null') {
					if (!debut_plage_atteint) {
						if (numero_dispo == plage[0])
							debut_plage_atteint=true;
						else
							continue;
					}
					if (debut_plage_atteint) {
						if (fin_plage_atteint)
							break;
						if (numero_dispo == plage[1])
							fin_plage_atteint=true;
					}
				}
				if (numero_dispo == 'Aucun')
					continue; 
				var td_cloner=$('<td>');
				if (privilege == 'Admin' || privilege == 'Edition') {
					td_cloner.addClass('cloner')
							 .attr({title:'Cloner le numero'})
							 .data('numero',numero_dispo)
							 .click(cloner_numero);
				}
				var tr=$('<tr>',{id:'ligne_'+numero_dispo})
							.addClass('ligne_dispo')
							.data('numero',numero_dispo)
							.append(td_cloner);
				if (typeof(tranches_pretes[numero_dispo]) != 'undefined') {
					if (tranches_pretes[numero_dispo] == 'par_moi') {
						tr.addClass('cree_par_moi');
						tr.attr({title:'Vous avez contribue a modeliser cette tranche.'});
					}
					else {
						tr.addClass('tranche_prete');
						tr.attr({title:'Cette tranche est modelisee.'});
					}
				}
				var td=$('<td>',{title:'Voir la tranche'})
						.addClass('intitule_numero preview')
						.append(numero_dispo).append('&nbsp;');                    
				table.append(tr.append(td));

				td.click(function() {
					preview_numero($(this));
				});
							
				tr.append($('<td>'));
			}
			
			$('#chargement').html('Chargement des &eacute;tapes...');
			table.append($('.ligne_noms_options:first').clone(true))
				 .append($('.ligne_etapes:first').clone(true));
			$.ajax({
				url: urls['parametrageg']+['index',pays,magazine,'null','null'].join('/'),
				type: 'post',
				dataType: 'json',
				success:function(data) {
					var etapes=data;
					nb_lignes = $('#table_numeros').find('tr').length;
					etapes_valides=new Array();
					$.each($('#table_numeros tr:not(.ligne_entete)'),function(i,tr) {
						for (var etape=0;etape<etapes.length;etape++) {
							if (etapes[etape].Ordre == -1 || est_dans_intervalle($(tr).data('numero'), etapes[etape].Numero_debut+'~'+etapes[etape].Numero_fin)) {
								if (etapes_valides.indexOf(etapes[etape]) == -1) {
									etapes_valides.push(etapes[etape]);
									continue;
								}
							}
						}
					});
					
					if (etapes_valides.length * nb_numeros_plage >= 1000)
						if (restriction_plage())
							return;

					etapes_valides.sort(function(etape1,etape2) {
						if (parseInt(etape1.Ordre)<parseInt(etape2.Ordre))
							return -1;
						if (parseInt(etape1.Ordre)>parseInt(etape2.Ordre))
							return 1;
						return 0;
					});
					
					$.each($('#table_numeros').find('tr'),function(index,tr) {
						for (var i=0;i<etapes_valides.length;i++) {
							charger_etape_ligne(etapes_valides[i],$(tr));
						}
					});


					$.each($('.ligne_entete td'),function(i,td) {
						$(td).replaceWith($('<th>'));
					});
					
					$.each($(selecteur_cellules),function(i,td) {
						$(td).data('valeur_reelle','Etape '+($(td).hasClass('num_checked') ? '':'in')+'active');
					});
					
					//table.scrollbarTable();
					reload_observers_etapes();
					
					reload_observers_cells();
					$('#chargement').html('');
					}
				}
			);
		}
	});
}
   
function adapter_scroll_reperes() {
    $.each($('.repere'),function(i,repere) {
        $(repere).css({'marginTop': ((-1)*$('#viewer_inner').scrollTop()) +'px',
                       'marginLeft':((-1)*$('#viewer_inner').scrollLeft())+'px'});
    });
}

function changer_titres_images_view(titre_image_view){
    $('.preview img').attr({'title':titre_image_view});
}

function charger_etape_ligne (etape, tr, est_nouvelle) {
    est_nouvelle=typeof(est_nouvelle) != 'undefined';
    var est_ligne_header = tr.children('th').length > 0;
    var balise_cellule = est_ligne_header ? 'th':'td';
    var num_etape=etape.Ordre;
    var cellule=null;
    if (num_etape==-1) { // cellule deja existante
        cellule=tr.children(balise_cellule+':nth-child('+3+')');
    }
    else {
        var num_etape_precedente=parseInt(num_etape-.5);
        cellule=$('<'+balise_cellule+'>');
        if (num_etape != parseInt(num_etape)) {// Nouvelle etape
            tr.children(balise_cellule+':nth-child('+($('[name="entete_etape_'+num_etape_precedente+'"]').first().prevAll().length+1)+')')
              	.after(cellule);
        }
        else
            tr.append(cellule);
    }
    cellule.data('etape',num_etape);
    switch(tr.prevAll().length) {
        case 0: case nb_lignes-1:// Ligne des etapes
            var nom_fonction=etape.Nom_fonction;
            
            if (privilege !='Affichage')
            	cellule.html(image_supprimer.clone(true));
              
            cellule.addClass('lien_etape'+(est_nouvelle ? ' nouvelle':''))
                   .attr({'name':'entete_etape_'+num_etape})
                   .data('etape',num_etape)
                   .append($('<span>').addClass('numero_etape')
                                      .attr({'title':'Cliquez pour developper l\'etape '+num_etape})
                                      .html(num_etape == -1 
                                         ? 'Dimensions'
                                         : (est_nouvelle ? 'Nouvelle &eacute;tape' : 'Etape '+num_etape)))
                   .append($('<br>'))
                   .append($('<img>',{'height':18,'src':base_url+'images/fonctions/'+nom_fonction+'.png',
                                      'title':nom_fonction,'alt':nom_fonction}).addClass('logo_option'));
              
            if (privilege !='Affichage')
                cellule.append(image_ajouter.clone(true));
                
        break;
        case 1: case nb_lignes-2 :// Ligne des options, vide
            cellule.addClass('etape_active')
                   .append($('<a>',{'href':'javascript:void(0)'}));
        break;
        default:
            if (est_dans_intervalle(tr.data('numero'), etape.Numero_debut+'~'+etape.Numero_fin))
                cellule.addClass('num_checked');
        break;
    }
}

var numero_a_cloner=null;

function cloner_numero (ev) {
    var numero = $(this).data('numero');
    if (numero_a_cloner == null) {
        numero_a_cloner=numero;
        jqueryui_alert('Vous allez cloner le numero '+numero_a_cloner+'\n'
             +'S&eacute;lectionnez maintenant le num&eacute;ro vers lequel cloner ses informations');
    }
    else {
        $('#chargement').html('Clonage en cours...');
        var nouveau_numero=numero;
        $.ajax({
            url: urls['etendre']+['index',pays,magazine,numero_a_cloner,nouveau_numero].join('/'),
            type: 'post',
            success:function(data) {
                if (typeof(data.erreur) !='undefined')
                    jqueryui_alert(data);
                else
                    charger_liste_numeros(magazine);
			},
            failure:function(data) {
                numero_a_cloner=null;
                jqueryui_alert('Erreur');
            }
        });
    }
}

var nom_nouvelle_fonction=null;

function fermer_etapes() {
    $.each($('.ligne_noms_options').first().find('.option_etape'),function (i,colonne_entete) {
        var num_colonne=$(colonne_entete).prevAll().length+1;
        $.each($('.ligne_dispo,.ligne_noms_options'),function(index,ligne) {
            $(ligne).children('td:nth-child('+num_colonne+'),th:nth-child('+num_colonne+')').remove();
        });
    });
    $('.lien_etape').attr({'colspan':1});
    $('.etape_ouverte').removeClass('etape_ouverte');
    $('.etape_active').html('');
    colonne_ouverte=false;
}

var descriptions_options=new Array();

function charger_etape(num_etape, numeros_sel, nom_option_sel, recharger) {
    recharger = typeof(recharger) != 'undefined';
    if ($('.ligne_noms_options').first().select('.option_etape').length > 0) {
        var est_etape_ouverte= num_etape == $('.etape_ouverte').first().data('etape');
        if (!recharger) {
            fermer_etapes();
            if (est_etape_ouverte)
                return;
        }
    }
    
    var element=$('[name="entete_etape_'+num_etape+'"]:not(.header_fixe_col)').first();

    var num_colonne=element.prevAll().length+1;
    if (num_etape == -1)
        $('#chargement').html('Chargement des param&egrave;tres des dimensions de tranche...');
    else
        $('#chargement').html('Chargement des param&egrave;tres de l\'&eacute;tape '+num_etape+'...');
    
    $.ajax({
        url: urls['parametrageg']+['index',pays,magazine,num_etape,
                                   nom_nouvelle_fonction==null?'null':nom_nouvelle_fonction,
                                   typeof(nom_option_sel) == 'undefined' ? 'null':nom_option_sel
                                  ].join('/'),
        type: 'post',
        dataType:'json',
        success:function(data) {
            $('[name="entete_etape_'+num_etape+'"]').first().addClass('etape_ouverte');
            colonne_ouverte=true;
            etape_en_cours=num_etape;
            var nb_options=Object.keys(data).length;
            var texte='';
            
            if (recharger) {
                $.each($('td.selected'),function(i,td_sel) {
                    var numero=$(td_sel).parent('tr').data('numero');
                    for (var intervalle in data[nom_option_sel]) {
                        if (est_dans_intervalle(numero, intervalle)) {
                            texte_valeur=data[nom_option_sel][intervalle];
                            var td_valeur_option=formater_valeur($(td_sel),nom_option_sel,texte_valeur).data('valeur_reelle',texte_valeur);
                            
                            td_valeur_option.effect('pulsate',{times: 3},500);
                        }
                    }
                });
            }
            else {
                $('.ligne_etapes th').attr({'colspan':1});
                $('.ligne_etapes th:nth-child('+num_colonne+')').attr({'colspan':nb_options+1});

                var i=0;
                var contenu;
                types_options=new Array();
                valeurs_defaut_options=new Array();
                for (var option_nom in data) {
                    types_options[option_nom]=data[option_nom]['type'];
                    
                    $.each($('.ligne_noms_options'),function(j,ligne) {
                        var nouvelle_cellule=$('<th>')
                                                .addClass('etape_'+num_etape+'__option')
                                                .addClass('option_etape')
                                                .data('etape',num_etape);
                        contenu=$('<span>').append(option_nom);
                        var desc='';
                        if (data[option_nom]['description'] != '') {
                            desc = $('<span>').addClass('desc cache').html(data[option_nom]['description']);
                            descriptions_options[option_nom]=data[option_nom]['description'];
                        }
                        
                        nouvelle_cellule.append(desc)
				                        .append(contenu)
                                        .data('nom_option',option_nom)
                        				.insertBefore($(ligne).children('th:nth-child('+(num_colonne+i)+')'));
                    });
                    i++;
                }
    
                i=0;
                var erreur_donnee_inexistante_affichee=false;
                for (var option_nom in data) {
                    $.each($('.ligne_dispo'),function(index,ligne) {
                        nouvelle_cellule=$('<td>');
                        var numero=$(ligne).data('numero');
                        var etape_utilisee=$(ligne).children('td:nth-child('+($('[name="entete_etape_'+num_etape+'"]').first().prevAll().length +1 +i)+')').hasClass('num_checked');
                        if (etape_utilisee) {
                            if (typeof(data[option_nom])=='string')
                                data[option_nom]=new Array(data[option_nom]);
    
                            texte=null;
                            for (var intervalle in data[option_nom]) {
                                if (intervalle != 'type' && intervalle != 'valeur_defaut' && intervalle !='description') {
                                    if (intervalle == "" && typeof(data[option_nom][intervalle]) !='undefined')
                                        texte=data[option_nom]['valeur_defaut'];
                                    else if (est_dans_intervalle(numero, intervalle))
                                        texte=data[option_nom][intervalle];
                                }
                            }
                            if (typeof(texte) == 'undefined' && etape_en_cours != num_etape) {
                            	if (!erreur_donnee_inexistante_affichee) {
	                            	jqueryui_alert('Erreur critique : Aucune donn&eacute;e dans la base pour l\'&eacute;tape '+num_etape+', l\'option '+option_nom+' et le num&eacute;ro '+numero+'.\n'
	                            		 +'Merci de reporter cette erreur aupr&egrave;s du webmaster');
                            	}
                            	erreur_donnee_inexistante_affichee=true;
                            	texte='ERREUR';
                            }
                            nouvelle_cellule=formater_valeur(nouvelle_cellule,option_nom,texte)
                                                .data('valeur_reelle',texte || null);
                        }
                        else
                            nouvelle_cellule.html('').addClass('non_concerne');
                        nouvelle_cellule.insertBefore($(ligne).children('td:nth-child('+(num_colonne+i)+')'));
                    });
                    i++;
                }
            }
            
            $.each($('.etape_active'),function(index,etape_active) {
            	$(etape_active).html('Active');
            });
            
            $('#chargement').html('');
            reload_observers_cells();
            
            assistant_cellules_sel();
        }
    });
}

function restriction_plage() {
    if (confirm('Le nombre d\'informations sur les tranches de ce magazine semble tres important.\n'
               +'Pour des raisons de fluidite, il est conseille de restreindre la plage de numeros a afficher.\n'
               +'Voulez vous indiquer une plage de numeros ?')) {
        jqueryui_alert('Utilisez les listes d&eacute;roulantes en haut de la page pour indiquer le premier et le dernier num&eacute;ro de la plage, puis cliquez sur le filtre pour valider');
        $('#chargement').html('');
        return true;
    }
    return false;
}

function recharger_selects_filtres() {
    $.each($('#filtre_debut').children('option'),function(i,option) {
        if ($(option).val() == plage[0])
            $('#filtre_debut').prop('selectedIndex', $(option).index());
    });

    $.each($('#filtre_fin').children('option'),function(i,option) {
        if ($(option).val() == plage[1])
            $('#filtre_fin').prop('selectedIndex', $(option).index());
    });
}

function charger_helper(nom_helper, nom_div, nom_fonction) {
    if (nom_fonction != null)
		$('#liste_possibilites_fonctions').prop('selectedIndex', $('#liste_possibilites_fonctions [title="'+nom_fonction+'"]').index());
    
    if (!$(nom_div))
        $('#helpers').append($('<div>',{'id':nom_div}));
        
    $.ajax({
        url: base_url+'index.php/helper/index/'+nom_helper+'.html',
        type: 'post',
        data: 'nom_helper='+nom_helper,
        failure:function(data) {
            jqueryui_alert('Page de helper introuvable : '+nom_helper+'.html');
        },
        success:function(data) {
            var texte=data;
            var suivant_existe=texte.indexOf('...') != -1;
            var nom_fonction_fin=texte.match(/!([^!]+)!/g);
            var est_dernier=nom_fonction_fin != null;
            texte=texte.replace(/\\.\\.\\./g,'')
                       .replace(/!([^!]+)!/g,'');
            var numero_helper=nom_helper.substring(nom_helper.length-1,nom_helper.length);
            if (numero_helper>1) {
                var lien_precedent=$('<a>');//.html('&lt;&lt; Pr&eacute;c&eacute;dent');
                $(nom_div).html('')
                          .append($('<br>'))
                          .append(lien_precedent);
                lien_precedent.click(function() {
                    var nom_helper_suivant= nom_helper.substring(0,nom_helper.length-1)+(parseInt(numero_helper)-1);
                    charger_helper(nom_helper_suivant,nom_div,false,nom_fonction);
                });
            }
            else
                $(nom_div).html('');
            
            $(nom_div).append(texte)
                      .data('numero_helper',numero_helper);
            if (suivant_existe) {
                var lien_suivant=$('<a>').html('Suivant &gt;&gt;');
                $(nom_div).append(lien_suivant);
                lien_suivant.click(function() {
                    var nom_helper_suivant= nom_helper.substring(0,nom_helper.length-1)+(parseInt(numero_helper)+1);
                    charger_helper(nom_helper_suivant,nom_div,nom_fonction);
                });
            }
            if (est_dernier) {
                var nouvelle_etape=new Object();
                nouvelle_etape['Nom_fonction']=nom_fonction_fin[0].replace(/!/g,'');
                nouvelle_etape['Numero_debut']='';
                nouvelle_etape['Numero_fin']='';
                nouvelle_etape['Ordre']=parseInt(num_etape_avant_nouvelle)+.5;
                nom_nouvelle_fonction=nouvelle_etape['Nom_fonction'];
                $.each($('#table_numeros').find('tr'),function(i,tr) {
                    charger_etape_ligne(nouvelle_etape,$(tr), true);
                });
                reload_observers_etapes();
                reload_observers_cells();
            }
        }
    });
}

function get_onglet_courant() {
	return $('#contenu_'+onglet_sel.toLowerCase());
}

function remplacer_caracteres_whatthefont() {
    var nom_police=$('#url_police').value.replace(/(?:http:\/\/)?(?:new\.)?myfonts.com\/fonts\/(.*)\//g,'$1')
                                         .replace(/\//g,'.');
    $('#nom_police').html('Notez le nom de la police correspondant &agrave; votre texte :')
                    .append($('<br>'))
                    .append($('<b>').html(nom_police));
}

function jqueryui_alert(texte, titre) {
	if (typeof(titre) == 'undefined')
		titre='DucksManager EdgeCreator';
	var boite=$('<div>',{'title':titre});
	if (typeof(texte)=='string')
		boite.append($('<p>').html(texte));
	else
		boite.append(texte);
	$('#body').append(boite);
	boite.dialog({
		width: 350,
		modal: true,
		buttons: {
			OK: function() {
				$( this ).dialog( "close" );
			}
		}
	});
}

function afficher_dialogue_accueil() {
	$( "#wizard-accueil" ).dialog({
		width: 850,
		modal: false,
		resizable: false,
		buttons: {
			"Suivant":function() {
				$( this ).dialog( "close" );
				$( "#wizard-accueil2" ).dialog({
					width: 500,
					modal: false,
					resizable: false,
					buttons: {
						"Suivant":function() {
							$( this ).dialog( "close" );
							$( "#wizard-accueil3" ).dialog({
								width: 500,
								modal: false,
								resizable: false,
								buttons: {
									"Suivant":function() {
										$( this ).dialog( "close" );
										jquery_connexion();
									}
								}
							});
						}
					}
				});
			}
		}
	});
}

function jquery_connexion() {
	$( "#login-form" ).dialog({
		width: 500,
		modal: false,
		buttons: {
			"Connexion":function() {
				$.ajax({
			        url: base_url+'index.php/edgecreatorg/login',
			        type: 'post',
			        data: 'user='+$('#username').val()+'&pass='+$('#password').val()+"&mode_expert="+$('#mode_expert').prop('checked'),
			        success:function(data) {
			            if (data.indexOf("Erreur") == 0)
			            	$( "#login-form" ).find('.erreurs').html(data);
			            else {
			            	location.reload();
			            }
			        }
				});
			}
		}
	});
}

function logout() {
	$.ajax({
        url: base_url+'index.php/edgecreatorg/logout',
        type: 'post',
        success:function(data) {
        	location.reload();
        }
	});
}

function URLEncode (clearString) {
  var output = '';
  var x = 0;
  clearString = clearString.toString();
  var regex = /(^[a-zA-Z0-9_.]*)/;
  while (x < clearString.length) {
    var match = regex.exec(clearString.substr(x));
    if (match != null && match.length > 1 && match[1] != '') {
    	output += match[1];
      x += match[1].length;
    } else {
      if (clearString[x] == ' ')
        output += '+';
      else {
        var charCode = clearString.charCodeAt(x);
        var hexVal = charCode.toString(16);
        output += '%' + ( hexVal.length < 2 ? '0' : '' ) + hexVal.toUpperCase();
      }
      x++;
    }
  }
  return output;
}