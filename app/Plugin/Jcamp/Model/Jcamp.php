<?php

/**
 * File containing all necessary functions to ingest a jcamp file of any type and generate an array of data ready for export in any format
 * Assumes that $file is an array of the lines of text in a JCAMP file
 * Version 1.0
 * Stuart J. Chalk
 * Created: 2015-01-13 (from old website)
 */
class Jcamp extends JcampAppModel
{
	public $useTable=false;
	var $actsAs=['Jcamp.Scidata'];

	public $file=[];
	public $comments=[];
	public $isjcamp="";
	public $technique="Unknown";
	public $asdftype="";
	public $params=[];
	public $bruker=[];
    public $nist=[];
    public $ldrs=[];
	public $errors=[];
	private $asdftypes=['FIX','PAC','SQZ','DIF','DIFDUP'];

    /**
     * Class Constructor
     * @param array|bool|int|string $file
     */
	public function __construct($file)
	{
        parent::__construct($file);
		$this->file=$file;
	}

    /**
     * Convert JCAMP file to array or XML
     * @param $file
     * @param string $format
     * @return array|mixed
     */
    public function convert($file,$format="array")
    {
        // $file must be as array not string
        $this->file=$file;
		$this->clean();
		$this->uncomment();
		$this->ldrs();
        $this->validate();
        $this->standardize();
        $this->decompress();
        if($format=="xml") {
            return $this->makexml();
        } else {
			return $this->getAll();
        }
    }

	// Getters

    /**
     * Get Errors
     */
	public function getErrors() { return $this->errors; }

    /**
     * Get Ldrs
     */
    public function getLdrs() 	{ return $this->ldrs; }

    /**
     * Get Data
     */
    public function getData() 	{ return $this->ldrs['DATA']; }

	/**
	 * Get all data
	 * ldrs, data, errors, params, bruker
	 */
	public function getAll() {
		$data=$this->ldrs;
		$data['PARAMS']=$this->params;
		$data['BRUKER']=$this->bruker;
        $data['NIST']=$this->nist;
        $data['COMMENTS']=$this->comments;
		$data['ERRORS']=$this->errors;
		return $data;
	}

	// Methods

	/**
     * Cleanup JCAMP file
     */
	public function clean()
	{
		$output=[];
		foreach($this->file as $line)
		{
			$line=trim($line);  // Removes whitespace from the beginning and end of the line
			for($y=0;$y<strlen($line);$y++)
			{
				// Checks each character for being part of the ASCII set
				if(ord($line[$y])<32||ord($line[$y])>126)
				{
					if($line[$y]=="\t"):		$line[$y]=" ";
					else:						$line[$y]="";
					endif;
				}
			}
			// Check for spaces, hyphens, and slashes in the names of the LDRs
			// This means I am deliberately making JCAMP-DX become JCAMPDX
			if(stristr($line," ") && substr($line,0,2)=="##")
			{
				list($ldr,$value)=explode("=",$line);
				$clean=[" ","-","/","_","\\"];
				$line=str_replace($clean,"",$ldr)."=".trim($value);
			}
			// Remove empty lines
			if($line!="")	{ $output[]=$line; }
		}
		$this->file=$output;
		return $output;
	}

	/**
     * Remove/filter out comments
     */
	public function uncomment()
	{
		$output=[];
		$comments=['freeform'=>'','in_data'=>'','reference'=>''];
		foreach($this->file as $line)
		{
			if(substr($line,0,2)=="$$") {
				// If the $$ is at the start of a line save the comment and dont leave it in the file
				$comments['freeform'].=trim(substr(trim($line),2)).";";
			} elseif(stristr($line,"$$") && substr($line,0,2)=="##") {
				// Remove comment strings from lines
				list($line,$comment)=explode("$$",$line,2);
				list($param,)=explode("=",$line);
				$comments['ldr_'.strtolower(substr(trim($param),2))]=trim($comment);
				$output[]=trim($line);
			} elseif(stristr($line,"$$")) {
				// Remove comment strings from lines
				list($line,$comment)=explode("$$",$line,2);
				$comments['in_data'].=trim($comment);
				$output[]=trim($line);
			} elseif(stristr($line,"##$")&&(stristr($line,"NIST"))) {
				// Get the data format
				$comment=str_replace("##$","",$line);
				if($comments['in_data']=="") {
                    $comments['in_data'].=trim($comment);
                } else {
                    $comments['in_data'].=", ".trim($comment);
                }
            } elseif(stristr($line,'##$REF')) {
                // Get the reference
                $ref=str_replace('##$REF',"",trim($line));
                list($field,$value)=explode("=",$ref);
                if($field=='AUTHOR') { $field="AUTHORS"; }
                if($field=='DATE') { $field="YEAR"; }
                if($field=='PAGE') {
                    $field="STARTPAGE";
                    if(stristr($value,"-")) {
                        list($value,$e)=explode("-",$value);
                        $diff=strlen($e)!=strlen($value);
                        if($diff>0) {
                            $e=substr($value,1,$diff).$e;
                        }
                        $comments['reference']['endpage']=$e;
                    }
                }
                $comments['reference'][strtolower($field)]=$value;
            } else {
				$output[]=$line;
			}
		}
		$this->file=$output;
        if($comments['freeform']=="") { unset($comments['freeform']); }
        if($comments['in_data']=="") { unset($comments['in_data']); }
        if($comments['reference']=="") { unset($comments['reference']); }
        $this->comments=$comments;
		return $comments;
	}

	/**
     * Take the array and pull out the $ldrs as values (also the $params for NMR and $bruker settings)
     */
	public function ldrs()
	{
		$prev="";$page=0;
		$ldrs=$params=$bruker=$nist=$errors=[];
		foreach($this->file as $key=>$line) {
			// Types of lines in the file
			// "##LDR="      - typical LDR line with ##KEY=VALUE format
			// "##$ASDFFORM" - Ascii Squeezed Difference Format type
			// "##$"         - NMR parameters (Bruker)
			// "##."         - NMR, MS, IMS, Chrom(Draft) datatype parameters
			// ""            - continuation lines with no ## before them -> must be concatenated with previous LDR
			if(substr($line,0,3)=="##$") {
				// Find the data format
				if(stristr($line,"ASDFFORM")) {
					// Get the data format
					list(,$type)=explode("=",$line);
					$type=strtoupper(trim($type));
					if(in_array($type,$this->asdftypes)):	$this->asdftype=$type;
					else:									$errors['E05']="Unknown data format: ".$type; // Defaults to FIX
					endif;
				} elseif(stristr($line,"URL")) {
					// Get the url
					list(,$url)=explode("=",$line);
					$ldrs['URL']=$url;
					// Set the prev LDR for the next iteration
					$prev='URL';
                } elseif(stristr($line,"NIST")) {
                    // Processing for NIST parameters here -> $this->nist
                    list($ldr,$value)=explode("=",$line);
                    $ldr=str_replace("NIST","",$ldr);
                    $nist[$ldr]=trim($value);
                    // Set the prev LDR for the next iteration
                    $prev=$ldr;
                } else {
					// Processing for Bruker parameters here -> $this->bruker
					list($ldr,$value)=explode("=",$line);
					$ldr=strtoupper(substr($ldr,2));
					$bruker[substr($ldr,1)]=trim($value);
					// Set the prev LDR for the next iteration
					$prev=$ldr;
				}
			} elseif(substr($line,0,3)=="##.") {
				// NMR, MS, EMR, Chrom(draft) parameters -> $this->params
				list($ldr,$value)=explode("=",$line);
                $value=trim($value); // Remove extra spaces
                $ldr=strtoupper(substr($ldr,2));
				$params[substr($ldr,1)]=trim($value);
				// Set the prev LDR for the next iteration
				$prev=$ldr;
			} elseif(substr($line,0,2)=="##") {
				// Find the LDRs
				list($ldr,$value)=explode("=",$line);
                $value=trim($value); // Remove extra spaces
				$ldr=strtoupper(substr($ldr,2));
				$ldr=str_replace("-","",$ldr); // Remove - from JCAMP-DX
				$ldr=str_replace(" ","",$ldr); // Remove space
				if($prev=="PAGE") {
					// This is a part of the page information for a multiple page series
					if($ldr=="NPOINTS")		{ $ldrs['DATA'][$page]['npoints']=$value; }
					if($ldr=="DATATABLE")	{ $ldrs['DATA'][$page]['type']=$value; }
				} elseif($ldr=="XYDATA"||$ldr=="XYPOINTS"||$ldr=="PEAKTABLE"||$ldr=="PAGE"||$ldr=="PEAKASSIGNMENTS") {
					$page++;
					$ldrs[$ldr]=trim($value);
					if(!isset($ldrs['DATA'])) { $ldrs['DATA']=[]; }
					if($ldr=="PAGE"):	$ldrs['DATA'][$page]=["type"=>$ldr,"format"=>$value,"asdftype"=>$this->asdftype,"time"=>"","npoints"=>"","raw"=>""];
					else:				$ldrs['DATA'][$page]=["type"=>$ldr,"format"=>$value,"asdftype"=>$this->asdftype,"raw"=>""];
					endif;
					// Set the prev LDR for the next iteration
					$prev=$ldr;
				} else {
					$ldrs[$ldr]=trim($value);
					// Set the prev LDR for the next iteration
					$prev=$ldr;
				}
			} else {
				// This is a continuation line therefore check what the previous LDR was to know what to do
				$ldrarray=["TITLE","ORIGIN","OWNER","SPECTROMETERDATASYSTEM","AUDITTRAIL","INSTRUMENTPARAMETERS","SAMPLINGPROCEDURE","NAMES","MASFREQUENCY"];
				if(in_array($prev,$ldrarray)):		$ldrs[$prev].=" ".$line;
				elseif($prev[0]=="$"):				$bruker[substr($prev,1)].=$line;
				elseif($prev[0]=="."):				$params[substr($prev,1)].=$line;
				else:								$ldrs['DATA'][$page]['raw'][]=$line;
				endif;
			}
			if(isset($ldrs['END'])) { break; } // ignore anything after the END line
		}
		// Check for important LDRs
		if(isset($ldrs['DATACLASS'])&&$ldrs['DATACLASS']=="XYDATA") {
			if($this->asdftype=="")			{ $errors['E12']="No data format in file (defaulting to FIX)";$this->asdftype="FIX"; }
			if(!isset($ldrs['NPOINTS']))	{ $errors['E30']="NPOINTS not defined in file (calculate using data)"; }
			if(!isset($ldrs['FIRSTX']))		{ $errors['E31']="FIRSTX not defined in file (calculate using data)"; }
			if(!isset($ldrs['LASTX']))		{ $errors['E32']="LASTX not defined in file (calculate using data)"; }
			if(!isset($ldrs['DELTAX']))		{ $errors['E33']="DELTAX not defined in file (calculate using data)"; }
			if(!isset($ldrs['FIRSTY']))		{ $errors['E34']="FIRSTY not defined in file (calculate using data)"; }
			if(!isset($ldrs['XFACTOR']))	{ $errors['E35']="XFACTOR not defined in file (calculate using FIRSTX and first X data point)"; }
			if(!isset($ldrs['YFACTOR']))	{ $errors['E36']="YFACTOR not defined in file (calculate using FIRSTY and first Y data point)"; }
			// Clarify datatype for FACTORS
			$xf=round($ldrs['XFACTOR']);$yf=round($ldrs['YFACTOR']);
			if(($ldrs['XFACTOR']-$xf)==0) { $ldrs['XFACTOR']=round($ldrs['XFACTOR']); }
			if(($ldrs['YFACTOR']-$yf)==0) { $ldrs['YFACTOR']=round($ldrs['YFACTOR']); }
		} elseif(isset($ldrs['DATACLASS'])&&$ldrs['DATACLASS']=="NTUPLES") {
			if($this->asdftype=="")			{ $errors['E12']="No data format in file (defaulting to FIX)";$this->asdftype="FIX"; }
			// TODO: Need to complete this
		} elseif(isset($ldrs['DATACLASS'])&&$ldrs['DATACLASS']=="(X++(Y..Y))") {
            if($this->asdftype=="")			{ $errors['E12']="No data format in file (defaulting to FIX)";$this->asdftype="FIX"; }
            // TODO: Need to complete this
        }
        // Convert TIME and DATE to DATETIME
        // Detect data format (may contain time)
        $y=$m=$d=$h=$n=$s=0;
        if(isset($ldrs['TIME'])&&$ldrs['TIME']!="") {
            list($h,$n,$s)=explode(":",$ldrs['TIME']);
        } else {
            $errors['L01']="No time given";
        }
        if(isset($ldrs['DATE'])&&$ldrs['DATE']!="") {
            $ldrs['DATE']=preg_replace("/ +/"," ",$ldrs['DATE']);
            if(preg_match("/^([0-9]{1,2}) ([a-zA-Z]{3}) ([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]{2})?$/",$ldrs['DATE'],$p)) {
                $d=$p[1];$m=$p[2];$y=$p[3];$h=$p[4];$n=$p[5];$s=$p[6];
            } elseif(preg_match("%^([0-9]{1,2})/([0-9]{2})/([0-9]{2}) ([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]{2})?$%",$ldrs['DATE'],$p)) {
				$d=$p[1];$m=$p[2];$y=$p[3];$h=$p[4];$n=$p[5];$s=$p[6];
			}  elseif (preg_match("/^([a-zA-Z]{3}) ([0-9]{1,2}) ([0-9]{4}) ([0-9]{2}):([0-9]{2}):([0-9]{2})$/",$ldrs['DATE'],$p)) {
                $m=$p[1];$d=$p[2];$y=$p[3];$h=$p[4];$n=$p[5];$s=$p[6];
            } elseif (preg_match("/^([0-9]{1,2}) ([a-zA-Z]{3}) ([0-9]{4})$/",$ldrs['DATE'],$p)) {
                $d=$p[1];$m=$p[2];$y=$p[3];
            } elseif (preg_match("/^([a-zA-Z]{3}) ([0-9]{1,2}) ([0-9]{4})$/",$ldrs['DATE'],$p)) {
                $m=$p[1];$d=$p[2];$y=$p[3];
            } else {
                list($y,$m,$d)=explode("/",$ldrs['DATE']);
            }
        } else {
            $errors['L02']="No date given";
        }
        if(isset($ldrs['DATE'])&&$ldrs['DATE']!=""&&isset($ldrs['TIME'])&&$ldrs['TIME']!="") {
            if(is_string($m)) { $m = date('m',strtotime($m)); }
            $ldrs['DATETIME']=date(DATE_ATOM,mktime($h,$n,$s,$m,$d,$y));
            unset($ldrs['DATE']);unset($ldrs['TIME']);
        } else {
            $ldrs['DATETIME']=date(DATE_ATOM); // Default
        }
        // Save info
		$this->ldrs=$ldrs;
		if(!empty($params))	{ $this->params=$params; }
		if(!empty($bruker))	{ $this->bruker=$bruker; }
        if(!empty($bruker))	{ $this->nist=$nist; }
        if(!empty($errors))	{ $this->errors['LDRS']=$errors; }
		return $ldrs;
	}

	/**
     * Checks to make sure this is a JCAMP file
     */
	public function validate()
	{
		// Is this a JCAMP file? Use appropriate ldrs to determine this
		$ldrs=$this->ldrs;
		$errors=[];
		if(isset($ldrs['JCAMPDX'])&&!isset($ldrs['DATATYPE']))
		{
			// Looks like JCAMP file but no DATATYPE is set - try and work out what type it is
			if(!isset($ldrs['TITLE']))					{ $ldrs['TITLE']=""; }
			if(!isset($ldrs['ORIGIN']))					{ $ldrs['ORIGIN']=""; }
			if(!isset($ldrs['SPECTROMETERDATASYSTEM']))	{ $ldrs['SPECTROMETERDATASYSTEM']=""; }
			if(!isset($ldrs['INSTRUMENTPARAMETERS']))	{ $ldrs['INSTRUMENTPARAMETERS']=""; }
			if(!isset($ldrs['XUNITS']))					{ $ldrs['XUNITS']=""; }
			if(!isset($ldrs['YUNITS']))					{ $ldrs['YUNITS']=""; }
			if(!isset($ldrs['XLABEL']))					{ $ldrs['XLABEL']=""; }
			if(!isset($ldrs['YLABEL']))					{ $ldrs['YLABEL']=""; }
			$s=$ldrs['TITLE']." ".$ldrs['ORIGIN']." ".$ldrs['SPECTROMETERDATASYSTEM']." ".$ldrs['INSTRUMENTPARAMETERS'];
			if(stristr($s,"UV")||stristr($s,"Vis")||stristr($s,"Spectrophotometer")||stristr($ldrs['XLABEL'],"wavelength")||stristr($ldrs['XUNITS'],"nanometer")||stristr($ldrs['XUNITS'],"nm"))
			{
				$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.uvvis');
				$errors['E01']="Looks like a JCAMP file but LDR(##DATATYPE) not found (interpretted as UV/Vis)";
			}
			elseif(stristr($s,"FTIR")||stristr($s,"FT-IR")||stristr($s,"Infrared")||stristr($ldrs['XLABEL'],"wavenumber")||stristr($ldrs['XUNITS'],"1/cm")||stristr($ldrs['XUNITS'],"cm-1")||stristr($ldrs['YUNITS'],"trans")||stristr($ldrs['YUNITS'],"%T"))
			{
				$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.ir');
				$errors['E01']="Looks like a JCAMP file but LDR(##DATATYPE) not found (interpretted as IR)";
			}
			elseif(stristr($s,"MASS")||stristr($ldrs['XUNITS'],"m/z"))
			{
				$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.mass');
				$errors['E01']="Looks like a JCAMP file but LDR(##DATATYPE) not found (interpretted as MS)";
			}
			elseif(stristr($s,"NMR")||stristr($s,"NUCLEAR")||stristr($ldrs['XUNITS'],"HERTZ")||stristr($ldrs['XUNITS'],"HZ")||stristr($ldrs['XUNITS'],"ppm"))
			{
				$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.nmr');
				$errors['E01']="Looks like a JCAMP file but LDR(##DATATYPE) not found (interpretted as NMR)";
			}
			else
			{
				$errors['E01']="Looks like a JCAMP file but LDR(##DATATYPE) not found (and could not interpret DATATYPE from file)";
			}
		}
		if(!isset($ldrs['DATATYPE'])&&!isset($ldrs['JCAMPDX'])&&!isset($ldrs['END']))
		{
			$this->isjcamp="no";
			$errors['E03']="Not a JCAMP file (no DATATYPE, JCAMPDX, END)";
		}
		elseif(!isset($ldrs['TITLE'])||!isset($ldrs['OWNER'])||!isset($ldrs['ORIGIN']))
		{
			// Check here for empty data fields
			$this->isjcamp="incomplete";
			$errors['EO4']="Incomplete JCAMP file";
			if(!isset($ldrs['TITLE'])) 	{ $errors['E13']="LDR(##TITLE) not found"; 	$ldrs['TITLE']="**TITLE not found in JCAMP file**"; }
			if(!isset($ldrs['OWNER'])) 	{ $errors['E14']="LDR(##OWNER) not found"; 	$ldrs['OWNER']="**OWNER not found in JCAMP file**"; }
			if(!isset($ldrs['ORIGIN'])) { $errors['E15']="LDR(##ORIGIN) not found"; 	$ldrs['ORIGIN']="**ORIGIN not found in JCAMP file**"; }
		}
		else
		{
			$this->isjcamp="yes";
		}
		// Save any errors
		if(!empty($errors)) { $this->errors['VALIDATE']=$errors; }
		// Update ldrs
		$this->ldrs=$ldrs;
		return $this->errors;
	}

	/**
     * Check for all needed metadata
     */
	public function standardize()
	{
		$ldrs=$this->ldrs;
		$errors=[];
		$dtypes=['uvvis'=>'UV/VIS SPECTRUM','ir'=>'IR SPECTRUM','ms'=>'MASS SPECTRUM','nmr'=>'NMR SPECTRUM','flow'=>'FLOW ANALYSIS'];
		// Run error checks for each of the techniques and set defualt values if appropriate
		if($ldrs['DATATYPE']=="UV/VIS SPECTRUM"||$ldrs['DATATYPE']=="UV-VIS SPECTRUM"||$ldrs['DATATYPE']=="UV/VISIBLE SPECTRUM"||$ldrs['DATATYPE']=="UV-VISIBLE SPECTRUM")
		{
			// Standardize the DATATYPE string
			//$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.uvvis'); // This should work put config needs to be fixed
			$ldrs['DATATYPE']=$dtypes['uvvis'];
			// Other LDRs that must be present UV/Vis spectra are XUNITS,YUNITS,FIRSTX,LASTX,XFACTOR,YFACTOR,XYDATA,NPOINTS,FIRSTY
			if(!isset($ldrs['XUNITS'])||!isset($ldrs['YUNITS'])||!isset($ldrs['FIRSTX'])||!isset($ldrs['LASTX'])||!isset($ldrs['XFACTOR'])||!isset($ldrs['YFACTOR'])||(!isset($ldrs['XYDATA'])&&!isset($ldrs['XYPOINTS'])&&!isset($ldrs['PEAKTABLE']))||!isset($ldrs['NPOINTS'])||!isset($ldrs['FIRSTY']))
			{
				$errors['E02']="Incomplete UV/VIS JCAMP-DX file";
				if(!isset($ldrs['XUNITS'])) 	{ $errors['E15']="LDR(##XUNITS) not found (default set to nm)"; 			$ldrs['XUNITS']="nm"; }
				if(!isset($ldrs['YUNITS'])) 	{ $errors['E16']="LDR(##YUNITS) not found (default set to Absorbance)"; 	$ldrs['YUNITS']="Absorbance"; }
				if(!isset($ldrs['XFACTOR'])) 	{ $errors['E17']="LDR(##XFACTOR) not found (default set to 1)"; 			$ldrs['XFACTOR']="1"; }
				if(!isset($ldrs['YFACTOR'])) 	{ $errors['E18']="LDR(##YFACTOR) not found (default set to 1)"; 			$ldrs['YFACTOR']="1"; }
				if(!isset($ldrs['FIRSTX'])) 	{ $errors['E19']="LDR(##FIRSTX) not found (see independent series set)"; }
				if(!isset($ldrs['LASTX'])) 		{ $errors['E20']="LDR(##LASTX) not found (see independent series set)"; }
				if(!isset($ldrs['FIRSTY'])) 	{ $errors['E21']="LDR(##FIRSTY) not found (see dependent series set)"; }
			}
			// Standardize units
			if(stristr($ldrs['XUNITS'],"nanometer")) { $ldrs['XUNITS']="nm"; }
			if(in_array($ldrs['YUNITS'],["A","AU","au","AUFS","aufs","Abs","abs","OD","Optical Density"])) { $ldrs['YUNITS']="Absorbance"; }
		}
		elseif($ldrs['DATATYPE']=="FLOW ANALYSIS"||$ldrs['DATATYPE']=="FLOW INJECTION ANALYSIS")
		{
			// Standardize the DATATYPE string
			//$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.flow'); // This should work put config needs to be fixed
			$ldrs['DATATYPE']=$dtypes['flow'];
			// Other LDRs that must be present for UV/Vis spectra are XUNITS,YUNITS,FIRSTX,LASTX,XFACTOR,YFACTOR,XYDATA,NPOINTS,FIRSTY
			if(!isset($ldrs['XUNITS'])||!isset($ldrs['YUNITS'])||!isset($ldrs['FIRSTX'])||!isset($ldrs['LASTX'])||!isset($ldrs['XFACTOR'])||!isset($ldrs['YFACTOR'])||(!isset($ldrs['XYDATA'])&&!isset($ldrs['PEAKTABLE']))||!isset($ldrs['NPOINTS'])||!isset($ldrs['FIRSTY']))
			{
				$errors['E02']="Incomplete FIA/SIA JCAMP-DX file";
				if(!isset($ldrs['XUNITS'])) 	{ $errors['E15']="LDR(##XUNITS) not found (default set to sec)"; 			$ldrs['XUNITS']="sec"; }
				if(!isset($ldrs['YUNITS'])) 	{ $errors['E16']="LDR(##YUNITS) not found (default set to Absorbance)"; 	$ldrs['YUNITS']="Absorbance"; }
				if(!isset($ldrs['XFACTOR'])) 	{ $errors['E17']="LDR(##XFACTOR) not found (default set to 1)"; 			$ldrs['XFACTOR']="1"; }
				if(!isset($ldrs['YFACTOR'])) 	{ $errors['E18']="LDR(##YFACTOR) not found (default set to 1)"; 			$ldrs['YFACTOR']="1"; }
				if(!isset($ldrs['FIRSTX'])) 	{ $errors['E19']="LDR(##FIRSTX) not found (see independent series set)"; }
				if(!isset($ldrs['LASTX'])) 		{ $errors['E20']="LDR(##LASTX) not found (see independent series set)"; }
				if(!isset($ldrs['FIRSTY'])) 	{ $errors['E21']="LDR(##FIRSTY) not found (see dependent series set)"; }
			}
		}
		elseif($ldrs['DATATYPE']=="INFRARED SPECTRUM"||$ldrs['DATATYPE']=="IR SPECTRUM"||$ldrs['DATATYPE']=="FT-IR SPECTRUM"||$ldrs['DATATYPE']=="FTIR SPECTRUM"||$ldrs['DATATYPE']=="FT-IR"||$ldrs['DATATYPE']=="FTIR")
		{
			// Standardize the DATATYPE string
			//$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.ir'); // This should work put config needs to be fixed
			$ldrs['DATATYPE']=$dtypes['ir'];
			// Other LDRs that must be present for IR or UV/Vis spectra are XUNITS,YUNITS,FIRSTX,LASTX,XFACTOR,YFACTOR,XYDATA,NPOINTS,FIRSTY
			if(!isset($ldrs['XUNITS'])||!isset($ldrs['YUNITS'])||!isset($ldrs['FIRSTX'])||!isset($ldrs['LASTX'])||!isset($ldrs['XFACTOR'])||!isset($ldrs['YFACTOR'])||(!isset($ldrs['XYDATA'])&&!isset($ldrs['PEAKTABLE']))||!isset($ldrs['NPOINTS'])||!isset($ldrs['FIRSTY'])|!isset($ldrs['RESOLUTION']))
			{
				$errors['E02']="Incomplete IR JCAMP-DX file";
				if(!isset($ldrs['XUNITS'])) 	{ $errors['E15']="LDR(##XUNITS) not found (default set to 1/cm);"; 			$ldrs['XUNITS']="1/cm"; }
				if(!isset($ldrs['YUNITS'])) 	{ $errors['E16']="LDR(##YUNITS) not found (default set to %T);"; 			$ldrs['YUNITS']="%TRANSMITTANCE"; }
				if(!isset($ldrs['XFACTOR'])) 	{ $errors['E17']="LDR(##XFACTOR) not found (default set to 1);"; 			$ldrs['XFACTOR']="1"; }
				if(!isset($ldrs['YFACTOR'])) 	{ $errors['E18']="LDR(##YFACTOR) not found (default set to 1);"; 			$ldrs['YFACTOR']="1"; }
				if(!isset($ldrs['FIRSTX'])) 	{ $errors['E19']="LDR(##FIRSTX) not found (see independent series set);"; }
				if(!isset($ldrs['LASTX'])) 		{ $errors['E20']="LDR(##LASTX) not found (see independent series set);"; }
				if(!isset($ldrs['FIRSTY'])) 	{ $errors['E21']="LDR(##FIRSTY) not found (see dependent series set);"; }
				if(!isset($ldrs['RESOLUTION']))	{ $errors['E22']="LDR(##RESOLUTION) not found (default set to NaN);";		$ldrs['RESOLUTION']="NaN"; }
			}
		}
		elseif($ldrs['DATATYPE']=="MASS SPECTRUM"||$ldrs['DATATYPE']=="MASSSPECTRUM"||$ldrs['DATATYPE']=="MASS SPEC"||$ldrs['DATATYPE']=="MS")
		{
			// Standardize the DATATYPE string
			//$ldrs['DATATYPE']=Configure::read('jcamp.datatypes.mass'); // This should work put config needs to be fixed
			$ldrs['DATATYPE']=$dtypes['ms'];
			// Other LDRs that must be present for MS spectra are XUNITS,YUNITS,FIRSTX,LASTX,XFACTOR,YFACTOR,XYDATA,NPOINTS,FIRSTY,DATACLASS
			if(!isset($ldrs['XUNITS'])||!isset($ldrs['YUNITS'])||!isset($ldrs['FIRSTX'])||!isset($ldrs['LASTX'])||!isset($ldrs['XFACTOR'])||!isset($ldrs['YFACTOR'])||!isset($ldrs['DATACLASS'])||!isset($ldrs['NPOINTS'])||!isset($ldrs['FIRSTY']))
			{
				$errors['E02']="Incomplete MS JCAMP-DX file";
                if(!isset($ldrs['XUNITS'])) 	{ $errors['E15']="LDR(##XUNITS) not found (default set to M/Z)"; 				$ldrs['XUNITS']="M/Z"; }
				if(!isset($ldrs['YUNITS'])) 	{ $errors['E16']="LDR(##YUNITS) not found (default set to RELATIVE ABUNDANCE)";	$ldrs['YUNITS']="RELATIVE ABUNDANCE"; }
                if(!isset($ldrs['FIRSTX'])) 	{ $errors['E19']="LDR(##FIRSTX) not found (see independent series set)"; }
                if(!isset($ldrs['LASTX'])) 		{ $errors['E20']="LDR(##LASTX) not found (see independent series set)"; }
                if(!isset($ldrs['XFACTOR'])) 	{ $errors['E17']="LDR(##XFACTOR) not found (default set to 1)"; 				$ldrs['XFACTOR']="1"; }
				if(!isset($ldrs['YFACTOR'])) 	{ $errors['E18']="LDR(##YFACTOR) not found (default set to 1)"; 				$ldrs['YFACTOR']="1"; }
				if(!isset($ldrs['FIRSTY'])) 	{ $errors['E21']="LDR(##FIRSTY) not found (see dependent series set)"; }
                if(!isset($ldrs['DATACLASS'])) 	{ $errors['E15']="LDR(##DATA CLASS) not found (default set to PEAK TABLE)";     $ldrs['DATA CLASS']="PEAK TABLE"; }
			}
            if($ldrs['DATACLASS']=='PEAK TABLE'&&!isset($ldrs['PEAKTABLE'])) {
                $errors['E02']="Incomplete MS JCAMP-DX file";
                $errors['E22']="LDR(##PEAK TABLE) not found";
            }
            if($ldrs['DATACLASS']=='XYDATA'&&!isset($ldrs['XYDATA'])) {
                $errors['E02']="Incomplete MS JCAMP-DX file";
                $errors['E23']="LDR(##XYDATA) not found";
            }
            if($ldrs['DATACLASS']=='NTUPLES'&&!isset($ldrs['NTUPLES'])) {
                $errors['E02']="Incomplete MS JCAMP-DX file";
                $errors['E24']="LDR(##NTUPLES) not found";
            }
		}
		elseif($ldrs['DATATYPE']=="NMR SPECTRUM"||$ldrs['DATATYPE']=="NMR")
		{
			// Standardize the DATATYPE string
			// $ldrs['DATATYPE']=Configure::read('jcamp.datatypes.nmr'); // This should work put config needs to be fixed
			$ldrs['DATATYPE']=$dtypes['nmr'];
			// Other LDRs that must be present for IR or UV/Vis spectra are XUNITS,YUNITS,FIRSTX,LASTX,XFACTOR,YFACTOR,XYDATA,NPOINTS,FIRSTY
			if(!isset($ldrs['XUNITS'])||!isset($ldrs['YUNITS'])||!isset($ldrs['FIRSTX'])||!isset($ldrs['LASTX'])||!isset($ldrs['XFACTOR'])||!isset($ldrs['YFACTOR'])||(!isset($ldrs['XYDATA'])&&!isset($ldrs['PEAKTABLE']))||!isset($ldrs['NPOINTS'])||!isset($ldrs['FIRSTY']))
			{
				$errors['E02']="Incomplete NMR JCAMP-DX file because:";
				if(!isset($ldrs['XUNITS'])) 	{ $errors['E15']="LDR(##XUNITS) not found (default set to Hz);"; 				$ldrs['XUNITS']="Hz"; }
				if(!isset($ldrs['YUNITS'])) 	{ $errors['E16']="LDR(##YUNITS) not found (default set to ARBITRARY UNITS);";	$ldrs['YUNITS']="ARBITRARY UNITS"; }
				if(!isset($ldrs['XFACTOR'])) 	{ $errors['E17']="LDR(##XFACTOR) not found (default set to 1);"; 				$ldrs['XFACTOR']="1"; }
				if(!isset($ldrs['YFACTOR']))	{ $errors['E18']="LDR(##YFACTOR) not found (default set to 1);"; 				$ldrs['YFACTOR']="1"; }
				if(!isset($ldrs['FIRSTX'])) 	{ $errors['E19']="LDR(##FIRSTX) not found (see independent series set);"; }
				if(!isset($ldrs['LASTX'])) 		{ $errors['E20']="LDR(##LASTX) not found (see independent series set);"; }
				if(!isset($ldrs['FIRSTY'])) 	{ $errors['E21']="LDR(##FIRSTY) not found (see dependent series set);"; }
			}
		}
		else
		{
			// Anything to go here?
		}
		// Save any errors
		if(!empty($errors)) { $this->errors['STANDARDIZE']=$errors; }
		// Update ldrs
		$this->ldrs=$ldrs;
		return $this->errors;
	}

	/**
     * Uncompress data
     */
	public function decompress()
	{
		// Work out the compression of each set of data in the file
		$set=1;
		$errors=[];
		$ldrs=$this->ldrs;
        if(isset($ldrs['XYDATA'])) {
            foreach($this->ldrs['DATA'] as $dataset)
            {
                // TODO: make this selectable for different types of data constructs
                // First verify compression is what is indicated
                // Concat data string to work out compression
                $all=$asdftype=$temp="";
                $raw=$dataset['raw'];
                $all_ys=$data=[];
                $avgdeltax="no";
                $actualfirstx=$actualfirsty=0;
                foreach($raw as $line) { $all.=$line; }
                // The different compression formats are independent
                foreach(['s','t','u','v','w','x','y','z'] as $char)	 { if(stristr($all,$char))	        { $asdftype.="DUP";break; } }
                foreach(['j','k','l','m','n','o','p','q','r'] as $char) { if(stristr($all,$char))	    { $asdftype.="DIF";break; } }
                foreach(['@','a','b','c','d','e','f','g','h','i'] as $char) { if(stristr($all,$char))	{ $asdftype.="SQZ";break; } }
                if((stristr($all,"+")||stristr($all,"-"))&&(!stristr($all," +")||!stristr($all," -")))  { $asdftype.="PAC"; }
                if(stristr($all," ")||stristr($all,","))												{ $asdftype.="FIX"; }
                if($asdftype=="") {
					$errors['E07']="Cannot identify compression of data from data (dataset ".$set.")";
				}
				if($asdftype!="FIX"&&$asdftype!="") {
					$errors['E08']="(Set ".$set.") NOTE: Compression format detected as ".$asdftype;
				}
				$dataset['asdftype']=$asdftype;$ldrs['DATA'][$set]['asdftype']=$asdftype;
                // Set up variables
                $prevx=round($ldrs['FIRSTX']/$ldrs['XFACTOR']);  // This is the unscaled value
                $prevy=round($ldrs['FIRSTY']/$ldrs['YFACTOR']);  // This is the unscaled value
                $prevycount=0;
                // Decompress - work on one line at a time
                for($line=0;$line<count($raw);$line++) {
                    // Clean up the dataset of any unwanted characters
                    //echo "LINE: ".$line."<br>";
                    $temp=$temp2=$raw[$line];
                    //if(stristr($temp2,"?")) { echo "LINE: ".$temp."<br>"; }
                    $temp=str_replace(["$","\t","\r","\n",""],"",$temp);
                    if(stristr($dataset['asdftype'],"SQZ")||stristr($dataset['asdftype'],"DIF")||stristr($dataset['asdftype'],"DUP")) { $temp=str_replace(" ","",$temp); }
                    //echo $temp."<br>";
                    // Unsqueeze the line
                    if(stristr($dataset['asdftype'],"SQZ")) { $temp=$this->jcampsqz($temp); }
                    //echo $temp."<br>";
                    //if(stristr($temp2,"?")) { echo $temp."<br>"; }
                    // Replace difference digits
                    if(stristr($dataset['asdftype'],"DIF")) { $temp=$this->jcampdif($temp); }
                    //echo $temp."<br>";
                    //if(stristr($temp2,"?")) { echo $temp."<br>"; }
                    // Convert duplicates
                    if(stristr($dataset['asdftype'],"DUP")) { $temp=$this->jcampdup($temp); }
					//if(stristr($temp2,"?")) { echo $temp."<br>"; }
                    // Add spaces to PAC signs
                    if(stristr($dataset['asdftype'],"PAC")) { $temp=$this->jcamppac($temp); }
                    //if(stristr($temp2,"?")) { echo $temp."<br>"; }
                    // Remove ? and extra space from FIX format data
                    if(stristr($dataset['asdftype'],"FIX")) { $temp=$this->jcampfix($temp); }
					//echo $temp."<br>";
					//if(stristr($temp2,"?")) { echo $temp."<br>"; }
                    // Split off X point
                    list($xpart,$temp)=explode(" ",$temp,2);
                    // Clean up Y string
                    $temp=trim($temp);
                    // Scale the X value if present
                    if(isset($ldrs['XFACTOR'])) { $xpart=$xpart*$ldrs['XFACTOR']; }
                    //echo 'X: '.$xpart.' Ys: '.$temp."<br>";
                    // Change differences into real values
                    if(stristr($dataset['asdftype'],"DIF")) { $temp=$this->jcampadd($temp); }
                    //echo "<pre>".$line.") ".$temp." (".count(explode(" ",$temp)).")</pre><br>";
                    //if(stristr($temp2,"?")) { echo $temp."<br><br>"; }
                    // Generate array of y values
                    $yarray=explode(" ",$temp);
                    //echo "<pre>";print_r($yarray);echo "</pre>";
                    if($line==0) { $actualfirstx=$xpart; $actualfirsty=$yarray[0]; }
                    if(stristr($dataset['asdftype'],"DIF"))
                    {
                        // Check that the last value of the previous line matches up to the first of this line
                        if($yarray[0]!=$prevy)
                        {
                            if($line==0):					$errors['E09']="(Set ".$set.") FIRSTY value (".$yarray[0].") does not match first ordinate value (".$prevy.")";
                            elseif($line==count($raw)-1):	$errors['E10']="(Set ".$set.") Last ordinate value (".$yarray[0].") does not match check ordinate value (".$prevy.")";
                            else:							$errors['E11']="(Set ".$set.") Y ordinate value (".$yarray[0].") does not match check ordinate value (".$prevy.") on line ".$line;
                                //echo "LINE: ".$line." => ".$temp." (PREVY: ".$prevy.")<br>";
                            endif;
                        }
                        // Remove the last (duplicate) value of the current line and use it to error check on the first one of the next line
                        if($line<(count($raw)-1)) { $prevy=array_pop($yarray); }
                    }
                    if($line==1) {
                        $calcdeltax=($xpart-$prevx)/$prevycount;
                        if(!isset($ldrs['DELTAX'])) {
                            if(isset($ldrs['FIRSTX'])&&isset($ldrs['LASTX'])&&isset($ldrs['NPOINTS'])) {
                                $errors['E22']="(Set ".$set.") DELTAX not in original file (calculated from FIRSTX, LASTX, and NPOINTS)";
                                $ldrs['DELTAX']=($ldrs['LASTX']-$ldrs['FIRSTX'])/($ldrs['NPOINTS']-1);
                            } else {
                                $errors['E22']="(Set ".$set.") DELTAX not in original file (taken from x data points on the first two lines)";
                                $ldrs['DELTAX']=$calcdeltax;
                                $avgdeltax="yes";
                            }
                        } else {
                            $deltaxdiff=abs(($ldrs['DELTAX']-$calcdeltax)*100/$ldrs['DELTAX']);
                            if($deltaxdiff>0.01) {
                                $errors['E35']="(Set ".$set.") DELTAX does not match calculated value (taken from x data points on the first two lines)";
                                $ldrs['DELTAX']=$calcdeltax;
                                $avgdeltax="yes";
                            }
                        }
                    }
                    if($avgdeltax=="yes") {
                        // Calculate overall average deltax
                        $calcdeltax=($xpart-$prevx)/$prevycount;
                        $ldrs['DELTAX']=(($ldrs['DELTAX']*($line-1))+$calcdeltax)/$line;
                    }
                    if($line>0) {
                        // Calculate the difference between expected and actual X value ()
                        if($xpart!=0):  $diff=abs((($prevx+($prevycount*$ldrs['DELTAX'])) - $xpart)*100/$xpart);
                        else:           $diff=0;
                        endif;
                        if($diff>0.01) {
                            //debug($yarray);
                            $dp=strlen($ldrs['FIRSTX'])-(strpos($ldrs['FIRSTX'],'.')+1);
                            $errors['E35']="(Set ".$set.", line ".$line.") Mismatch in X values (missing data?) (DIFF: ".$diff.") ".sprintf("%1\$.".$dp."f",$prevx+($prevycount*$ldrs['DELTAX'])).":".sprintf("%1\$.".$dp."f",$xpart);
                            // Add to the $yarray to pad for missing numbers
                            $prevycount=count($yarray);
                            $yarray=array_pad($yarray,-1*abs(($prevx-$xpart)/$ldrs['DELTAX']),'?');
                            //echo "<pre>";print_r($yarray);echo "</pre>";
                        } else {
                            //echo "DIFF: ".$diff."<br>";
                            $prevycount=count($yarray);
                        }
                    } else {
                        $prevycount=count($yarray);
                    }
                    $prevx=$xpart;
                    // Append data points to the end of the
                    $all_ys=array_merge($all_ys,$yarray);
                }
                // Set the number of DPs to a logical value if the DELTAX was calculated from the data
                if($avgdeltax=="yes") {
                    $dp=strlen($ldrs['FIRSTX'])-strpos($ldrs['FIRSTX'],'.');
                    $ldrs['DELTAX']=sprintf("%1\$.".$dp."f",$ldrs['DELTAX']);
                }
                // Set the number of points if not set already
                if(!isset($ldrs['NPOINTS'])) {
                    $errors['E22']="(Set ".$set.") NPOINTS not in original file (taken from y data points)";
                    $ldrs['NPOINTS']=count($all_ys);
                }
                // Check the number of data points against what it said in the file
                if(count($all_ys)!=$ldrs['NPOINTS']) {
                    $errors['E12']="(Set ".$set.") NPOINTS (".$ldrs['NPOINTS'].") does not match points found (".count($all_ys).")";
                }
                // Set first data point values if not set
                if(!isset($ldrs['FIRSTX'])) {
                    $errors['E23']="(Set ".$set.") FIRSTX not in original file (taken from x data points)";
                    if(stristr($actualfirstx,".")) {
                        $dp=strlen($actualfirstx)-strpos($actualfirstx,'.')-1;
                        $ldrs['FIRSTX']= sprintf("%1\$.".$dp."f",$actualfirstx);
                    } else {
                        if(isset($ldrs['DELTAX']) && $ldrs['DELTAX'] < 1) {
                            $dp = strlen($ldrs['DELTAX']) - strpos($ldrs['DELTAX'], '.') - 1;
                            $ldrs['FIRSTX'] = sprintf("%1\$." . $dp . "f", $actualfirstx);
                        } else {
                            $ldrs['FIRSTX'] = sprintf("%1\$u", $actualfirstx);
                        }
                    }
                }
                if(!isset($ldrs['FIRSTY'])) {
                    $errors['E24']="(Set ".$set.") FIRSTY not in original file (taken from y data points)";
                    if(stristr($actualfirsty,".")) {
                        $dp=strlen($actualfirsty)-strpos($actualfirsty,'.')-1;
                        $ldrs['FIRSTY']=sprintf("%1\$.".$dp."f",$actualfirsty/$ldrs['YFACTOR']);
                    } else {
                        $ldrs['FIRSTY']=sprintf("%1\$u",$actualfirsty/$ldrs['YFACTOR']);
                    }
                }
                // Can't rely on ##DELTAX being present as it is not required in JCAMP, so calculate it here...
                if(isset($ldrs['XYDATA'])&&!isset($ldrs['DELTAX']))	{
                    $ldrs['DELTAX']=($ldrs['LASTX']-$ldrs['FIRSTX'])/($ldrs['NPOINTS']-1);
                }
                // Work out the correct way to quote X values (decimal places) based off of FIRSTX and DELTAX value
                // How many decimals in FIRSTX?
                if(stristr($ldrs['FIRSTX'],"E")):
                    list($temp,)=explode("E",strtoupper($ldrs['FIRSTX']));
                    $xdp=strlen($temp)-strpos($temp,'.')-1;
                elseif(stristr($ldrs['FIRSTX'],".")):
                    $xdp=strlen($ldrs['FIRSTX'])-strpos($ldrs['FIRSTX'],'.')-1;
                else:
                    $xdp=0;
                endif;
                // How many decimals in DELTAX?
                if(stristr($ldrs['DELTAX'],"E")):
                    list($temp,)=explode("E",strtoupper($ldrs['DELTAX']));
                    $ddp=strlen($temp)-strpos($temp,'.')-1;
                elseif(stristr($ldrs['DELTAX'],".")):
                    $ddp=strlen($ldrs['DELTAX'])-strpos($ldrs['DELTAX'],'.')-1;
                else:
                    $ddp=0;
                endif;
                // Decide the format based on values being exponentials, floats, or integers
                if(stristr($ldrs['FIRSTX'],"E")||stristr($ldrs['DELTAX'],"E")):
                    $xformat="%.".min($xdp,$ddp)."E";
                elseif(stristr($ldrs['FIRSTX'],".")&&stristr($ldrs['DELTAX'],".")):
                    $xformat="%.".min($xdp,$ddp)."f";
                elseif(!stristr($ldrs['FIRSTX'],".")&&stristr($ldrs['DELTAX'],".")):
                    $xformat="%.".$ddp."f";
                elseif(stristr($ldrs['FIRSTX'],".")&&!stristr($ldrs['DELTAX'],".")):
                    $xformat="%.".$xdp."f";
                else:
                    $xformat="%d";
                endif;
                // Work out the correct way to quote Y values (decimal places) based of of FIRSTY value
                // How many decimals (actually SF!) in FIRSTY?
                if(stristr($ldrs['FIRSTY'],"E")):
                    list($temp,)=explode("E",strtoupper($ldrs['FIRSTY']));
                    $ydp=strlen($temp)-strpos($temp,'.')-1;
                elseif(stristr($ldrs['FIRSTY'],".")):
					if($ldrs['FIRSTY']==0) {
						$ydp=strlen(str_replace(".","",$ldrs['FIRSTY']));
					} else {
						$scinot=$this->scinot($ldrs['FIRSTY']);
						$ydp=$scinot['s'];
					}
                else:
                    $ydp=1000;
                endif;
                // How many decimals in actual first datapoint?
                if(stristr($all_ys[0],"E")):
                    list($temp,)=explode("E",strtoupper($all_ys[0]));
                    $aydp=strlen($temp)-strpos($temp,'.')-1;
                elseif(stristr($all_ys[0],".")):
                    $aydp=strlen($all_ys[0])-strpos($all_ys[0],'.')-1;
                else:
                    $aydp=1000;
                endif;
                if($aydp<$ydp) { $ydp=$aydp; }
                // How many decimals in YFACTOR?
                if(stristr($ldrs['YFACTOR'],"E")):
                    list($temp,)=explode("E",strtoupper($ldrs['YFACTOR']));
                    $fdp=strlen($temp)-strpos($temp,'.')-1;
                elseif(stristr($ldrs['YFACTOR'],".")):
                    $scinot=$this->scinot($ldrs['YFACTOR']);
                    $fdp=$scinot['s'];
                else:
                    $fdp=1000; // Assumes exact integer
                endif;
				//debug($ldrs);
				//debug($ydp);debug($aydp);debug($fdp);exit;

                // Decide the format based on values being exponentials, floats, or integers
                if(stristr($ldrs['FIRSTY'],"E")||stristr($ldrs['YFACTOR'],"E")):
                    $yformat="%.".min($ydp,$fdp)."E";
                elseif(stristr($ldrs['FIRSTY'],".")||stristr($ldrs['YFACTOR'],".")):
                    $yformat="%.".min($ydp,$fdp)."f";
                    //$yformat="%.".$ydp."f"; // Forcing dp for y points to be based on FIRSTY after trailing zeros removed
                else:
                    $yformat="%d";
                endif;
                // Build the XY array
                $ldrs['DELTAX']=trim($ldrs['DELTAX'],"0"); // Remove trailing zeros from DELTAX
                for($x=0;$x<count($all_ys);$x++) {
                    if($x==0) {
                        // Check that the first data point matches up
                        $y=sprintf($yformat,($all_ys[0]*$ldrs['YFACTOR']));
                        if($y!=$ldrs['FIRSTY'])	{
                            $errors['E25']="(Set ".$set.") FIRSTY (".$ldrs['FIRSTY'].") does not match first Y point (".$y.")";
                        }
                    }
                    // sprintf used because the conversion of YFACTOR to a float causes it to have 50 decimal places which rounding won't deal with
                    if($all_ys[$x]=="NaN") {
                        $data[sprintf($xformat,($actualfirstx+($x*$ldrs['DELTAX'])))]="NaN";
                    } else {
                        $data[sprintf($xformat,($actualfirstx+($x*$ldrs['DELTAX'])))]=sprintf($yformat, ($all_ys[$x]*$ldrs['YFACTOR']));
                    }
                }
                // Check the FIRSTY consistency
                $xs=array_keys($data);
                if($ldrs['FIRSTX']!=$xs[0]) {
                    $errors['E24']="(Set ".$set.") FIRSTX (".$ldrs['FIRSTX'].") does not match actual first x point (".$xs[0].")";
                }
                // Set the max/min y values if not set already
                if(!isset($ldrs['MAXY']))		{ $errors['E26']="(Set ".$set.") MAXY not in original file (taken from y data points)"; $ldrs['MAXY']=max($data); }
                if(max($data)!=$ldrs['MAXY'])	{ $errors['E27']="(Set ".$set.") MAXY (".$ldrs['MAXY'].") does not match point found (".max($data).")"; }
                if(!isset($ldrs['MINY']))		{ $errors['E28']="(Set ".$set.") MINY not in original file (taken from y data points)"; $ldrs['MINY']=min($data); }
                if(min($data)!=$ldrs['MINY'])	{ $errors['E29']="(Set ".$set.") MINY (".$ldrs['MINY'].") does not match point found (".min($data).")"; }
                // Save data
                $ldrs['DATA'][$set]['pro']=$data;
				//debug($ldrs);
				$set++;
            }
        } elseif(isset($ldrs['PEAKTABLE'])|isset($ldrs['XYPOINTS'])) {
            foreach($ldrs['DATA'] as $dataset) {
                // Concatenate the list of peak data
                $points="";
                foreach($dataset['raw'] as $line) {
                    $points.=" ".$line;
                }
                // Clean the points string
                $points=str_replace("  "," ",$points);
                if(stristr($points,", ")) {
                    $points=str_replace(", ",",",$points);
                }
                // Split data apart
                $xys=explode(" ",trim($points));
                // Split the xy pairs
                $data=[];
                foreach($xys as $xy) {
                    list($x,$y)=explode(",",$xy);
                    $data[$x]=$y;
                }
                // Save data
                $ldrs['DATA'][$set]['pro']=$data;
                $set++;
            }
        }
		// Save errors
		if(!empty($errors)) { $this->errors['DECOMPRESS']=$errors; }
		// Update ldrs
		$this->ldrs=$ldrs;
		return $errors;
	}

	/**
     * Output xml file
	 * @param array $data
	 * @return xml
     */
	public function makexml($data=[])
	{
		$xmlstr="<?xml version='1.0' encoding='UTF-8'?><jcamp></jcamp>";
		$jxml = new SimpleXMLElement($xmlstr);
		// Record type of file
		$jxml->addChild('type','jcamp');
		if(empty($data)) {
			$data=$this->getAll();
		}
		foreach($data as $key=>$value)
		{
			$key=strtolower($key);
			if(!is_array($value))
			{
				$jxml->addChild($key,$value);
			}
			else
			{
				$temp=$jxml->addChild($key,"");
				foreach($value as $key2=>$value2)
				{
					$key2=strtolower($key2);
					if($key=="data") { $key2="set"; }
					if(!is_array($value2))
					{
						$temp->addChild(strtolower($key2),$value2);
					}
					else
					{
						$temp2=$temp->addChild(strtolower($key2),"");
						foreach($value2 as $key3=>$value3)
						{
							$key3=strtolower($key3);
							if(!is_array($value3))
							{
								$temp2->addChild($key3,$value3);
							}
							else
							{
								$temp3=$temp2->addChild($key3,"");
								foreach($value3 as $key4=>$value4)
								{
									$key4=strtolower($key4);
									if($key3=="raw") { $key4="line"; }
									if($key3=="pro") { $value4=$key4.",".$value4;$key4="xy"; }
									if(!is_array($value4))
									{
										$temp3->addChild(strtolower($key4),$value4);
									}
								}
							}
						}
					}
				}
			}
		}
		return $jxml->asXML();
	}

	// Private methods

	/**
     * Process data that has been SQZ compressed
     * @param $line
     * @param string $split
     * @return mixed
     */
	private function jcampsqz($line,$split="no")
	{
		// Conversion of chars %JKLMNOPQRjklmnopqr into difference digits
		$sqzchars=["@","A","B","C","D","E","F","G","H","I","a","b","c","d","e","f","g","h","i"];
		$str="";
		for($char=0;$char<strlen($line);$char++)
		{
			if(in_array($line[$char],$sqzchars))
			{
				if($line[$char]=="A"):		$str.=" +1";
				elseif($line[$char]=="B"):	$str.=" +2";
				elseif($line[$char]=="C"):	$str.=" +3";
				elseif($line[$char]=="D"):	$str.=" +4";
				elseif($line[$char]=="E"):	$str.=" +5";
				elseif($line[$char]=="F"):	$str.=" +6";
				elseif($line[$char]=="G"):	$str.=" +7";
				elseif($line[$char]=="H"):	$str.=" +8";
				elseif($line[$char]=="I"):	$str.=" +9";
				elseif($line[$char]=="@"):	$str.=" 0";
				elseif($line[$char]=="a"):	$str.=" -1";
				elseif($line[$char]=="b"):	$str.=" -2";
				elseif($line[$char]=="c"):	$str.=" -3";
				elseif($line[$char]=="d"):	$str.=" -4";
				elseif($line[$char]=="e"):	$str.=" -5";
				elseif($line[$char]=="f"):	$str.=" -6";
				elseif($line[$char]=="g"):	$str.=" -7";
				elseif($line[$char]=="h"):	$str.=" -8";
				elseif($line[$char]=="i"):	$str.=" -9";
				endif;
			}
			else
			{
				$str.=$line[$char];
			}
		}
		// Add spaces for any pesky ? chars
		$line=str_replace("?"," ?",$str);
		if($split=="yes"):	list($parts['xpart'],$parts['ypart'])=explode(" ",$line,2);
							return $parts;
		else:				return $line;
		endif;
	}

    /**
     * Process data that has been DIF compressed
     * @param $line
     * @return mixed
     */
	private function jcampdif($line)
	{
		// Conversion of chars %JKLMNOPQRjklmnopqr into difference digits
		$difchars=["%","J","K","L","M","N","O","P","Q","R","j","k","l","m","n","o","p","q","r"];
		for($char=0;$char<strlen($line);$char++)
		{
			if(in_array($line[$char],$difchars))
			{
				if($line[$char]=="J"):		$line=substr_replace($line," +1",$char,1);
				elseif($line[$char]=="K"):	$line=substr_replace($line," +2",$char,1);
				elseif($line[$char]=="L"):	$line=substr_replace($line," +3",$char,1);
				elseif($line[$char]=="M"):	$line=substr_replace($line," +4",$char,1);
				elseif($line[$char]=="N"):	$line=substr_replace($line," +5",$char,1);
				elseif($line[$char]=="O"):	$line=substr_replace($line," +6",$char,1);
				elseif($line[$char]=="P"):	$line=substr_replace($line," +7",$char,1);
				elseif($line[$char]=="Q"):	$line=substr_replace($line," +8",$char,1);
				elseif($line[$char]=="R"):	$line=substr_replace($line," +9",$char,1);
				elseif($line[$char]=="%"):	$line=substr_replace($line," 0",$char,1);
				elseif($line[$char]=="j"):	$line=substr_replace($line," -1",$char,1);
				elseif($line[$char]=="k"):	$line=substr_replace($line," -2",$char,1);
				elseif($line[$char]=="l"):	$line=substr_replace($line," -3",$char,1);
				elseif($line[$char]=="m"):	$line=substr_replace($line," -4",$char,1);
				elseif($line[$char]=="n"):	$line=substr_replace($line," -5",$char,1);
				elseif($line[$char]=="o"):	$line=substr_replace($line," -6",$char,1);
				elseif($line[$char]=="p"):	$line=substr_replace($line," -7",$char,1);
				elseif($line[$char]=="q"):	$line=substr_replace($line," -8",$char,1);
				elseif($line[$char]=="r"):	$line=substr_replace($line," -9",$char,1);
				endif;
			}
		}
		return $line;
	}

    /**
     * Process data that has been DUP compressed
     * @param $line
     * @return string
     */
    private function jcampdup($line)
	{
		$dupchars=["T","U","V","W","X","Y","Z","s"];  // No S as its not needed
		$str=$startstr=$endstr=$xdata="";
		// Check start of line problem with DUP chars inadvertently indicating first number (absolute) should be repeated
		if(substr_count($line," ")>1):		list($xdata,$startstr,$endstr)=explode(" ",$line,3);
		else:								list($xdata,$startstr)=explode(" ",$line,2);
		endif;
		$cleanstr=str_replace(["0","1","2","3","4","5","6","7","8","9","-","+"],"",$startstr);
		if($cleanstr!="")
		{
			if($cleanstr=="T"):			$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",1);
			elseif($cleanstr=="U"):		$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",2);
			elseif($cleanstr=="V"):		$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",3);
			elseif($cleanstr=="W"):		$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",4);
			elseif($cleanstr=="X"):		$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",5);
			elseif($cleanstr=="Y"):		$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",6);
			elseif($cleanstr=="Z"):		$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",7);
			elseif($cleanstr=="s"):		$startstr=$xdata." ".substr($startstr,0,-1).str_repeat(" 0",8);
			endif;
			$line=$startstr." ".$endstr;
		}
		// Conversion of chars STUVWXYZs into duplicate data points
		$lastsp=-1;
		for($char=0;$char<strlen($line);$char++)
		{
			if(in_array($line[$char],$dupchars))
			{
				if($line[$char]=="T"):		$str.=str_pad("",1*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				elseif($line[$char]=="U"):	$str.=str_pad("",2*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				elseif($line[$char]=="V"):	$str.=str_pad("",3*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				elseif($line[$char]=="W"):	$str.=str_pad("",4*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				elseif($line[$char]=="X"):	$str.=str_pad("",5*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				elseif($line[$char]=="Y"):	$str.=str_pad("",6*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				elseif($line[$char]=="Z"):	$str.=str_pad("",7*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				elseif($line[$char]=="s"):	$str.=str_pad("",8*($char-$lastsp)," ".substr($line,$lastsp+1,($char-$lastsp-1)));
				endif;
			}
			else
			{
				if($line[$char]==" ")	{ $lastsp=$char; }
				$str.=$line[$char];
			}
		}
		return $str;
	}

	/**
     * Process data that has been ADD compressed
     * @param $line
     * @return string
     */
    private function jcampadd($line)
	{
		// Conversion of the differences into actual values based on the known y value at the start of a line
		$yarray=explode(" ",$line);
		for($d=1;$d<count($yarray);$d++) // start at $d=1 so we ignore the X data point
		{
			if($yarray[$d]!="?")
			{
				if($yarray[$d-1]!="?") { $yarray[$d]=$yarray[$d-1]+$yarray[$d]; }
			}
			else
			{
				$yarray[$d]="?";
			}
		}
		return implode(" ",$yarray);
	}

    /**
     * Process data that has been PAC compressed
     * @param $line
     * @return string
     */
	private function jcamppac($line)
	{
		// Addition of spaces in front of +/-
		$line=str_replace("?","NaN",$line);
		if(stristr($line,"+"))		{ $line=str_replace("+"," +",$line); }
		if(stristr($line,"-"))		{ $line=str_replace("-"," -",$line); }
		$line = preg_replace('/\s\s+/', ' ', $line);
		//if(stristr($line,"  "))		{ $line=str_replace("  "," ",$line); }
		return trim($line);  // If negative X value then this removes the leading space just inserted
	}

    /**
     * Process data that has been FIX compressed
     * @param $line
     * @return mixed
     */
	private function jcampfix($line)
	{
		$line=str_replace("?","NaN",$line);
		$line=preg_replace("/\s\s+/", " ", $line);
		return $line;
	}
}
