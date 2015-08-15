<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Descriptor
 * Descriptor model
 * A descriptor is a piece of data that describes the data recorded
 * This might include the highest value, first data point etc.
 * Values that are calculated from the data are not descriptors
 * and may be represented as additional dataseries (peak tables)
 * or results (concentration of analyte)
 */
class Descriptor extends AppModel
{
    public $belongsTo = ['Dataset','Dataseries','Property','Unit'];

    /**
     * General function to add a new descriptor
     * @param array $data
     * @return integer
     */
    public function add($data)
    {
        $model='Descriptor';
        $this->create();
        $ret=$this->save([$model=>$data]);
        $this->clear();
        return $ret[$model];
    }

}