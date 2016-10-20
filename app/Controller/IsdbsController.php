<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');

/**
 * Class JcampldrsController
 * Controller for Refractive Index data
 */
class IsdbsController extends AppController
{

    public $uses = ['Isdbset','Isdbsample'];

    /**
     * beforeFilter function
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    public function ingest($type="meta")
    {
        if($type=="meta") {
            $meta = new Folder('/Users/n00002621/Dropbox/Research - Cheminfo/OSDB/IS-DB/metadata');
            $files = $meta->find('.*\.xml');
            foreach($files as $file) {
                $file = new File($meta->pwd() . DS . $file);
                $text = $file->read();
                $xml = simplexml_load_string($text);
                $data=json_decode(json_encode($xml),true);
                $set=$data['data_set'];
                $sam=$data['sample'];
                $set['id']=$set['DataSetId'];unset($set['DataSetId']);
                $set['author_id']=$set['ContactAuthor'];unset($set['ContactAuthor']);
                $set['publication_id']=$set['PublicationId'];unset($set['PublicationId']);
                $set['sample_id']=$set['SampleId'];unset($set['SampleId']);
                $set['datatype']=$set['DataType'];unset($set['DataType']);
                $set['dataformat']=$set['DataFormat'];unset($set['DataFormat']);
                $set['description']=$set['Description'];unset($set['Description']);
                if(isset($set['Instrument'])) {
                    $set['instrument']=$set['Instrument'];unset($set['Instrument']);
                } else {
                    $set['instrument']="";
                }
                if(isset($set['MeasurementTechnique'])) {
                    $set['measurement'] = $set['MeasurementTechnique'];unset($set['MeasurementTechnique']);
                } else {
                    $set['measurement']="";
                }
                $set['oldfilename']=$set['OldFileName'];unset($set['OldFileName']);
                $set['newfilename']=$set['NewFileName'];unset($set['NewFileName']);
                $set['mimetype']=$set['MimeType'];unset($set['MimeType']);
                $set['filesize']=$set['FileSize'];unset($set['FileSize']);
                $set['submitted']=$set['SubmissionDate'];unset($set['SubmissionDate']);
                $set['flags']=$set['DataSetFlags'];unset($set['DataSetFlags']);

                debug($set);debug($sam);exit;
            }
            debug($files);exit;
        }
    }

/*array(
'DataSetId' => '11',
'ContactAuthor' => '29',
'PublicationId' => '10',
'SampleId' => '14',
'DataType' => 'IR / infrared spectrum',
'DataFormat' => 'JCAMP-DX Data (dx, jdx, jcm)',
'Description' => 'unknown',
'OldFileName' => 'D1pp75.dx',
'NewFileName' => '10-D11.dx',
'MimeType' => 'chemical/x-jcamp-dx',
'FileSize' => '10602',
'SubmissionDate' => '2003-04-29 22:26:36',
'DataSetFlags' => '0'
)*/

    /**
     * Show all datasets
     */
    public function index() {
        $sets=$this->Isdbset->find('all');
        debug($sets);exit;
    }
}
