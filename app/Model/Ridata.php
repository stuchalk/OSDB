<?php
App::uses('AppModel', 'Model');
App::uses('ClassRegistry', 'Utility');

/**
 * Class Ridata
 * RI Data model
 */
class Ridata extends AppModel {
	
	public $belongsTo = array('Reference','Chemical');

    /**
     * RI data ingest function
     * @param $text
     * @param $file
     * @return array
     * @throws Exception
     */
	public function ingest($text,$file)
	{
		// Intro lines - variable
		// Stop of data is blank line
		// Optional:  If references found in table look for next line to contain )
		// End of compound is two empty lines
		
		$output=$data=array();
		for($x=0;$x<count($text);$x++)
		{
			// Detect first compound to process - first line with first char as non space
			if(empty($text[$x])||substr($text[$x],0,1)==" "):	continue;
			else:												break;
			endif;
		}
		
		for($y=$x;$y<count($text);$y++)
		{
			$temp=array();
			
			// Detect presence of 'Compounds' heading
			if(stristr($text[$y],'compounds'))
			{
				if($y+1<count($text)) { $y++; } else { break; } // break out if we are at the last line 
				while(trim($text[$y]=="")) { $y++; }
			}
			
			// First line
			$temp['comp']=$this->processCompound($text[$y],$y,$output,$data);
			$output[]="SL: ".$text[$y];
			//echo $temp['comp'][0].') '.$temp['comp'][2].'<br />';
			
			// Second line is empty (or all whitespace)
			if($y+1<count($text)) { $y++; } else { break; } // break out if we are at the last line 
			if(trim($text[$y])!=""):	echo "Error on line ".($y+1)." - non-empty line";exit;
			else:						$output[]="EL: ".$text[$y];
			endif;
			
			// Third line is all ----, check: no space or letters
			if($y+1<count($text)) { $y++; } else { break; } // break out if we are at the last line 
			$chars=count_chars($text[$y],1);
			if(count($chars)!==1||!isset($chars[45]))
			{
				echo "Error on line ".($y+1)." - start of table header not found";exit;
			}
			$output[]="HT: ".$text[$y];
			
			// Fourth line - column headers
			if($y+1<count($text)) { $y++; } else { break; } // break out if we are at the last line 
			$temp['cols']=$this->processColumnTitles($text[$y],$y);
			$output[]="HM: ".$text[$y];
			
			// Fifth line is all ----, check: no space or letters
			if($y+1<count($text)) { $y++; } else { break; } // break out if we are at the last line 
			$chars=count_chars($text[$y],1);
			if(count($chars)!==1||!isset($chars[45]))
			{
				echo "Error on line ".($y+1)." - start of table header not found";exit;
			}
			$output[]="HB: ".$text[$y];
			
			// Sixth thru ? line is data
			if($y+1<count($text)) { $y++; } else { break; } // break out if we are at the last line 
			while(stristr($text[$y],'|'))
			{
				$temp['data'][]=$this->processData($text[$y],$y);
				$output[]="DL: ".$text[$y];
				$y++;
			}
			
			// Next line is empty (or all whitespace)
			if(trim($text[$y])!=""):	echo "Error on line ".($y+1)." - non-empty line (A)";exit;
			else:						$output[]="EL: ".$text[$y];
			endif;
			
			// Optional conditions line(s)
			if(!empty(array_column($temp['data'],4)))
			{
				// Find the line(s)
				$y++;
				$condstr="";
				while($text[$y]!="") { $condstr.=" ".trim($text[$y]);$y++; }
				$conds=explode(",",$condstr);
				$conds=array_map('trim',$conds);
				for($z=0;$z<count($temp['data']);$z++)
				{
					for($a=0;$a<count($conds);$a++)
					{
						if(isset($temp['data'][$z][4])&&stristr($conds[$a],$temp['data'][$z][4]))
						{
							$temp['data'][$z][4]=$conds[$a];
						}
					}
				}
				
				// Next line is empty
				if(trim($text[$y]!="")):	echo "Error on line ".($y+1)." - non-empty line (B)";exit;
				else:						$output[]="EL: ".$text[$y];
				endif;
			}
			
			// At least one empty line -> till last one of compound
			$y++;
			if(!isset($text[$y])) { break; } // Check for end of file
			while(trim($text[$y])=="")
			{
				$output[]="EL: ".$text[$y];$y++;
				if(!isset($text[$y])) { break; } // Check for end of file
			}
			$y--; // So that loop increment takes script to the first line of the new compound
			
			//echo "<pre>";print_r($temp);echo "</pre>";exit;
			// End of compound - save in $data array
			$data[]=$temp;
			
			// Add compound if it does not exist (inchistr or cas)
			if($temp['comp'][4]!=="NA"):	$result=$this->Compound->find('first',array('conditions'=>array('inchistr'=>$temp['comp'][4])));
			else:							$result=$this->Compound->find('first',array('conditions'=>array('cas'=>$temp['comp'][3])));
			endif;
			if(empty($result))
			{
				$c=$temp['comp'];
				$cdata=array();
				$cdata['sid']=$file.":".$c[0]; // Assumming that compound ids are specific to different data series (books)
				$cdata['formula']=$c[1];
				$cdata['name']=ucfirst($c[2]);
				$cdata['cas']=$c[3];
				$cdata['inchistr']=$c[4];
				$cdata['inchikey']=str_replace("InChIKey=","",$c[5]);
				
				$this->Chemical->create();
				$this->Chemical->save(array('Chemical'=>$cdata));
				$cid=$this->Chemical->id;
				$this->Chemical->clear();
			}
			else
			{
				$cid=$result['Chemical']['id'];
			}
			
			// Add ri data
			foreach($temp['data'] as $r)
			{
				$ridata=array();
				$ridata['book']=$file;
				$ridata['compound_id']=$cid;
				$ridata['value']=$r[0];
				if($r[1]==NULL||$r[1]==""):	$ridata['temperature']=0;
				else:						$ridata['temperature']=$r[1]+297.16; // Convert to Kelvin
				endif;
				if($r[2]==NULL||$r[2]==""):	$ridata['wavelength']=0;
				else:						$ridata['wavelength']=$r[2];
				endif;
				
				// Get reference id
				$result=$this->Reference->find('first',array('conditions'=>array('citenum'=>$r[3])));
				if(empty($result)):	$ridata['reference_id']=$r[3];
				else:				$ridata['reference_id']=$result['Reference']['id'];
				endif;
				if(isset($r[4])) { $ridata['comment']=$r[4]; }
				
				// Save data
				$this->create();
				$this->save(array('Ridata'=>$ridata));
				$this->clear();
			}
		}
		
		return $data;
	}

    /**
     * RI data process function
     * @param $linetxt
     * @param $linenum
     * @param $o
     * @param $d
     * @return array
     */
	public function processCompound($linetxt,$linenum,$o,$d)
	{
		// First line of compound - explode on space, checks: array has a count of 4, last value has -
		$line = preg_replace('!\s{2,}!',"  ",$linetxt);
		$comp=explode("  ",$line);
		if(count($comp)<3):				echo "<pre>";print_r($o);print_r($d);echo "</pre>";
										echo "Error on line ".($linenum+1)." - wrong # of values expected for compound";exit;
		elseif(count($comp)==3):		echo "Error on line ".($linenum+1)." - no CAS#<br />";
										$comp[3]='NA';
		elseif(!stristr($comp[3],"-")):	echo "Error on line ".($linenum+1)." - last value is not a CAS#<br />";
										$comp[3].=" (not a CAS#)";
		endif;
		$comp[1]=str_replace(' ','',$comp[1]); // Clean up chemical formula
		
		// Lookup compound InChI String and Key
		$inchi="yes";
		if($inchi=="yes")
		{
			$cir="http://cactus.nci.nih.gov/chemical/structure/";
			$h=get_headers($cir.rawurlencode($comp[3])."/stdinchi");
			if(stristr($h[0],'OK'))
			{
				$comp[4]=file_get_contents($cir.$comp[3]."/stdinchi");
				$comp[5]=file_get_contents($cir.$comp[3]."/stdinchikey");
			}
			else
			{
				$h=get_headers($cir.rawurlencode($comp[2])."/stdinchi");
				if(stristr($h[0],'OK'))
				{
					$comp[4]=file_get_contents($cir.$comp[2]."/stdinchi");
					$comp[5]=file_get_contents($cir.$comp[2]."/stdinchikey");
				}
				else
				{
					$comp[4]='NA';$comp[5]='NA';
				}
			}
		}
		return $comp;
	}

    /**
     * RI data process column titles function
     * @param $linetxt
     * @param $linenum
     * @return array
     */
    public function processColumnTitles($linetxt,$linenum)
	{
		// Fourth line are table headings - explode on space, checks: array count is 4, last value is 'Ref.'
		$line = preg_replace('!\s{2,}!',"  ",trim($linetxt));
		$cols=explode("  ",$line);
		if(count($cols)!=4):		echo "Error on line ".($linenum+1)." - wrong # of values expected for columns";exit;
		elseif($cols[3]!="Ref."):	echo "Error on line ".($linenum+1)." - last value is not 'Ref.'";exit;
		endif;
		return $cols;
	}

    /**
     * RI data process data function
     * @param $linetxt
     * @param $linenum
     * @return array
     */
	public function processData($linetxt,$linenum)
	{
		// Sixth through ? line is data - check: prescence of | symbol, explode on | => array count is 4,
		//     check for prescence of space in last value of array, if present split on space, check for
		//     letter in last value of array (validates reference)
		//echo $linetxt.'<br />';
		$vals=explode("|",$linetxt);
		$vals=array_map('trim',$vals);
		if(count($vals)!=4)									{ echo "Error on line ".($linenum+1)." - wrong # of values expected for data";exit; }
		if(is_numeric($vals[3])&&!stristr($vals[3],'E'))	{ echo "Error on line ".($linenum+1)." - wrong value for reference id";exit; }
		// Check for reference
		if(stristr($vals[3],' '))
		{
			list($vals['3'],$vals['4'])=explode(" ",$vals['3'],2);
			$vals['4']=str_replace(')','',$vals['4']);
		}
		return $vals;
	}
	
}
?>