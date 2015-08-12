<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Dataset
 * Dataset model
 * Dataset is a logical aggregation of data from a single source
 * All data about the set hangs of it in some way
 * The representation of a dataset in a publication is a report
 * The original source of the data is a reference
 * THe data may have been obtained from a file (i.e. spectra)
 */
class Dataset extends AppModel
{
    public $hasOne=['Methodology','Context','Sample'];

    public $hasMany = ['Dataseries'=>['foreignKey'=>'dataset_id','dependent'=>true],
                        'Data'=>['foreignKey'=>'dataset_id','dependent'=>true],
                        'Annotation'];

    public $belongsTo = ['Propertytype','Reference','File','Report'];

    /**
     * General function to add a new dataset
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Dataset';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}