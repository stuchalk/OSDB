<?php

/**
 * Class JcampController
 * Controller for Refractive Index data
 */
class JcampController extends AppController {

    public $uses=['Jcamp','Property','Setting'];

    /**
     * View RI data
     */
    public function test()
    {
        $contain=['Property'=>['fields'=>['Property.name'],'Quantity'=>['fields'=>['Quantity.name','Quantity.si_unit'],'Unit'=>['fields'=>['Unit.symbol']]]]];
        $settings=$this->Jcamp->find('all',['fields'=>['Jcamp.arrayname','Jcamp.unit'],'conditions'=>['scidata'=>'settings'],'contain'=>$contain]);
        $jarray['PARAMS']=['OBSERVEFREQUENCY'=>'300.03180','ACQUISITIONTIME'=>'3.42098'];
        $jarray['BRUKER']=['OBSERVENUCLEUS'=>'^1H'];
        $params=array_merge($jarray['PARAMS'],$jarray['BRUKER']);

        foreach($settings as $s) {
            if(isset($params[$s['Jcamp']['arrayname']])) {
                // Need methodology, property_id, number, unitid, accuracy
                $number=$params[$s['Jcamp']['arrayname']];
                $propid=$s['Property']['id'];
                $unit=$s['Jcamp']['unit'];
                $units=$s['Property']['Quantity']['Unit'];
                $unitid=null;
                for($x=0;$x<count($units);$x++) {
                    if($units[$x]['symbol']==$unit) {
                        $unitid=$units[$x]['id'];
                    }
                }
                $acc=strlen(str_replace(".","",$number));
                $setn=['methodology_id'=>1,'property_id'=>$propid,'unit_id'=>$unitid,'number'=>$number,'accuracy'=>$acc];
                $this->Setting->create();
                $data=$this->Setting->save(['Setting'=>$setn]);

                debug($data);exit;
            }
        }
    }
}