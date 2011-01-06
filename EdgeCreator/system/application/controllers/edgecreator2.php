<?php
class EdgeCreator2 extends Controller {
    static $pays;
    static $magazine;
    
    function index($pays=null,$magazine=null)
    {
        self::$pays=$pays;
        self::$magazine=$magazine;
        $this->load->library('session');
        $this->load->database();
        $this->db->query('SET NAMES UTF8');
        $this->load->helper('form');
        $this->load->helper('url');

        if (!is_null($pays) && !is_null($magazine)) {
            $this->session->set_userdata(array('pays'=>self::$pays));
            $this->session->set_userdata(array('magazine'=>self::$magazine));
        }
        if ($this->session->userdata('pays')!==false && $this->session->userdata('magazine')!==false) {
            self::$pays=$this->session->userdata('pays');
            self::$magazine=$this->session->userdata('magazine');
        }

        $this->load->model('Modele_tranche');
        $this->Modele_tranche->setPays(self::$pays);
        $this->Modele_tranche->setMagazine(self::$magazine);
        $num_ordres=$this->Modele_tranche->get_ordres(self::$pays,self::$magazine);
        //print_r($ordres);
        $ordres=array();
        foreach($num_ordres as $num_ordre) {
            $ordres[$num_ordre]=$this->Modele_tranche->get_fonctions(self::$pays,self::$magazine,$num_ordre);
            foreach($ordres[$num_ordre] as &$fonction) {
                $fonction->options=$this->Modele_tranche->get_options(self::$pays,self::$magazine,$num_ordre,$fonction->Nom_fonction);
            }
        }

        $numeros_dispos=$this->Modele_tranche->get_numeros_disponibles(self::$pays,self::$magazine);
        $liste_fonctions=array('Dimensions','Remplir','Agrafer','TexteTTF','TexteMyFonts','Image','Polygone','Degrade','Rectangle','Arc_cercle');
        sort($liste_fonctions);
        $data = array(
                'title' => 'EdgeCreator',
                'pays' => self::$pays,
                'magazine'=>self::$magazine,
                'num_ordres'=>$num_ordres,
                'ordres'=>$ordres,
                'texte'=>'',
                'liste_fonctions'=>$liste_fonctions,
                'numeros_dispos'=>$numeros_dispos,
        );
        $this->session->set_userdata(array('zoom'=>1));

        if (false!==$this->input->post('zoom')) {
            $this->session->set_userdata(array('zoom'=>$this->input->post('zoom')));
        }
        $this->session->set_userdata(array('preview'=>'Aucun'));

        if (false!==$this->input->post('preview_issue')) {
            $this->session->set_userdata(array('preview_issue'=>$this->input->post('preview_issue')));
        }
        $data['numeros_preview']=form_dropdown('preview_issue', $numeros_dispos, $this->session->userdata('preview_issue'),'id="preview_issue"');
        
        // Pour les dimensions
        $data['numeros_visualisables1_input']=form_input('viewable_issue1', null);
        $data['numeros_visualisables2_input']=form_input('viewable_issue2', null);
        // Pour les autres
        $data['numeros_visualisables1_select']=form_dropdown('viewable_issue1', $numeros_dispos, null);
        $data['numeros_visualisables2_select']=form_dropdown('viewable_issue2', $numeros_dispos, null);
        
        $data['preview_form']=form_dropdown('zoom', array('1'=>1,'2'=>2,'4'=>4,'8'=>8), $this->session->userdata('zoom'),'id="zoom"')
                             .'<br /><br />'
                             .'Num&eacute;ro de pr&eacute;visualisation : '
                             .$data['numeros_preview']
                             .'<br />'
                             .'<button onClick="reload_preview()">OK</button>';
        /*
        $data['texte'].=form_open('edgecreator');
        $input = array(
          'name'        => 'dimension_w',
          'id'          => 'dimension_w',
          'value'       => $this->session->userdata('dimension_w'),
          'maxlength'   => '2',
          'size'        => '2'
        );
        $data['texte'].=form_input($input).'mm x ';
        $input = array(
          'name'        => 'dimension_h',
          'id'          => 'dimension_h',
          'value'       => $this->session->userdata('dimension_h'),
          'maxlength'   => '3',
          'size'        => '3'
        );
        $data['texte'].=form_input($input).'mm';
        $data['texte'].=form_submit('', 'OK');*/

        $this->load->view('headerview',$data);
        $this->load->view('preview',$data);
        $this->load->view('edgecreator2view',$data);
        $this->load->view('footerview',$data);
    }

    function _private() {

    }
}
?>