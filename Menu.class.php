<?php
class Menu {
	var $menu = array(
		'Collection'=>array(
			'nouvelle'=>	   array('nom complet'=>'Nouvelle collection',
									'traitement_immediat'=>true
									 ),
			'ouvrir'=>		   array('nom complet'=>'Ouvrir ma collection', 
									 'traitement_immediat'=>true
									 ),
			'sauvegarder'=>	   array('nom complet'=>'Sauvegarder ma collection', 
									 'traitement_immediat'=>true
									 ),
			'imprimer'=>	   array('nom complet'=>'Imprimer ma collection', 
									 'onclick'=>'window.print();')
		),
		
		'Collection Inducks'=>array(
			'importer'=>	   array('nom complet'=>'Importer ma collection Inducks', 
									 'traitement_immediat'=>true
									 ),
			'exporter'=>	   array('nom complet'=>'Exporter vers ma collection Inducks', 
									 'traitement_immediat'=>true)
		)
	);
	
	function Menu() {
		
	}
	
	function afficher() {
		echo '<table><tr><td>';
		echo '<table><tr valign="top">';
		foreach($this->menu as $titre_menu=>$items) { // Boutons principaux ("Fichier","Affichage",etc.)
			echo '<td align="center">';
			echo '<button class="bouton" style="font-weight:bold;width:226px"
						  id="'.$titre_menu.'"
						  onclick="montrer_cacher(this)">'.$titre_menu.'</button>';
			echo '<span id="'.$titre_menu.'_sous_menu" style="display:none"><br />';
			foreach($items as $titre_item=>$item)
				echo $this->nouveauBouton($titre_item,$item,220); // Mettre en place les items de chaque bouton
		
			echo '</span>';
			echo '</td>';
		}
		echo '</tr></table></td></tr></table>';
	}
	
	function nouveauBouton($titre_item,$item,$largeur_bouton) { // Sous-items des menus "Nouveau", "Ouvrir",etc.
		$bouton= '<button class="bouton" style="width:'.$largeur_bouton.'px"
						  id="'.$titre_item.'"
						  onclick="';
		$onclick=''; // Cette variable contient, comme son nom l'indique, toutes les actions à effectuer lors du clic sur le bouton
		if (isset($item['sous-menu'])) // Cas où le clic déclenche l'affiche d'un sous-menu (rotation, rectangle, cercle)
			$onclick.='if (traitement_possible(\''.$titre_item.'\')) montrer_cacher(this)	;';
		else {
			if (isset($item['traitement_immediat'])) {
				if ($item['traitement_immediat']) //La plupart du temps, l'opération doit s'exécuter tout de suite...
					$onclick.='if (sel_traitement(\''.$titre_item.'\')) ajax();';
				else // ...Mais par exemple pour le dessin d'une ligne ou la gomme, il faut attendre le(s) clic(s) de l'utilisateur
					$onclick.='sel_traitement(\''.$titre_item.'\');';
			}
		}
		
		if (isset($item['onclick']))
			$onclick.=$item['onclick']; // Ici on ajoute les actions "spéciales" du onclick (appeler set_zoom(), afficher le formulaire d'upload,...)
		$bouton.=$onclick.'">'.$item['nom complet'].'</button><br />';
		if (!empty($item['sous-menu'])) { // Si il y a un sous-menu, mettre en place les sous-items en ré-appelant nouveauBouton()
			$bouton.='<span id="'.$titre_item.'_sous_menu" style="display:none">';
			$cpt=0;
			foreach($item['sous-menu'] as $sous_menu) {
				$bouton.= '&nbsp;'
					   .  $this->nouveauBouton($titre_item.'_'.$cpt,$sous_menu,$largeur_bouton-6);
					   // Les sous-boutons ont un id de la forme menuparent_x, avec x un entier défini par $cpt
				$cpt++;
			}
			$bouton.='</span>';
		}
		return $bouton;
	}
}
?>