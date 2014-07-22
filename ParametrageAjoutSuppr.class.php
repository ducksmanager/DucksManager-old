<?php
class ParametreAjoutSuppr {
    var $nomParametrage;
    var $classeCssParametre;
    var $valeurParametre;
    var $libelleParametre;

    static $nomParametreConserver = 'conserver';

    function __construct($nomParametrage, $classeCssParametre, $valeurParametre, $libelleParametre)
    {
        $this->nomParametrage = $nomParametrage;
        $this->valeurParametre = $valeurParametre;
        $this->libelleParametre = $libelleParametre;
        $this->classeCssParametre = $classeCssParametre;
    }

    function __toString() {
        $classes = array($this->nomParametrage, $this->classeCssParametre);
        return '
            <li '.($this->valeurParametre === self::$nomParametreConserver ? 'class="selected"' : '').'>
                <a href="javascript:return false;"
                   name="'.$this->valeurParametre.'"
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

    function toStringListe($liste) {
        $str = '<ul class="liste_parametrage">';
        foreach($liste as $item) {
            $str.= $item;
        }
        $str.= '</ul>';

        return $str;
    }

    function __toString() {
        $liste = array_merge(
            self::$liste[$this->nom],
            array(
                new ParametreAjoutSuppr($this->nom, ParametreAjoutSuppr::$nomParametreConserver, ParametreAjoutSuppr::$nomParametreConserver, NE_PAS_CHANGER),
                new ParametreAjoutSuppr($this->nom, 'choisir', 'choisir', CHOISISSEZ)
            )
        );
        $str = '<div class="footer_section">'
                    .'<h2 class="libelle">'
                        .'<label for="'.$this->nom.'">'.$this->libelle.'</label>'
                    .'</h2>'
                    .'<div class="conteneur_liste" id="parametrage_'.$this->nom.'">'
                        .$this->toStringListe($liste)
                    .'</div>'
                .'</div>';
        return $str;
    }

    function add_to_list(ParametreAjoutSuppr $parametre) {
        self::$liste[$this->nom][$parametre->valeurParametre] = $parametre;
    }

    function getListe() {
        return self::$liste[$this->nom];
    }
}

/** Etats */

class Etat extends ParametreAjoutSuppr {

    var $couleur;

    function __construct($nom, $classeCss, $libelle, $couleur)
    {
        parent::__construct('Etat', $classeCss, $nom, $libelle);
        $this->couleur = $couleur;
    }
}

class Etats extends ParametrageAjoutSuppr {

    /** @var Etats */
    static $instance;

    function __construct() {
        parent::__construct('Etat', ETAT);

        $this->add_to_list(new Etat('mauvais', 'mauvais', MAUVAIS, '#FF0000'));
        $this->add_to_list(new Etat('moyen', 'moyen', MOYEN, '#FF8000'));
        $this->add_to_list(new Etat('bon', 'bon', BON, '#2CA77B'));
        $this->add_to_list(new Etat('indefini', 'possede', INDEFINI, '#808080'));
        $this->add_to_list(new Etat('non_possede', 'non_possede', NON_POSSEDE, '#000000'));
    }
}

Etats::$instance = new Etats();

/** Etats de vente */

class EtatAVendre extends ParametreAjoutSuppr {

    function __construct($nom, $classeCss, $libelle)
    {
        parent::__construct('AV', $classeCss, $nom, $libelle);
    }
}

class EtatsAVendre extends ParametrageAjoutSuppr {

    /** @var EtatsAVendre */
    static $instance;

    function __construct() {
        parent::__construct('AV', VENTE);

        $this->add_to_list(new EtatAVendre('1', 'a_vendre', VENTE_MARQUER_A_VENDRE));
        $this->add_to_list(new EtatAVendre('0', 'pas_a_vendre', VENTE_MARQUER_PAS_A_VENDRE));
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

    function __construct($nom, $classeCss, $libelle)
    {
        parent::__construct('ID_Acquisition', $classeCss, $nom, $libelle);
    }
}

class EtatsAchats extends ParametrageAjoutSuppr {

    /** @var EtatsAchats */
    static $instance;

    /** @var Achat[]  */
    var $dates_achat = array();

    static function toStringAchat($date_achat) {
        if (is_null($date_achat)) {
            $idAcquisition = '';
            $libelle = '';
            $dateAcquisition = '';
        }
        else {
            $idAcquisition = $date_achat->id_acquisition;
            $libelle = $date_achat->libelle;
            $dateAcquisition = $date_achat->date;
        }
        return '<li'.(is_null($date_achat) ? ' class="template"' :'').'>
                <div title="'.SUPPRIMER_DATE_ACHAT.'" class="supprimer_date_achat"></div>
                <a class="achat"
                   href="javascript:return false;"
                   name="'.$idAcquisition.'">
                    '.ACHAT.' "'.$libelle.'"<br />
                    '.$dateAcquisition.'
                </a>
            </li>';
    }

    function toStringListe($liste) {
        $str = parent::toStringListe($liste);
        $liste_achats =
            '<ul id="liste_achats">
                <li><a id="creer_date_achat" href="javascript:return false;" name="achat" class="creer_achat enabled">'.CREER_DATE_ACHAT.'</a></li>
                <li class="nouvel_achat template">
                    <input id="nouvelle_description" type="text" size="30" maxlength="30" value="'.DESCRIPTION.'"><br />
                    <div id="calendarview_input">
                        <label for="nouvelle_date"></label>
                        <input id="nouvelle_date" type="text" size="30" maxlength="10" />
                    </div>
                    <input id="nouvelle_date_ok" type="submit" value="'.OK.'" />&nbsp;
                    <input id="nouvelle_date_annuler" type="submit" value="'.ANNULER.'" />
                </li>
                <li class="separator"></li>';
        $liste_achats.=self::toStringAchat(null);
        foreach($this->dates_achat as $date_achat) {
            $liste_achats.= self::toStringAchat($date_achat);
        }

        $liste_achats.='</ul>';
        return $str.$liste_achats;
    }

    function __construct() {
        parent::__construct('ID_Acquisition', DATE_ACHAT);

        $this->add_to_list(new EtatAchat('-2', 'date', ACHAT_ASSOCIER_DATE_ACHAT));
        $this->add_to_list(new EtatAchat('-1', 'pas_date', ACHAT_DESASSOCIER_DATE_ACHAT));
    }

    function ajouter_date_achat($achat) {
        $this->dates_achat[] = new Achat($achat['ID_Acquisition'], $achat['Date'], $achat['Description']);
    }
}

EtatsAchats::$instance = new EtatsAchats();