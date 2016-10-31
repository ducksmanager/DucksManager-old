<?php
include_once 'ServeurCoa.class.php';
include_once 'Util.class.php';

class ServeurDb {
	static $db_servers_file='ducksmanager_db.ini';

	/** @var $db_servers ProfilDB[] */
	static $db_servers = [];

	/** @var $db_servers ProfilDB */
	static $current_db_server;

	static $nom_db_DM = 'db301759616';

	/**
	 * @return ProfilDB
	 */
	static function getPiwikServer() {
		return self::getProfil('serveur_virtuel');
	}

	static function initDBServers() {
		self::$db_servers = [];
		$configured_db_servers = parse_ini_file(self::$db_servers_file, true);
		foreach($configured_db_servers as $name=>$configured_db_server) {
			self::$db_servers[$name] = Util::cast(ProfilDB::class, json_decode(json_encode($configured_db_server)));
		}
	}

	static function connect($db=null) {
		if (count(self::$db_servers) === 0) {
			ServeurDb::initDBServers();
		}
		if (!isLocalHost()) {
			if (self::isServeurVirtuel()) {
				$current_db_server='serveur_virtuel';
			}
			else {
				$current_db_server='ducksmanager.net';
			}
		}
		else {
			$current_db_server='localhost';
		}
		self::$current_db_server=$current_db_server;
		return self::$db_servers[$current_db_server]->connexion($db);
	}

	static function getProfil($nom) {
		return self::$db_servers[$nom];
	}

	static function getIpServeurVirtuel() {
		return ServeurDb::getProfil('serveur_virtuel')->ip;
	}

	static function getDomainServeurVirtuel() {
		return ServeurDb::getProfil('serveur_virtuel')->domain;
	}

	static function isServeurVirtuel() {
		return in_array($_SERVER['HTTP_HOST'], [ServeurDb::getDomainServeurVirtuel(), ServeurDb::getIpServeurVirtuel()]);
	}

	static function getUrlServeurVirtuel() {
		return 'http://'.self::getIpServeurVirtuel();
	}

	static function getProfilCourant() {
		return self::getProfil(self::$current_db_server);
	}

	static function verifPassword($password) {
		return sha1(self::getProfilCourant()->password) == $password;
	}

	public static function getRemoteUrl($page, $server = null)
	{
		if (is_null($server)) {
			$server = 'dedibox';
		}
		$serveurCoaPrincipal = ServeurCoa::getCoaServer($server);
		return implode('/', [
			$serveurCoaPrincipal->getUrl(),
			$serveurCoaPrincipal->web_root,
			$page
		]);
	}
}

class ProfilDB {
	var $ip;
	var $domain;
	var $server;
	var $user;
	var $password;

	function __construct() { }

	function connexion($db) {
		if (!$this->server) return false;

        Database::$handle = mysqli_connect($this->server, $this->user, $this->password, is_null($db) ? ServeurDb::$nom_db_DM : $db);

        return ! Database::$handle->connect_errno;
	}
}

function isLocalHost() {
	return !(isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'],'localhost')===false);
}

ServeurDb::initDBServers();