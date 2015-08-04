<?php

App::uses('Reader', 'Vendor');

/**
 * Class MaterialsController
 */
class MaterialsController extends AppController {

	public $uses=array('Material','Ridata','Reader');

    /**
     * Function to ingest data file into the system
     * Stuart Chalk
     */
	public function ingest()
	{
		//echo "<pre>";print_r($this->data);echo("</pre>");exit;
		if(!empty($this->data))
		{
			$file=$this->data['Material']['file'];
			
			if($file['type']=="text/plain")
			{
				// Identify type of file to process
				$text=file($file['tmp_name'],FILE_IGNORE_NEW_LINES);
				if(stristr($text[0],'refractive')):
					unset($text[0]);$data=$this->Ridata->ingest($text,$file['name']);
				else:
					echo "Property type unknown";exit;
				endif;

				// Get statistics from tables
				$stats=array();
				$stats['data']=$this->Ridata->find('count',array('conditions'=>array('Ridata.book'=>$file['name'])));
				$stats['comps']=count($data);
				$stats['lines']=count($text);
				
				// Chemicals list
				$comps=array();
				foreach($data as $comp)
				{
					$comps[]=$comp['comp'][0].": ".$comp['comp'][2];
				}
				
				// Save as JSON file
				$jsonfile='files'.DS.'json'.DS.str_replace(".txt",".json",$file['name']);
				$fp=fopen(WWW_ROOT.$jsonfile,"w");
				fwrite($fp,json_encode($data));
				fclose($fp);
				
				// Send variables to view
				$this->set('filename',$file['name']);
				$this->set('jsonfile',$jsonfile);
				$this->set('stats',$stats);
				$this->set('comps',$comps);
				$this->render('ingestdone');
				//echo "<pre>";print_r($data);echo "</pre>";exit;
			}
			else
			{
				$this->Session->setFlash('Please upload a text file :)');
			}
		}
		else
		{

		}
	}

    /**
     * Generic function - test Johns reader/readertest code
     */
	public function test()
	{
        $reader=new Reader();

        /**
         * Config Array Documentation
         *  The config is a stack of nested associative arrays and defined arrays
         *  First Level
         *  Required:
         *      "Rules": An array assigned to rules which defines what the reader is looking for
         *  Optional:
         *      "skipblank": Should the script skip all blank lines it encounters before using any rules
         *
         *
         *  Rules Array
         *      The rules array is a defined array that should start at 1 corresponding to the first line read
         *      the reader will read the given text file one line at a time and for each line it reads it will
         *      go to the next line in the rules array unless told otherwise by a rule.
         *
         * Rules in the Rule Array
         *      Each line in the rules array can have any number of rules starting at 1. Each line will read through every rule
         *      for its line before going onto the next line there are only a single required option and several optional values
         *      for each rule
         *
         *
         * Required Values for Rules
         *      "ACTION": An action to take, if there is a pattern it will only take the action if the pattern is found
         *
         *
         * Optional Values for rules
         *      "pattern": A regex pattern to search this line for, if found will preform ACTION, else will preform FAILURE
         *      "FAILURE": The action to take should the given pattern not be found
         *      "valuename": If the ACTION or FAILURE is STORE, this is the name of the value in the results array where it will
         *                   be stored. Will always use the first found unless matchIndex is set
         *      "error": If the FAILURE is set to exception then this is the error message of the exception
         *      "matchIndex": Which result from the regex pattern to store, normalized so that first result is 1 not 0
         *      "headerIndex": If set it will store the value in the results array under the header found at given index
         *      "debug": If set to true this rule will dump the matches, headers, and all relevant config info to the page
         *               whenever it encounters this rule and then continue.
         *      "matchMethod": if not set assumes you want to use preg_match_all, otherwise will use the method specified
         *                     preg_match_all allows for smaller regex patterns however will not guarantee that a value is there
         *                     due to matching the pattern as many times as its found
         *                     preg_match often requires longer regex patterns but will only match the pattern once allowing
         *                     one to guarantee its there.
         *      "notAnomaly": if set won't store this value as an anomaly if missing
         *
         *
         * Available Actions
         *      NEXTLINE: Skip this line and go to the next line in the config and file
         *      USELAST: Use the last rule and then move forward 2 rules
         *      USELASTLINE: Reread this line using the previous lines rules then go forward 2 lines
         *      END: End the reading here and return the results
         *      SKIP: Skip this rule and continue onto the next
         *      STORE: Store this value in either valuename or the header value at headerIndex, if matchIndex is set use that
         *               result
         *      EXCEPTION: Throw an exception with message from error value or a generic message, stops reading does not return
         *      STOREASHEADER: store this value as the next index in the header array, follows same rules as STORE
         *      USELASTLINEUNTIL: Continue using the last line in the config array until the pattern fails to match
         *      STOREALL: Store all the values found in the match array, follows same rules as STORE
         *      STOREALLASHEADER: Store all the values found in the match array as headers, follows same rules as STORE
         *
         * Simple Example
         * $config=array(
         *      "Rules"=>array(
         *  Line->  1=>array(
         *  rule->      1=>array(
         *                  "ACTION":END
         *              )
         *          )
         *      )
         * )
         */

        $config=array(
            "Rules"=>array(
                1=>array(
                    1=>array(
                        "pattern"=>'^(\d+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"NEXTLINE",
                        "valuename"=>"id",
                    ),
                    2=>array(
                        "pattern"=>'(( \w+)+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"EXCEPTION",
                        "error"=>"Chemical formula not found",
                        "valuename"=>"chemical",
                        "required"=>true,
                    ),
                    3=>array(
                        "pattern"=>'(( [A-Za-z0-9-\)\(\]\[]+)+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"EXCEPTION",
                        "error"=>"Chemical name not found",
                        "valuename"=>"chemicalName",
                        'matchIndex'=>2,
                    ),
                    4=>array(
                        "pattern"=>'(( [A-Za-z0-9-\)\(\]\[]+)+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"STORE",
                        "valuename"=>"CAS",
                        'matchIndex'=>3,
                    ),
                ),
                2=>array(
                    //Get the four headers
                    1=>array(
                        "pattern"=>'(( [A-Za-z0-9]+)+([A-Za-z0-9\/]+)*)',
                        "ACTION"=>"STOREALLASHEADER",
                        "FAILURE"=>"NEXTLINE",
                    ),
                ),
                3=>array(
                    1=>array(
                        "pattern"=>'(-?[0-9.]+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"NEXTLINE",
                        "matchMethod"=>"preg_match",
                        "headerIndex"=>1,
                    ),
                    2=>array(
                        "pattern"=>'(-?[0-9.]+) +\|( ){1,3}(-?[0-9.]+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"STORE",
                        "headerIndex"=>2,
                        "matchMethod"=>"preg_match",
                        'matchIndex'=>3,
                    ),
                    3=>array(
                        "pattern"=>'(-?[0-9.]+) +\|.+\|( ){1,3}(-?[0-9.]+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"STORE",
                        "headerIndex"=>3,
                        "matchMethod"=>"preg_match",
                        'matchIndex'=>3
                    ),
                    4=>array(
                        "pattern"=>'(-?[0-9.]+) +\|.+\|([0-9A-Za-z.]+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"STORE",
                        "error"=>"Reference not found",
                        "headerIndex"=>4,
                        "matchMethod"=>"preg_match",
                        'matchIndex'=>2
                    ),
                    5=>array(
                        "pattern"=>'(-?[0-9.]+) +\|.+\|([0-9A-Za-z.]+) ([0-9A-Za-z.\)]+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"STORE",
                        "matchMethod"=>"preg_match",
                        "valuename"=>"comment",
                        'matchIndex'=>3,
                        "notAnomaly"=>true,
                    ),
                ),
                4=>array(
                    //repeat last until end
                    1=>array(
                        "pattern"=>'(-?[0-9A-Za-z.]+)+',
                        "ACTION"=>"USELASTLINEUNTIL",
                        "FAILURE"=>"SKIP",
                    ),
                ),
                5=>array(
                    //repeat last until end
                    1=>array(
                        "pattern"=>'(.+)',
                        "ACTION"=>"STORE",
                        "FAILURE"=>"END",
                        "valuename"=>"commentLine",
                    ),
                    2=>array(
                        "ACTION"=>"END",
                    ),
                ),

            ),
            "skipblank"=>false

        );
        $reader->SetConfig($config);
        $reader->LoadFile('files/10478514_7.txt');
        //$reader->LoadFile('D:\Chalk\Dropbox\Shared\Springer\10478514_sources\10478514_7.txt');
        $chemicals=0;
        while($array=$reader->ReadFile()) {
            //replace the comment codes with the actual comments
            if(isset($array['commentLine'])&&$array['commentLine']) {
                $comments = explode(",",$array['commentLine']);
                foreach($comments as $string) {
                    if (isset($array['comment'])&&is_array($array['comment'])) {
                        foreach ($array['comment'] as &$comment) {
                            if (strpos($string, $comment) !== false) {
                                $comment = trim($string);
                            }
                        }
                    } else {
                        $array['comment'] = trim($string);
                    }
                }
            }
            unset($array['commentLine']);
            var_dump($array);
            echo $reader->line."<br>";

            $chemicals++;

        }

        echo $chemicals." Have Been Recorded<br>";
        echo count($reader->anomalies)." Problems have been found<br>";
        foreach($reader->anomalies as $problem) {
            echo $problem."<br>";
        }
	}
}

?>