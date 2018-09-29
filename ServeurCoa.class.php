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

    /**
     * @param string $method
     * @param string $path
     * @param string $role
     * @param array $parameters
     * @return mixed|null
     */
    public function getServiceResults($method, $path, $role, $parameters = [])
    {
        $ch = curl_init();
        $url = $this->getUrl() . $this->web_root . $path;

        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($parameters));
                break;
            case 'GET':
                if (count($parameters) > 0) {
                    $url .= '/' . implode('/', $parameters);
                }
                break;
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, $method === 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        $headers = [
            'Authorization: Basic ' . base64_encode(implode(':', [$role, $this->role_passwords[$role]])),
            'Content-Type: application/x-www-form-urlencoded',
            'Cache-Control: no-cache',
            'x-dm-version: 1.0',
        ];
        if (isset($_COOKIE['user'])) {
            $headers[] = 'x-dm-user: ' . $_COOKIE['user'];
            $headers[] = 'x-dm-pass: ' . $_COOKIE['pass'];
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $buffer = curl_exec($ch);
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (!empty($buffer) && $responseCode >= 200 && $responseCode < 300) {
            $results = json_decode($buffer, true);
            if (is_array($results)) {
                return $results;
            }
        }
        return [];
    }

    /**
     * @param string $query
     * @param string $db
     * @return mixed|null
     */
    public function getQueryResultsFromRemote($query, $db)
    {
        $parameters = [
            'query' => $query,
            'db' => $db
        ];
        return $this->getServiceResults('POST', '/rawsql', 'rawsql', $parameters);
    }

    /**
     * @param string $url
     * @param bool $dbg
     * @return string
     */
    public function getSecuredPage($url, $dbg)
    {
        $baseUrl = $this->getUrl() . '/' . $this->web_root;
        $fullUrl = $baseUrl . '/' . $url . '&mdp=' . sha1($this->db_password);
        if ($dbg) {
            echo $fullUrl . '<br /><br />';
        }
        return Util::get_page($fullUrl);
    }

    /**
     * @param string $requete
     * @param string $db
     * @return array|null
     */
    public function getQueryResults($requete, $db)
    {
        if (isset($this->role_passwords)) { // DM server
            $output = $this->getQueryResultsFromRemote($requete, $db);
        } else {
            $output = $this->getSecuredPage("sql.php?db=$db&req=" . urlencode($requete), isset($_GET['dbg']));
        }
        if (isset($_GET['brut'])) {
            echo 'Requete : ' . $requete . '<br />'
                . 'Retour brut : <pre>' . $output . '</pre>'
                . 'Stacktrace : <pre>';
            debug_print_backtrace();
            echo '</pre>';
        }

        if (empty($output) || $output === ERREUR_CONNEXION_INDUCKS) {
            return [];
        }

        if (isset($this->role_passwords)) { // DM server
            return $output;
        }

        $unserialized = @unserialize($output);
        if (is_array($unserialized)) {
            list($champs, $resultats) = $unserialized;
            foreach ($champs as $i_champ => $nom_champ) {
                foreach ($resultats as $i => $resultat) {
                    $resultats[$i][$nom_champ] = $resultat[$nom_champ];
                }
            }
            return $resultats;
        }
        return [];
    }

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