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
        //echo "TEMP1: ";debug($tmp1);
        $ret=null;
        foreach($tmp1 as $x) {
            $c=0;$sys=$x['SubstancesSystem']['system_id'];
            foreach($subs as $sub) {
                $tmp2=$this->find('all',['fields'=>['system_id'],'conditions'=>['substance_id'=>$sub,'system_id'=>$sys]]);
                if(empty($tmp2)) {
                    break;
                } else {
                    $c++;
                }
            }
            if($c==count($subs)) {
                $ret=$sys;break;
            }
        }
        return $ret;
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