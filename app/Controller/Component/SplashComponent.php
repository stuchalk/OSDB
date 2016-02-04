<?php
App::uses('Component','Controller');
App::uses('HttpSocket', 'Network/Http');

/**
 * Class SplashComponent
 * Functions for requesting and validating spectral hashes
 * http://splash.fiehnlab.ucdavis.edu/
 */
class SplashComponent extends Component
{

    /**
     * Request a splash for a file
     * Send request to http://splash.fiehnlab.ucdavis.edu/splash/it
     * Use POST and JSON data packet
     * @param integer $rid
     * @param string $type
     * @param array $data
     */
    public function generate($rid,$type,$data)
    {
        //$tpl='{"ions":[{"mass":100,"intensity":1},{"mass":101,"intensity":2},{"mass":102,"intensity":3}],"type":"MS"}';
        $xdata=json_decode($data['Condition'][0]['number'],true);
        $ydata=json_decode($data['Data'][0]['number'],true);
        if($type=="Mass Spectrometry") { // MS
            $sarray=['ions'=>[],'type'=>'MS'];
            for($i=0;$i<count($xdata);$i++) {
                $sarray['ions'][]=['mass'=>$xdata[$i],'intensity'=>$ydata[$i]];
            }
            $json=json_encode($sarray);
            $http = new HttpSocket();
            // Have to overrid header:content-type as charset=utf8 causes error on server
            $response=$http->post('http://splash.fiehnlab.ucdavis.edu/splash/it','',['body'=>$json,'header'=>['Content-Type'=>'application/json']]);
            $splash=$response->body;

        } elseif($type=="Nuclear Magnetic Resonance") { // NMR
            $sarray=['ions'=>[],'type'=>'NMR'];
            for($i=0;$i<count($xdata);$i++) {
                $sarray['ions'][]=['mass'=>$xdata[$i],'intensity'=>$ydata[$i]];
            }
            $json=json_encode($sarray);
            $http = new HttpSocket();
            // Have to overrid header:content-type as charset=utf8 causes error on server
            $response=$http->post('http://splash.fiehnlab.ucdavis.edu/splash/it','',['body'=>$json,'header'=>['Content-Type'=>'application/json']]);
            $splash=$response->body;

    }
        return $splash;
    }
}