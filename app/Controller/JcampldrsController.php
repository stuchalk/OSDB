<?php

/**
 * Class JcampldrsController
 * Controller for Refractive Index data
 */
class JcampldrsController extends AppController {

    public $uses=['Jcampldr','Property','Setting'];

    /**
     * View RI data
     */
    public function test()
    {
        $contain=['Property'=>['fields'=>['Property.name'],'Quantity'=>['fields'=>['Quantity.name','Quantity.si_unit'],'Unit'=>['fields'=>['Unit.symbol']]]]];
        $settings=$this->Jcampldr->find('all',['fields'=>['Jcampldr.arrayname','Jcampldr.unit'],'conditions'=>['scidata'=>'settings'],'contain'=>$contain]);
        $jarray['PARAMS']=['OBSERVEFREQUENCY'=>'300.03180','ACQUISITIONTIME'=>'3.42098'];
        $jarray['BRUKER']=['OBSERVENUCLEUS'=>'^1H'];
        $params=array_merge($jarray['PARAMS'],$jarray['BRUKER']);

        foreach($settings as $s) {
            if(isset($params[$s['Jcampldr']['arrayname']])) {
                // Need methodology, property_id, number, unitid, accuracy
                $number=$params[$s['Jcampldr']['arrayname']];
                $propid=$s['Property']['id'];
                $unit=$s['Jcampldr']['unit'];
                $unitid = null;
                if($unit!="") {
                    $units = $s['Property']['Quantity']['Unit'];
                    for ($x = 0; $x < count($units); $x++) {
                        if ($units[$x]['symbol'] == $unit) {
                            $unitid = $units[$x]['id'];
                        }
                    }
                }
                $acc=strlen(str_replace(".","",$number));
                $setn=['measurement_id'=>1,'property_id'=>$propid,'unit_id'=>$unitid,'number'=>$number,'accuracy'=>$acc];
                $this->Setting->create();
                $data=$this->Setting->save(['Setting'=>$setn]);
                $this->Setting->clear();

                debug($data);
            }
        }
        exit;
    }
}