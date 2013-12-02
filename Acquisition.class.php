<?php
	class Acquisition {
		var $date;
		var $numeros=array();
		var $couleur;
		function __construct($date,$numeros,$couleur) {
			$this->date=$date;
			$this->numeros=$numeros;
			$this->couleur=$couleur;
		}
		
		function afficher($magazines_groupes) {
			if ($magazines_groupes) {
				$magazines=array();
				foreach($this->numeros as $numero) {
					if (!in_array($numero['Magazine'],$magazines))
						array_push($magazines,$numero['Magazine']);
				}
				echo '<table border="1"><tr>';
			}
		}
	}
?>