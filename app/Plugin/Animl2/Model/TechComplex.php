<?php

/**
 * Class TechComplex
 */

class TechComplex extends AnimlAppModel
{
public $useTable = false;

/**
 * The root element of an AnIML Technique Definition document.  Techniques are typically categorized as either sample alteration, detection, or data post-processing.  Each document defines and constrains how ExperimentSteps and Sample definitions are to be filled for its respective technique in an AnIML document.
 */
public function TechniqueType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'ExtensionScope'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'SampleRoleBlueprint'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'ExperimentDataRoleBlueprint'];
$com['seq'][0]['ele'][4]=['minOccurs'=>'0','ref'=>'MethodBlueprint'];
$com['seq'][0]['ele'][5]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'ResultBlueprint'];
$com['seq'][0]['ele'][6]=['minOccurs'=>'0','ref'=>'Bibliography'];
// Version number of the AnIML Technique Schema used in this document. Must be "0.90".
$com['att'][0]=['fixed'=>'0.9','name'=>'version','type'=>'xsd:string','use'=>'required'];
// Technique Definition name (human-readable).
$com['att'][1]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
// Is this document an extension?  If false, it is a Technique Definition. If true, it is a Technique Extension.
$com['att'][2]=['default'=>'','name'=>'extension','type'=>'xsd:boolean','use'=>'optional'];
// Whether this Technique Definition or Extension needs to be used with an Extension to be valid. If true, at least one non-abstract extension is required in the ExperimentStep.
$com['att'][3]=['default'=>'','name'=>'abstract','type'=>'xsd:boolean','use'=>'optional'];
return $com;
}

/**
 * For Extensions only. Specifies which Technique Definitions or Extensions can be extended using this Extension.
 */
public function ExtensionScopeType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'ExtendedTechnique'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'ExtendedExtension'];
return $com;
}

/**
 * Reference to a Technique Definition which can be extended using this Extension.
 */
public function ExtendedTechniqueType()
{
// Name of Extension to be used. Must match Name given in Extension Definition file.
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
// URI where Technique Definition file can be fetched.
$com['att'][1]=['name'=>'uri','type'=>'xsd:anyURI','use'=>'required'];
// SHA256 checksum of the referenced Technique Definition. Hex encoded, lower cased. Similar to the output of the sha256 unix command.
$com['att'][2]=['name'=>'sha256','type'=>'xsd:token','use'=>'optional'];
return $com;
}

/**
 * Reference to an Extension which can be extended using this Extension.
 */
public function ExtendedExtensionType()
{
// Name of Extension to be used. Must match Name given in Extension Definition file.
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
// URI where Extension file can be fetched.
$com['att'][1]=['name'=>'uri','type'=>'xsd:anyURI','use'=>'required'];
// SHA256 checksum of the referenced Extension. Hex encoded, lower cased. Similar to the output of the sha256 unix command.
$com['att'][2]=['name'=>'sha256','type'=>'xsd:token','use'=>'optional'];
return $com;
}

/**
 * Definition of characteristics and role that the referenced Sample plays in the ExperimentStep.
 */
public function SampleRoleBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'CategoryBlueprint'];
// SampleRole name used in the SampleReference element's role attribute.
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
// Is the sample in this role produced or consumed by the Technique? (see SamplePurposeType).
$com['att'][1]=['name'=>'samplePurpose','type'=>'PurposeType','use'=>'required'];
// Is a sample in this role required?
$com['att'][2]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
// Specifies how many samples with this role may exist. In an AnIML file, the Role name must then be suffixed with a number, starting with 1.
$com['att'][3]=['default'=>'1','name'=>'maxOccurs','type'=>'MaxOccursType','use'=>'optional'];
// Is the role required when this ExperimentStep is nested under a Result?  If true, the sample is inherited from the superordinate ExperimentStep.
$com['att'][4]=['default'=>'1','name'=>'inheritable','type'=>'xsd:boolean','use'=>'optional'];
return $com;
}

/**
 * Definition of characteristics and role that the referenced ExperimentStep plays in the ExperimentStep.
 */
public function ExperimentDataRoleBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
// Role name used in the ExperimentData(Bulk)Reference element's role attribute.
$com['att'][0]=['name'=>'name','type'=>'ShortStringType','use'=>'required'];
$com['att'][1]=['name'=>'experimentStepPurpose','type'=>'PurposeType','use'=>'required'];
// Are ExperimentData(Bulk)References with this role required?
$com['att'][2]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
// Specifies how many ExperimentData(Bulk)References with this role may exist.
$com['att'][3]=['default'=>'1','name'=>'maxOccurs','type'=>'MaxOccursType','use'=>'optional'];
return $com;
}

/**
 * Description of the experimental method used.
 */
public function MethodBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'CategoryBlueprint'];
return $com;
}

/**
 * Definition of a Result generated by the ExperimentStep.
 */
public function ResultBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'SeriesSetBlueprint'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'CategoryBlueprint'];
// Required name of the Result.
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
// Is the Result optional or required?
$com['att'][1]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
// Maximum number of occurences of the Result. If set to > 1, the index attribute (1+) must be used in the AnIML document.
$com['att'][2]=['default'=>'1','name'=>'maxOccurs','type'=>'MaxOccursType','use'=>'optional'];
return $com;
}

/**
 * Definition of SeriesSet that needs to be attached at this point.
 */
public function SeriesSetBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['cho'][0]=['maxOccurs'=>'unbounded'];
$com['seq'][0]['cho'][0]['ele'][0]=['ref'=>'SeriesBlueprint'];
$com['seq'][0]['cho'][0]['ele'][1]=['ref'=>'SeriesBlueprintChoice'];
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
$com['att'][1]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
return $com;
}

/**
 * Set of alternative Series which need to be attached to this SeriesSet.
 */
public function SeriesBlueprintChoiceType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['ele'][1]=['maxOccurs'=>'unbounded','ref'=>'SeriesBlueprint'];
$com['att'][0]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
return $com;
}

/**
 * Definition of Series that needs to be attached to this SeriesSet.
 */
public function SeriesBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Quantity'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'AllowedValue'];
// Name the Series shall have.
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
// Data type this Series shall have.
$com['att'][1]=['name'=>'seriesType','type'=>'SeriesTypeType','use'=>'required'];
// Is the Series optional or required?
$com['att'][2]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
$com['att'][3]=['default'=>'linear','name'=>'plotScale','type'=>'PlotScaleType','use'=>'optional'];
$com['att'][4]=['name'=>'dependency','type'=>'DependencyType','use'=>'required'];
// Maximum number of occurences of the Series. If > 1, the index attribute must be used in the AnIML document.
$com['att'][5]=['default'=>'1','name'=>'maxOccurs','type'=>'MaxOccursType','use'=>'optional'];
return $com;
}

/**
 * Collection of Parameters to be used on this hierarchy level.
 */
public function CategoryBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'SeriesSetBlueprint'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'ParameterBlueprint'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'CategoryBlueprint'];
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
$com['att'][1]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
$com['att'][2]=['default'=>'1','name'=>'maxOccurs','type'=>'MaxOccursType','use'=>'optional'];
return $com;
}

/**
 * Name-value pair to be stored in current Category.
 */
public function ParameterBlueprintType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['cho'][0]=['minOccurs'=>'0'];
$com['seq'][0]['cho'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Quantity'];
$com['seq'][0]['cho'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'AllowedValue'];
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
$com['att'][1]=['name'=>'parameterType','type'=>'ParameterTypeType','use'=>'required'];
$com['att'][2]=['default'=>'required','name'=>'modality','type'=>'ModalityType','use'=>'optional'];
// Maximum number of occurences of the Series. If multiple occurrence is uses, an index (1+) must be appended to the Series' name in the AnIML document.
$com['att'][3]=['default'=>'1','name'=>'maxOccurs','type'=>'MaxOccursType','use'=>'optional'];
return $com;
}

/**
 * Elements for allowed values in Parameters.
 */
public function ParameterValueType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['cho'][0]=[];
$com['seq'][0]['cho'][0]['ele'][0]=['ref'=>'I'];
$com['seq'][0]['cho'][0]['ele'][1]=['ref'=>'L'];
$com['seq'][0]['cho'][0]['ele'][2]=['ref'=>'F'];
$com['seq'][0]['cho'][0]['ele'][3]=['ref'=>'D'];
$com['seq'][0]['cho'][0]['ele'][4]=['ref'=>'S'];
$com['seq'][0]['cho'][0]['ele'][5]=['ref'=>'Boolean'];
$com['seq'][0]['cho'][0]['ele'][6]=['ref'=>'DateTime'];
$com['seq'][0]['cho'][0]['ele'][7]=['ref'=>'PNG'];
$com['seq'][0]['cho'][0]['ele'][8]=['ref'=>'EmbeddedXML'];
$com['seq'][0]['cho'][0]['ele'][9]=['ref'=>'SVG'];
return $com;
}

/**
 * Elements for allowed numeric values in Parameters.
 */
public function NumericValueType()
{
$com['con'][0]=[];
$com['con'][0]['res'][0]=['base'=>'ParameterValueType'];
$com['con'][0]['res'][0]['seq'][0]=[];
$com['con'][0]['res'][0]['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]=[];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][0]=['ref'=>'I'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][1]=['ref'=>'L'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][2]=['ref'=>'F'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][3]=['ref'=>'D'];
return $com;
}

/**
 * Description of the enclosing element.
 */
public function DocumentationType()
{
$com['sim'][0]=[];
$com['sim'][0]['ext'][0]=['base'=>'xsd:string'];
return $com;
}

/**
 * Set of literature references used in the documentation of this technique definition.
 */
public function BibliographyType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['maxOccurs'=>'unbounded','ref'=>'LiteratureReference'];
return $com;
}

/**
 * Literature reference cited from within the Documentation element.
 */
public function LiteratureReferenceType()
{
$com['sim'][0]=[];
$com['sim'][0]['ext'][0]=['base'=>'xsd:string'];
return $com;
}

/**
 * Definition of an allowable Quantity and its associated Units.
 */
public function QuantityType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Unit'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'AllowedRange'];
$com['att'][0]=['name'=>'name','type'=>'QuantityNameType','use'=>'required'];
return $com;
}

/**
 * Definition of a supported Scientific Unit.
 */
public function UnitType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'SIUnit'];
// Defines the visual representation of a particular Unit.
$com['att'][0]=['name'=>'label','type'=>'LabelType','use'=>'required'];
return $com;
}

/**
 * Combination of SI Units used to represent Scientific unit
 */
public function SIUnitType()
{
$com['sim'][0]=[];
$com['sim'][0]['ext'][0]=['base'=>'SIUnitNameList'];
return $com;
}

/**
 * Permitted Values for Parameter. If not specified, the full range of the data type is allowed.
 */
public function AllowedValueType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'ParameterValueType'];
return $com;
}

/**
 * Allowed value range for a Parameter or Series.
 */
public function AllowedRangeType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Documentation'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'Min'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'Max'];
// Label of Unit that applies to this allowed range of values.
$com['att'][0]=['name'=>'unit','type'=>'ShortTokenType','use'=>'optional'];
return $com;
}

/**
 * Lower range boundary; may be marked as inclusive or exclusive.
 */
public function MinType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'NumericValueType'];
return $com;
}

/**
 * Upper range boundary; may be marked as inclusive or exclusive.
 */
public function MaxType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'NumericValueType'];
return $com;
}

}
?>