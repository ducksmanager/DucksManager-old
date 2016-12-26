<?php
header('Content-Type: text/html; charset=utf-8');
error_reporting(E_ALL);
include_once('../Database.class.php');
if (!isset($noAuth)) {
    include_once('../authentification.php');
}

class DediboxApi {
    private static function curl($url, $method, $params = [], $token = null) {
        if (!empty($params) && $method === 'GET') {
            $url .= '?' . http_build_query($params);
        }
        if ($method === 'GET') {
            return file_get_contents($url);
        }

        $call = curl_init();
        curl_setopt($call, CURLOPT_URL, $url);
        if (!is_null($token)) {
            curl_setopt($call, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token, 'X-Pretty-JSON: 1']);
        }
        curl_setopt($call, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($call, CURLOPT_POST, true);
        curl_setopt($call, CURLOPT_POSTFIELDS, http_build_query($params));

        return curl_exec($call);
    }

    private static function call_online_api($token, $method, $endpoint, $params = []) {
        return self::curl('https://api.online.net/api/v1' . $endpoint, $method, $params, $token);
    }

    private static function getInfo($coaServer) {
        return json_decode(self::call_online_api(
            $coaServer->restart_token,
            'GET',
            '/server/'.$coaServer->machine_id
        ));
    }

    static function reboot($coaServer) {
        self::call_online_api(
            $coaServer->restart_token,
            'POST',
            '/server/reboot/'.$coaServer->machine_id,
            ['reason' => 'Server unresponsive']
        );
    }

    /**
     * @return bool
     */
    static function isDediboxAlive() {
        $info = json_decode(self::curl(
            'https://api.uptimerobot.com/getMonitors',
            'GET',
            ['apiKey'=> 'm776897992-f7af9503a82ec227f3eda2ff',
                  'monitors' => 'Dedibox DM sql',
                  'format' => 'json',
                  'noJsonCallback' => 1]
        ));
        return $info->monitors->monitor[0]->status === '2';
    }

    static function getSwitchPortState($coaServer) {
        $info = self::getInfo($coaServer);
        return $info->ip[0]->switch_port_state === 'up';
    }

    static function showInfo($coaServer) {
        echo '<pre>';print_r(self::getInfo($coaServer));echo '</pre>';
    }
}