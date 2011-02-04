<?php
if (isset($_POST['value'])) {
	require_once('Util.class.php');
	$valeurs=$_POST['value'];
        ?><ul class="contacts"><?php
	foreach(explode(' ',$valeurs) as $valeur) {
            $premiere_lettre=substr($valeur,0,1);
            if ($premiere_lettre>='a' && $premiere_lettre <='z')
                    $premiere_lettre=strtoupper($premiere_lettre);
            if ($premiere_lettre>='A' && $premiere_lettre <='Z')
                    $url='http://coa.inducks.org/legend-creator.php?start='.$premiere_lettre.'&sortby=&filter=';
            else
                    $url='http://coa.inducks.org/legend-creator.php?start=1&sortby=&filter=';
            $regex_auteur='#<a href="creator\.php\?c=([^"]+)">([^<]+)</a>#is';
            $page=Util::get_page($url);
            preg_match_all($regex_auteur,$page,$auteurs);   
            $i=0;  
            foreach($auteurs[0] as $auteur) {
                    $nom_auteur=preg_replace($regex_auteur,'$2',$auteur);
                    $id_auteur=preg_replace($regex_auteur,'$1',$auteur);
                    if (strpos(strtolower($nom_auteur),strtolower($valeur)) !== false) {
                        ?>
                        <li class="contact">
                            <!--<div class="image">
                                <img width="32" alt="<?=$id_auteur?>" src="images/<?=$nom_auteur?>-mini.jpg"/>
                            </div>!-->
                            <div class="nom">
                                <span><?=$nom_auteur?></span>
                                <span style="display:none" name="nom_auteur" title="<?=$id_auteur?>"></span>
                            </div>
                        </li><?php
                    }		
            }
        }
	echo '</ul>';
	die();
}