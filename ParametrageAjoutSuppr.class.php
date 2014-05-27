<?php
class ParametreAjoutSuppr {
    var $nomParametrage;
    var $nomParametre;
    var $libelleParametre;

    function __construct($nomParametrage, $nomParametre, $libelleParametre)
    {
        $this->nomParametrage = $nomParametrage;
        $this->nomParametre = $nomParametre;
        $this->libelleParametre = $libelleParametre;
    }

    function __toString() {
        $classes = array($this->nomParametrage, $this->nomParametre);
        return '
            <li '.($this->nomParametre === 'conserver' ? 'class="selected"' : '').'>
                <a href="javascript:return false;"
                   name="'.$this->nomParametrage.'_'.$this->nomParametre.'"
                   class="'.implode(' ', $classes).'">'.$this->libelleParametre.'
                </a>
            </li>';
    }
}

class ParametrageAjoutSuppr {

    var $nom;
    var $libelle;

    static $liste = array();

    function __construct($nom, $libelle)
    {
        $this->nom = $nom;
        $this->libelle = $libelle;
        self::$liste[$nom] = array();
    }

    function __toString() {
        $liste = array(new ParametreAjoutSuppr($this->nom, 'conserver', NE_PAS_CHANGER));
        $liste = array_merge(self::$liste[$this->nom], $liste);
        $str = '<div class="footer_section">'
                    .'<h2 class="libelle">'
                        .'<label for="'.$this->nom.'">'.$this->libelle.'</label>'
                    .'</h2>'
                    .'<div class="liste">'
                        .'<ul>';
                        foreach($liste as $item) {
                            $str.= $item;
                        }
        $str.=          '</ul>'
                    .'</div>'
                .'</div>';
        return $str;
    }

    function add_to_list(ParametreAjoutSuppr $parametre) {
        self::$liste[$this->nom][$parametre->nomParametre] = $parametre;
    }

    function getListe() {
        return self::$liste[$this->nom];
    }
}

/** Etats */

class Etat extends ParametreAjoutSuppr {

    var $couleur;

    function __construct($nom, $libelle, $couleur)
    {
        parent::__construct('etat', $nom, $libelle);
        $this->couleur = $couleur;
    }
}

class Etats extends ParametrageAjoutSuppr {

    /** @var Etats */
    static $instance;

    function __construct() {
        parent::__construct('etat', ETAT);

        $this->add_to_list(new Etat('mauvais', MAUVAIS,'#FF0000'));
        $this->add_to_list(new Etat('moyen', MOYEN,'#FF8000'));
        $this->add_to_list(new Etat('bon', BON,'#2CA77B'));
        $this->add_to_list(new Etat('indefini', INDEFINI,'#808080'));
        $this->add_to_list(new Etat('non_possede', NON_POSSEDE,'#000000'));
    }
}

Etats::$instance = new Etats();

/** Etats de vente */

class EtatAVendre extends ParametreAjoutSuppr {
    function __construct($nom, $libelle)
    {
        parent::__construct('vente', $nom, $libelle);
    }
}

class EtatsAVendre extends ParametrageAjoutSuppr {

    /** @var EtatsAVendre */
    static $instance;

    function __construct() {
        parent::__construct('vente', VENTE);

        $this->add_to_list(new EtatAVendre('a_vendre', VENTE_MARQUER_A_VENDRE));
        $this->add_to_list(new EtatAVendre('pas_a_vendre', VENTE_MARQUER_PAS_A_VENDRE));
    }
}

EtatsAVendre::$instance = new EtatsAVendre();

/** Etats d'achat */

class Achat {
    var $id_acquisition;
    var $date;
    var $libelle;

    function __construct($id_acquisition, $date, $libelle)
    {
        $this->id_acquisition = $id_acquisition;
        $this->date = $date;
        $this->libelle = $libelle;
    }
}

class EtatAchat extends ParametreAjoutSuppr {

    function __construct($nom, $libelle)
    {
        parent::__construct('achat', $nom, $libelle);
    }
}

class EtatsAchats extends ParametrageAjoutSuppr {

    /** @var EtatsAchats */
    static $instance;

    var $dates_achat = array();

    function __construct() {
        parent::__construct('achat', DATE_ACHAT);

        $this->add_to_list(new EtatAchat('date', ACHAT_ASSOCIER_DATE_ACHAT));
        $this->add_to_list(new EtatAchat('pas_date', ACHAT_DESASSOCIER_DATE_ACHAT));
    }

    function ajouter_date_achat($achat) {
        $this->dates_achat[] = new Achat($achat['ID_Acquisition'], $achat['Date'], $achat['Description']);
    }
}

EtatsAchats::$instance = new EtatsAchats();