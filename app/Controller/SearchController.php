<?php

/**
 * Class SearchController
 * Actions related to dealing with searches
 * @author Stuart Chalk <schalk@unf.edu>
 */
class SearchController extends AppController
{

    public $uses=['Annotation','Chemical','Condition','Context',
                    'Data','Dataseries','Dataset','Descriptors',
                    'Identifier','Measurement','Metadata','Methodology',
                    'Publication','Quantity','Reference',
                    'Report','Substance','System','Unit'];

    /**
     * function beforeFilter
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    /**
     * List the quantities
     */
    public function simple($term)
    {
        $tables=$this->uses;
        $results=[];
        $fields=['class','name','title','subdiscipline',
                    'title','processedType','property','title',
                    'value','technique','value','evaluation',
                    'title','description','title',
                    'description','name','name','name'];
        for($s=0;$s<count($tables);$s++) {
            $t=$tables[$s];$f=$fields[$s];
            $results[$t]=$this->{$t}->find('list',['fields'=>['id',$f],'conditions'=>[$f." like"=>'%'.$term.'%']]);
        }
        //debug($results);exit;
        $this->set('data',$results);
    }

    /**
     * Search by mass range
     * @param $range
     */
    public function mass($range) {
        if(stristr($range,"-")) {
            list($low,$high)=explode("-",$range);
        } else {
            $low=$range-1;$high=$range+1;
        }
        $data=$this->Substance->find('list',['fields'=>['id','name','molweight'],'conditions'=>['molweight >'=>$low,'molweight <'=>$high]]);
        $this->set('data',$data);
    }
}