<?php
class EdgeCreatorg extends CI_Controller {
	static $pays;
	static $magazine;
	
	function logout() {
		$this->load->library('session');
		$this->session->unset_userdata('user');
		$this->session->unset_userdata('pass');
		$this->index();
	}
	
	function index($pays=null,$magazine=null,$etape_ouverture=null,$numero_debut_filtre=null,$numero_fin_filtre=null)
	{
		self::$pays=$pays;
		self::$magazine=$magazine;
		$this->load->helper('url');
		$this->load->database();
		$this->load->library('session');
		

		$privilege=null;
		global $erreur;
		$erreur = '';
		if (isset($_POST['user'])) {
			if (!is_null($privilege = $this->user_connects($_POST['user'],$_POST['pass'])))
				$this->creer_id_session($_POST['user'],md5($_POST['pass']));
		}
		else {
			if ($this->session->userdata('user') !== false && $this->session->userdata('pass') !== false) {
				$privilege = $this->user_connects($this->session->userdata('user'),$this->session->userdata('pass'));
				if ($privilege == null) {
					$this->creer_id_session($this->session->userdata('user'),$this->session->userdata('pass'));
				}
			}
		}
		$data = array(
				'user'=>$this->session->userdata('user'),
				'privilege' => $privilege,
				'erreur' => $erreur,
				'title' => 'EdgeCreator',
				'pays' => self::$pays,
				'magazine'=>self::$magazine,
				'etape_ouverture'=>$etape_ouverture,
				'numero_debut_filtre'=>$numero_debut_filtre,
				'numero_fin_filtre'=>$numero_fin_filtre
		);
		$this->load->view('headergview',$data);
		$this->load->view('edgecreatorgview',$data);
		$this->load->view('footerview',$data);
	}
	

	function user_connects($user,$pass) {
		global $erreur;
		if (!$this->user_exists($user)) {
			$erreur = 'Cet utilisateur n\'existe pas';
			return false;
		}
		else {
			$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\') AND (password LIKE \''.$pass.'\' OR md5(password) LIKE \''.$pass.'\')';
			$resultat=$this->db->query($requete);
			if ($resultat->num_rows==0) {
				$erreur = 'Identifiants invalides !';
				return null;
			}
			else {
				$requete='SELECT privilege FROM edgecreator_droits WHERE username LIKE(\''.$user.'\')';
				$resultat= $this->db->query($requete);
				if ($resultat->row()==null) {
					$erreur='Vous n\'&ecirc;tes pas membre de la team EdgeCreator';
					return null;
				}
				return $resultat->row()->privilege;
			}
		}
	}

	function user_exists($user) {
		$requete='SELECT username FROM users WHERE username LIKE(\''.$user.'\')';
		return ($this->db->query($requete)->num_rows > 0);
	}
	
	
	function creer_id_session($user,$pass) {
		
		$this->session->set_userdata(array('user' => $user, 'pass' => $pass));
	}
	
}
?>