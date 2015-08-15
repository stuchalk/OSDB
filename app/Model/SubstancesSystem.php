<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class SubstancesSystem
 * SubstancesSystem model
 * Join table for substances that make up the components of systems
 */
class SubstancesSystem extends AppModel
{

    /**
     * Find unique system based on substances
     * System must contain all of an only the number of substances provided
     * @param array $subs
     * @return array|null
     */
    public function findUnique($subs)
    {
        //debug($subs);
        $tmp1=$this->find('all',['fields'=>['system_id','COUNT(*) as total'],'group'=>['system_id HAVING (total = '.count($subs).')' ]]);
        //debug($tmp1);
        $c=[];
        foreach($tmp1 as $x) { $c[]=$x['SubstancesSystem']['system_id']; }
        $tmp2=$this->find('all',['fields'=>['DISTINCT system_id'],'conditions'=>['substance_id'=>$subs],'group'=>'system_id']);
        //debug($tmp2);
        $s=[];
        foreach($tmp2 as $y) { $s[]=$y['SubstancesSystem']['system_id']; }
        foreach($s as $z) {
            if(in_array($z,$c)) {
                return $z;
            }
        }
        return null;
    }

    /**
     * General function to add a new substances_systems
     * @param $data
     * @return integer
     */
    public function add($data)
    {
        $model='SubstancesSystem';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}