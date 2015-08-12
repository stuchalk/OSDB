<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataseries
 * Dataseries model
 * Dataseries are logically related data, spectra, chromatogram, kinetics run, fiagram, reaction monitoring
 * Conditions for a dataseries can be constant for the entire series (parameters), or changing (variables)
 */
class Dataseries extends AppModel
{
    public $hasMany = ['Condition'=>['foreignKey'=>'dataseries_id','dependent' => true],
                        'Datapoint'=>['foreignKey'=>'dataseries_id','dependent' => true],
                        'Annotation','Descriptor'];

    public $belongsTo = ['Dataset'];

    /**
     * General function to add a new dataseries
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Dataseries';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}