<?php
include_once 'Util.class.php';

class ServeurCoa {
    /** @var $coa_servers ServeurCoa[] */
    static $coa_servers = [];

    /** @var $ducksmanager_server ServeurCoa */
    static $ducksmanager_server;

    static $coa_servers_file='coa.ini';

    var $ip;
    var $machine_id;
    var $restart_token;
    var $role_passwords;
    var $web_root;
    var $db_user;
    var $db_password;
    var $coa_only;
    var $complete_coa_tables;

    public function __construct() { }

    public function getUrl() {
        return 'http://'.$this->ip;
    }

    static function initCoaServers() {
        self::$coa_servers = [];
        $configured_coa_servers = parse_ini_file(self::$coa_servers_file, true);
        foreach($configured_coa_servers as $name=>$coaServer) {
            /** @var ServeurCoa $coaServerObject */
            $coaServerObject = Util::cast(__CLASS__, json_decode(json_encode($coaServer)));
            if (isset($coaServerObject->role_passwords)) {
                $roles = [];
                array_walk($coaServerObject->role_passwords, function($role) use (&$roles) {
                    list($roleName, $rolePassword) = explode(':', $role);
                    $roles[$roleName] = $rolePassword;
                });
                $coaServerObject->role_passwords = $roles;
            }
            if ($coaServerObject->complete_coa_tables) {
                self::$coa_servers[$name] = $coaServerObject;
            }
            else {
                self::$ducksmanager_server = $coaServerObject;
            }
        }
    }

    /**
     * @param $name string
     * @return ServeurCoa|null
     */
    static function getCoaServer($name) {
        return self::$coa_servers[$name] ?? null;
    }
}

ServeurCoa::initCoaServers();