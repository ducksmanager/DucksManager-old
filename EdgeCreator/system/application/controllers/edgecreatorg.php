<?php
class EdgeCreatorg extends Controller {
    static $pays;
    static $magazine;
    
    function index($pays=null,$magazine=null,$etape_ouverture=null)
    {
        self::$pays=$pays;
        self::$magazine=$magazine;
        $this->load->helper('url');

        
        $data = array(
                'title' => 'EdgeCreator',
                'pays' => self::$pays,
                'magazine'=>self::$magazine,
                'etape_ouverture'=>$etape_ouverture
        );
        $this->load->view('headergview',$data);
        //$this->load->view('preview',$data);
        $this->load->view('edgecreatorgview',$data);
        $this->load->view('footerview',$data);
    }

    function _private() {

    }
}
?>