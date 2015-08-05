<?php

/**
 * Configuration file for default values for creation of AnIML files
 */

$config['animl']['techniques']=['uvvis'=>'UV/Vis','nmr'=>'NMR','ms'=>'MS'];
$config['animl']['schemapath']="http://animl.cvs.sourceforge.net/viewvc/animl/techniques/";

// UV/Vis Technique

// Preferences for atdd file
$config['animl']['uvvis']['schemas']=['uv-vis dispersive spectrum'=>"UV/Vis Spectrum (Dispersive)",
                                        'uv-vis ft spectrum'=>"UV/Vis Spectrum (FT)",
                                        'uv-vis interferogram'=>"UV/Vis Interferogram",
                                        'uv-vis-peaktable'=>"UV/Vis Peak Table",
                                        'uv-vis-trace'=>"UV/Vis Trace (Chrom/Kinetics)",];
$config['animl']['uvvis']['atdd']['uv-vis dispersive spectrum']=$config['animl']['schemapath']."uv-vis%20dispersive%20spectrum.atdd";
$config['animl']['uvvis']['atdd']['uv-vis ft spectrum']=$config['animl']['schemapath']."uv-vis%20ft%20spectrum.atdd";
$config['animl']['uvvis']['atdd']['uv-vis interferogram']=$config['animl']['schemapath']."uv-vis%20interferogram.atdd";
$config['animl']['uvvis']['atdd']['uv-vis-peaktable']=$config['animl']['schemapath']."uv-vis-peaktable.atdd";
$config['animl']['uvvis']['atdd']['uv-vis-trace']=$config['animl']['schemapath']."uv-vis-trace.atdd";
$config['animl']['uvvis']['defaults']['atdd']="uv-vis dispersive spectrum";
$config['animl']['uvvis']['defaults']['SampleRole']="Test Sample";
$config['animl']['uvvis']['defaults']['ExperimentDataRole']="Reference Correction Spectrum";
$config['animl']['uvvis']['defaults']['Method']="";
$config['animl']['uvvis']['defaults']['Result']="Spectrum";

// Required elements in AnIML file (for validation)
$config['animl']['uvvis']['Required']['SampleSet']=['xpath'=>'//SampleSet[1]','default'=>null];
$config['animl']['uvvis']['Required']['Sample']=['xpath'=>'//SampleSet[1]/Sample[1]','default'=>null];
$config['animl']['uvvis']['Required']['Sample']['@name']=['xpath'=>'//Sample[1]/@name','default'=>"Test Sample"];
$config['animl']['uvvis']['Required']['Sample']['@id']=['xpath'=>'//Sample[1]/@sampleID','default'=>"sample0001"];
$config['animl']['uvvis']['Required']['ExperimentStepSet']=['xpath'=>'//ExperimentStepSet[1]','default'=>null];
$config['animl']['uvvis']['Required']['ExperimentStep']=['xpath'=>'//ExperimentStep[1]','default'=>null];
$config['animl']['uvvis']['Required']['ExperimentStep']['@name']=['xpath'=>'//ExperimentStep[1]/@name','default'=>"Analysis 1"];
$config['animl']['uvvis']['Required']['ExperimentStep']['@experimentStepID']=['xpath'=>'//ExperimentStep[1]/@experimentStepID','default'=>'step0001'];
$config['animl']['uvvis']['Required']['Method']=['xpath'=>'//Method[1]','default'=>null];
$config['animl']['uvvis']['Required']['Category']['Common Method']=['xpath'=>"///Category/@name='Common Method'",'defined'=>"Common Method"];
$config['animl']['uvvis']['Required']['Category']['Instrument Settings']=['xpath'=>"//Category/@name='Instrument Settings'",'defined'=>"Instrument Settings"];
$config['animl']['uvvis']['Required']['Parameter']['Measurement Type']=['xpath'=>"//Parameter/@name='Measurement Type'",'defined'=>"Measurement Type"];
$config['animl']['uvvis']['Required']['Parameter']['Measurement Type']['S']=['xpath'=>"//Parameter/@name='Measurement Type'/S",'default'=>"Spectrum"];

$config['animl']['uvvis']['Required']['Result']=['xpath'=>'//Result[1]','default'=>null];
$config['animl']['uvvis']['Required']['Result']['@name']=['xpath'=>'//Result[1]','default'=>"Spectrum"];
$config['animl']['uvvis']['Required']['SeriesSet']=['xpath'=>'//SeriesSet[1]','default'=>null];
$config['animl']['uvvis']['Required']['SeriesSet']['@name']=['xpath'=>'//SeriesSet[1]','default'=>"Spectrum"];
$config['animl']['uvvis']['Required']['SeriesSet']['@length']=['xpath'=>'//SeriesSet[1]','default'=>"NaN"];

$config['animl']['uvvis']['Required']['Series']=['xpath'=>'//Series[1]','default'=>null];
$config['animl']['uvvis']['Required']['Series']['@dependency=indendent']=['xpath'=>'//Series[1]/@name','choice'=>"Wavenumber|Radiant Energy|Wavelength"];
$config['animl']['uvvis']['Required']['Series']['@dependency=dependent']=['xpath'=>"//Series[2]/@name='Intensity'",'defined'=>"Intensity"];

$config['animl']['uvvis']['Required']['Category']['Measurement Description']=['xpath'=>"//Category/@name='Measurement Description'",'defined'=>"Measurement Description"];
$config['animl']['uvvis']['Required']['Parameter']['Experiment Duration']=['xpath'=>"//Parameter/@name='Experiment Duration'",'defined'=>"Experiment Duration"];
$config['animl']['uvvis']['Required']['Parameter']['Experiment Duration']['@parameterType']=['xpath'=>"//Parameter/@parameterType",'default'=>"Float32"];
$config['animl']['uvvis']['Required']['Parameter']['Experiment Duration']['F']=['xpath'=>"//Parameter/@name='Experiment Duration'/F",'default'=>"NaN"];
$config['animl']['uvvis']['Required']['Parameter']['Experiment Duration']['Unit']=['xpath'=>"//Parameter/@name='Experiment Duration'/Unit",'entity'=>true,'default'=>"&s;"];

// NMR

// Preferences for atdd file
$config['animl']['nmr']['schemas']=['1d-nmr'=>'1D NMR'];
$config['animl']['nmr']['atdd']['1d-nmr']="/Users/stu/Dropbox/Book/20%20-%20AnIML/Stuart/NMR%20Technique%20Defn/1d-nmr.atdd";
$config['animl']['nmr']['defaults']['atdd']="1d-nmr";
$config['animl']['nmr']['defaults']['SampleRole']="Test Sample";
$config['animl']['nmr']['defaults']['ExperimentDataRole']="Sample Spectrum";
$config['animl']['nmr']['defaults']['Method']="";
$config['animl']['nmr']['defaults']['Result']="Spectrum";

// Required elements in AnIML 1D NMR file
$config['animl']['nmr']['Required']['SampleSet']=['xpath'=>'//SampleSet[1]','default'=>null];
$config['animl']['nmr']['Required']['Sample']=['xpath'=>'//SampleSet[1]/Sample[1]','default'=>null];
$config['animl']['nmr']['Required']['Sample']['@name']=['xpath'=>'//Sample[1]/@name','default'=>"Test Sample"];
$config['animl']['nmr']['Required']['Sample']['@id']=['xpath'=>'//Sample[1]/@sampleID','default'=>"sample0001"];
$config['animl']['nmr']['Required']['ExperimentStepSet']=['xpath'=>'//ExperimentStepSet[1]','default'=>null];
$config['animl']['nmr']['Required']['ExperimentStep']=['xpath'=>'//ExperimentStep[1]','default'=>null];
$config['animl']['nmr']['Required']['ExperimentStep']['@name']=['xpath'=>'//ExperimentStep[1]/@name','default'=>"Analysis 1"];
$config['animl']['nmr']['Required']['ExperimentStep']['@experimentStepID']=['xpath'=>'//ExperimentStep[1]/@experimentStepID','default'=>'step0001'];
$config['animl']['nmr']['Required']['Method']=['xpath'=>'//Method[1]','default'=>null];
$config['animl']['nmr']['Required']['Category']['Common Method']=['xpath'=>"///Category/@name='Common Method'",'defined'=>"Common Method"];
$config['animl']['nmr']['Required']['Category']['Instrument Settings']=['xpath'=>"//Category/@name='Instrument Settings'",'defined'=>"Instrument Settings"];
$config['animl']['nmr']['Required']['Parameter']['Measurement Type']=['xpath'=>"//Parameter/@name='Measurement Type'",'defined'=>"Measurement Type"];
$config['animl']['nmr']['Required']['Parameter']['Measurement Type']['S']=['xpath'=>"//Parameter/@name='Measurement Type'/S",'default'=>"Spectrum"];

$config['animl']['nmr']['Required']['Result']=['xpath'=>'//Result[1]','default'=>null];
$config['animl']['nmr']['Required']['Result']['@name']=['xpath'=>'//Result[1]','default'=>"Spectrum"];
$config['animl']['nmr']['Required']['SeriesSet']=['xpath'=>'//SeriesSet[1]','default'=>null];
$config['animl']['nmr']['Required']['SeriesSet']['@name']=['xpath'=>'//SeriesSet[1]','default'=>"Spectrum"];
$config['animl']['nmr']['Required']['SeriesSet']['@length']=['xpath'=>'//SeriesSet[1]','default'=>"NaN"];

$config['animl']['nmr']['Crosswalk']['TITLE']=[];


// MS

// Preference for attd file
$config['animl']['ms']['schemas']=['ms'=>'MS'];
$config['animl']['ms']['atdd']['1d-nmr']="/Users/stu/Dropbox/Book/20%20-%20AnIML/Stuart/MS%20Technique%20Defn/ms.atdd";
$config['animl']['ms']['defaults']['atdd']="1d-nmr";
$config['animl']['ms']['defaults']['SampleRole']="Test Sample";
$config['animl']['ms']['defaults']['ExperimentDataRole']="Sample Spectrum";
$config['animl']['ms']['defaults']['Method']="";
$config['animl']['ms']['defaults']['Result']="Spectrum";

?>