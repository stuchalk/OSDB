<?php
App::uses('Component', 'Controller');

class RecaptchaComponent extends Component {
    
    public function check($recaptcha=null)
    {
        $google_url = "https://www.google.com/recaptcha/api/siteverify";
        $secret = '6LcL5iMTAAAAAMcUfnYjOgeUKXsQhbYQihc5t51h';
        $ip = $_SERVER['REMOTE_ADDR'];
        $url = $google_url . "?secret=" . $secret . "&response=" . $recaptcha ."&remoteip=" . $ip;
        $res = file_get_contents($url);
        $res = json_decode($res, true);
        //debug($res);exit;
        if($res['success']){
            return $res;
        } else {
            return false;
        }
    }
}

?>