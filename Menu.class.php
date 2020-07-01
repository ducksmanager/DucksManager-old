<?php

class Item {
    var $nom;
    var $est_prive;
    var $texte;
    var $icone;
    var $beta=false;
    var $nouveau=false;
    static $beta_user=false;
    static $action="";

    function __construct($nom, $est_prive, $texte, $icone = null, $beta = false, $nouveau = false) {
        $this->nom = $nom;
        $this->est_prive = $est_prive;
        $this->texte = $texte;
        $this->icone = $icone;
        $this->beta = $beta;
        $this->nouveau = $nouveau;
    }

    function afficher() {
        if ($this->est_affiche()) {?>
            <li class="non-empty <?= $this->icone ? '' : 'no-icon' ?> <?= !isset($_GET['user']) && ($_GET['action'] ?? '') === $this->nom ? 'active' : '' ?>">
                <a href="?action=<?= $this->nom ?>">
                    <i class="<?= $this->icone ?>"></i>
                    <?= $this->texte ?><?php
                    if ($this->nouveau) {
                        ?><span class="nouveau"><?= NOUVEAU ?></span><?php
                    }?>
                </a>
            </li><?php
        }
    }

    function est_affiche() {
        return ($this->est_prive==='no'
             || (in_array($this->est_prive, ['always', 'always_except_user_provided']) && isset($_SESSION['user']) &&!(self::$action==='logout'))
             || ($this->est_prive==='never'  &&!(isset($_SESSION['user']) &&!(self::$action==='logout'))));
    }
}

class LigneVide extends Item{
    function __construct() {

    }

    function afficher() {
        ?><li class="empty"></li><?php
    }

}

class Menu extends Item{
    /** @var Item[] $items */
    var $items;

    /**
     * Menu constructor.
     * @param string $nom
     * @param bool $est_prive
     * @param string $texte
     * @param string $icone
     * @param Item[] $items
     */
    function __construct($nom, $est_prive, $texte, $icone, $items) {
        parent::__construct($nom, $est_prive, $texte, $icone);
        $this->items = $items;
    }

    public function afficher() {
        $isActive = !isset($_GET['user']) && in_array($_GET['action'] ?? '', array_map(function (Item $i) {
            return $i->nom;
        }, $this->items), true);
        ?>
        <li data-toggle="collapse" data-target="#<?=$this->nom?>" class="collapsed <?=$isActive ? 'active' : ''?>">
            <a href="#"><i class="<?=$this->icone?>"></i> <?=$this->texte?> <span class="arrow"></span></a>
        </li>
        <ul class="sub-menu collapse in" id="<?=$this->nom?>"><?php
        foreach($this->items as $item) {
            $item->afficher();
        }
        ?></ul><?php
    }

    /**
     * @param Menu[] $menus
     */
    static function afficherMenus($menus) {?>
        <ul id="menu-content" class="menu-content collapse"><?php
        foreach($menus as $menu) {
            $menu->afficher();
        }?>
        </ul><?php
    }
}

$menus= [
    new Menu('collection', 'no', COLLECTION, 'glyphicon glyphicon-home', [
            new Item('new', 'never', NOUVELLE_COLLECTION, 'glyphicon glyphicon-certificate'),
            new Item('open', 'never', OUVRIR_COLLECTION, 'glyphicon glyphicon-folder-open'),
            new Item('bibliotheque', 'always_except_user_provided', BIBLIOTHEQUE_COURT, 'glyphicon glyphicon-book'),
            new Item('gerer', 'always', GERER_COLLECTION, 'glyphicon glyphicon-list-alt'),
            new Item('stats', 'always', STATISTIQUES_COLLECTION, 'glyphicon glyphicon-tasks'),
            new Item('agrandir', 'always', AGRANDIR_COLLECTION, 'glyphicon glyphicon-fire'),
            new Item('importer_inducks', 'always', COLLECTION_INDUCKS, 'glyphicon custom-inducks'),
            new Item('print', 'always', IMPRIMER_COLLECTION, 'glyphicon glyphicon-print'),
            new Item('logout', 'always', DECONNEXION, 'glyphicon glyphicon-log-out')
        ]
    ),
    new LigneVide(),
    new Item('bouquineries', 'no', RECHERCHER_BOUQUINERIES),
    new Item('inducks', 'never', COLLECTION_INDUCKS_POSSEDEE),
    new Item('demo', 'never', DEMO_MENU),
];
?>
