<?php

/**
 * Class ReferencesController
 */
class ReferencesController extends AppController
{

    public $uses=['Reference','Crossref','File'];

    /**
     * Ingest function
     * @param $filename
     */
    public function ingest($filename)
    {
        $xml=simplexml_load_file('/Users/stu/Desktop/'.$filename.'.xml');
        $refs=$xml->xpath('//Citation');
        foreach($refs as $ref) {
            $temp=[];
            $attrs=$ref->attributes();
            $temp['sid']=(string) $attrs['ID'];
            $temp['citenum']=(string) $ref->CitationNumber;
            $temp['authors']=[];
            $authors=$ref->BibArticle->BibAuthorName;
            if(!empty($authors))
            {
                foreach($authors as $author)
                {
                    $temp['authors'][]=["firstname"=>(string) $author->Initials,"lastname"=>(string) $author->FamilyName];
                }
            }
            $temp['authors']=json_encode($temp['authors']);
            if(isset($ref->BibArticle->Year)) { $temp['year']=(string) $ref->BibArticle->Year; }
            $temp['oldjournal']=(string) $ref->BibArticle->JournalTitle;
            $temp['journal']=trim(str_replace(".",". ",$temp['oldjournal']));
            $temp['volume']=(string) $ref->BibArticle->VolumeID;
            $temp['startpage']=(string) $ref->BibArticle->FirstPage;
            $temp['bibliography']=(string) $ref->BibUnstructured;
            $temp['bibliography']=str_replace(["\t","\n"],[""," "],$temp['bibliography']);
            $temp['bibliography']=str_replace($temp['oldjournal'],$temp['journal'],$temp['bibliography']);
            unset($temp['oldjournal']);

            // Save data
            $this->Reference->create();
            $this->Reference->save(['Reference'=>$temp]);
            $this->Reference->clear();
        }
        exit;
    }

    /**
     * Extract function
     * @param $fileID
     */
    public function extract($fileID){
        if(isset($fileID)) {
            $file = $this->File->find('first',
                ['conditions' =>
                        ['File.id' => $fileID],
                    'contain' =>[
                        'TextFile' => [
                            'order' => 'TextFile.updated DESC',
                            'limit' => 1
                        ]
                    ]
                ]); //get the file of interest
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $pdfToTextPath = Configure::read("pdftotextPath.windows"); //save path to the pdftotext for the server
            }elseif (PHP_OS=="Linux") {
                $pdfToTextPath=Configure::read("pdftotextPath.linux");
            }elseif (PHP_OS=="FreeBSD") {
                $pdfToTextPath=Configure::read("pdftotextPath.freebsd");
            }else{
                $pdfToTextPath=Configure::read("pdftotextPath.mac");
            }
            $fileToExtract=WWW_ROOT.'files'.DS.'pdf'.DS.$file['File']['publication_id'].DS.$file['File']['filename'];// find the path to the file name

            exec($pdfToTextPath.' -layout -r 300  "'. $fileToExtract.'" -',$lines); //run the extraction
            $start=false;
            $data=json_decode($file['TextFile'][0]['extracted_data'],true);
            var_dump($data['citation']);
            $citation="";
            foreach($lines as $line){
                if(strpos($line,$data['citation'])!==false){
                    $start=true;
                }
                if($start==true){
                    if($line!==""){
                        $citation.=$line." ";
                    }else{
                        break;
                    }
                }
            }
            var_dump($citation);
            $client = new SoapClient("http://wing.comp.nus.edu.sg/parsCit/wing.nus.wsdl");
            $client->extract_citations($citation);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => 'http://freecite.library.brown.edu/citations/create',
                CURLOPT_USERAGENT => 'ChalkLab Citation Retriever',
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => ['citation' => $citation],
                CURLOPT_HEADER=>'Accept: text/xml'

            ]);
            $result = curl_exec($curl);
            curl_close($curl);
            echo $result;
            die();
        }

    }

    /**
     * Get doi function
     */
    public function getdois()
    {
        $refs=$this->Reference->find('all',['conditions'=>['id >'=>5000],'limit'=>4830]);
        foreach($refs as $ref) {
            $response=$this->Crossref->openurl($ref['Reference']);
            if($response['crossref']=='yes') {
                $this->Reference->save(['Reference'=>$response]);
                echo $response['sid']." updated<br />";
            } else {
                echo $response['sid']." not found<br />";
            }
        }
        exit;
    }
}