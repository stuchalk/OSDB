<?php

/**
 * Class CoreComplex
 */

class CoreComplex extends AnimlAppModel
{
public $useTable = false;

/**
 * ComplexType for the root element of an AnIML document.
 */
public function AnIMLType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'SampleSet'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'ExperimentStepSet'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'AuditTrailEntrySet'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','ref'=>'SignatureSet'];
// Version number of the AnIML Core Schema used in this document. Must be "0.90".
$com['att'][0]=['fixed'=>'0.9','name'=>'version','type'=>'ShortStringType','use'=>'required'];
return $com;
}

/**
 * Container for Samples used in this AnIML document.
 */
public function SampleSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['maxOccurs'=>'unbounded','ref'=>'Sample'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Container for multiple ExperimentSteps that describe the process and results.
 */
public function ExperimentStepSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Template'];
$com['seq'][0]['ele'][1]=['maxOccurs'=>'unbounded','ref'=>'ExperimentStep'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Container for audit trail entries describing changes to this document.
 */
public function AuditTrailEntrySetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'AuditTrailEntry'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Container for digital signatures covering parts of this AnIML document.
 */
public function SignatureSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['maxOccurs'=>'unbounded','ref'=>'Signature'];
return $com;
}

/**
 * Individual Sample, referenced from other parts of this AnIML document.
 */
public function SampleType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'TagSet'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Category'];
$com['att'][0]=['name'=>'sampleID','type'=>'ShortTokenType','use'=>'required'];
// Value of barcode label that is attached to sample container.
$com['att'][1]=['name'=>'barcode','type'=>'ShortTokenType','use'=>'optional'];
// Unstructured text comment to further describe the Sample.
$com['att'][2]=['name'=>'comment','type'=>'ShortStringType','use'=>'optional'];
// Indicates whether this is a derived Sample. A derived Sample is a Sample that has been created by applying a Technique. (Sub-Sampling, Processing, ...)
$com['att'][3]=['default'=>'','name'=>'derived','type'=>'xsd:boolean','use'=>'optional'];
// Whether this sample is also a container for other samples. Set to "simple" if not.
$com['att'][4]=['default'=>'simple','name'=>'containerType','type'=>'ContainerTypeType','use'=>'optional'];
// Sample ID of container in which this sample is located.
$com['att'][5]=['name'=>'containerID','type'=>'ShortTokenType','use'=>'optional'];
// Coordinates of this sample within the enclosing container. In case of microplates or trays, the row is identified by letters and the column is identified by numbers (1-based) while in landscape orientation. Examples: A10 = 1st row, 10th column, Z1 = 26th row, 1st column, AB2 = 28th row, 2nd column.
$com['att'][6]=['name'=>'locationInContainer','type'=>'ShortTokenType','use'=>'optional'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
$com['grp'][1]=['ref'=>'SourceDataLocation'];
return $com;
}

/**
 * Container that documents a step in an experiment. Use one ExperimentStep per application of a Technique.
 */
public function ExperimentStepType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'TagSet'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'Technique'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'Infrastructure'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','ref'=>'Method'];
$com['seq'][0]['ele'][4]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Result'];
// Unique identifier for this ExperimentStep. Used to point to this step from an ExperimentDataReference.
$com['att'][0]=['name'=>'experimentStepID','type'=>'ShortTokenType','use'=>'required'];
$com['att'][1]=['name'=>'templateUsed','type'=>'ShortTokenType','use'=>'optional'];
// Unstructured text comment to further describe the ExperimentStep.
$com['att'][2]=['name'=>'comment','type'=>'ShortStringType','use'=>'optional'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
$com['grp'][1]=['ref'=>'SourceDataLocation'];
return $com;
}

/**
 * Represents a template for an ExperimentStep.
 */
public function TemplateType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'TagSet'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'Technique'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'Infrastructure'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','ref'=>'Method'];
$com['seq'][0]['ele'][4]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Result'];
$com['att'][0]=['name'=>'templateID','type'=>'ShortTokenType','use'=>'required'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
$com['grp'][1]=['ref'=>'SourceDataLocation'];
return $com;
}

/**
 * Describes how this Experiment was performed.
 */
public function MethodType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Author'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'Device'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'Software'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Category'];
// Optional method name, as defined in the instrument software.
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'optional'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Contains references to the context of this Experiment.
 */
public function InfrastructureType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'SampleReferenceSet'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'ParentDataPointReferenceSet'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'ExperimentDataReferenceSet'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','ref'=>'Timestamp'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Set of Samples used in this Experiment.
 */
public function SampleReferenceSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'SampleReference'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'SampleInheritance'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Reference to Technique Definition used in this Experiment.
 */
public function TechniqueType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Extension'];
// URI where Technique Definition file can be fetched.
$com['att'][0]=['name'=>'uri','type'=>'xsd:anyURI','use'=>'required'];
// SHA256 checksum of the referenced Technique Definition. Hex encoded, lower cased. Similar to the output of the sha256 unix command.
$com['att'][1]=['name'=>'sha256','type'=>'xsd:token','use'=>'optional'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
return $com;
}

/**
 * Reference to an Extension to amend the active Technique Definition.
 */
public function ExtensionType()
{
// URI where Extension file can be fetched.
$com['att'][0]=['name'=>'uri','type'=>'xsd:anyURI','use'=>'required'];
// Name of Extension to be used. Must match Name given in Extension Definition file.
$com['att'][1]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
// SHA256 checksum of the referenced Extension. Hex encoded, lower cased. Similar to the output of the sha256 unix command.
$com['att'][2]=['name'=>'sha256','type'=>'xsd:token','use'=>'optional'];
return $com;
}

/**
 * Contains references to the parent Result.
 */
public function ParentDataPointReferenceSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['maxOccurs'=>'unbounded','ref'=>'ParentDataPointReference'];
return $com;
}

/**
 * Reference to a data point or value range in an independent Series in the parent Result.
 */
public function ParentDataPointReferenceType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['ref'=>'StartValue'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'EndValue'];
// Contains the ID of the Series referenced.
$com['att'][0]=['name'=>'seriesID','type'=>'ShortTokenType','use'=>'required'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Reference to a Sample used in this Experiment.
 */
public function SampleReferenceType()
{
// SampleID of the Sample used in the current ExperimentStep. Refers to the sampleID within the SampleSet section of the document.
$com['att'][0]=['name'=>'sampleID','type'=>'ShortTokenType','use'=>'required'];
$com['grp'][0]=['ref'=>'SignableItem'];
$com['grp'][1]=['ref'=>'SampleAttributes'];
return $com;
}

/**
 * Indicates that a Sample was inherited from the parent ExperimentStep.
 */
public function SampleInheritanceType()
{
$com['grp'][0]=['ref'=>'SignableItem'];
$com['grp'][1]=['ref'=>'SampleAttributes'];
return $com;
}

/**
 * Set of Experiment Steps consumed by this Experiment Step.
 */
public function ExperimentDataReferenceSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'ExperimentDataReference'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'ExperimentDataBulkReference'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Reference to an Experiment Step whose data is consumed.
 */
public function ExperimentDataReferenceType()
{
$com['att'][0]=['name'=>'experimentStepID','type'=>'ShortTokenType','use'=>'required'];
$com['grp'][0]=['ref'=>'SignableItem'];
$com['grp'][1]=['ref'=>'ExperimentDataAttributes'];
return $com;
}

/**
 * Prefix-based reference to a set of Experiment Steps whose data are consumed.
 */
public function ExperimentDataBulkReferenceType()
{
$com['att'][0]=['name'=>'experimentStepIDPrefix','type'=>'ShortTokenType','use'=>'required'];
$com['grp'][0]=['ref'=>'SignableItem'];
$com['grp'][1]=['ref'=>'ExperimentDataAttributes'];
return $com;
}

/**
 * Container for Data derived from Experiment.
 */
public function ResultType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'SeriesSet'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Category'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'ExperimentStepSet'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
return $com;
}

/**
 * Container for n-dimensional Data.
 */
public function SeriesSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['maxOccurs'=>'unbounded','ref'=>'Series'];
// Number of data points each Series contains.
$com['att'][0]=['name'=>'length','type'=>'NonNegativeIntType','use'=>'required'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
return $com;
}

/**
 * Container for multiple Values.
 */
public function SeriesType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Unit'];
$com['seq'][0]['cho'][0]=['minOccurs'=>'0'];
$com['seq'][0]['cho'][0]['ele'][0]=['maxOccurs'=>'unbounded','ref'=>'IndividualValueSet'];
$com['seq'][0]['cho'][0]['ele'][1]=['maxOccurs'=>'unbounded','ref'=>'EncodedValueSet'];
$com['seq'][0]['cho'][0]['ele'][2]=['maxOccurs'=>'unbounded','ref'=>'AutoIncrementedValueSet'];
// Specified whether the Series is independent or dependent.
$com['att'][0]=['name'=>'dependency','type'=>'DependencyType','use'=>'required'];
// Identifies the Series. Used in References from subordinate ExperimentSteps. Unique per SeriesSet.
$com['att'][1]=['name'=>'seriesID','type'=>'ShortTokenType','use'=>'required'];
// Specifies whether data in this Series is to be displayed to the user by default.
$com['att'][2]=['default'=>'1','name'=>'visible','type'=>'VisibleType','use'=>'optional'];
// Specifies whether the data in this Series is typically plotted on a linear or logarithmic scale.
$com['att'][3]=['default'=>'linear','name'=>'plotScale','type'=>'PlotScaleType','use'=>'optional'];
// Data type used by all values in this Series.
$com['att'][4]=['name'=>'seriesType','type'=>'SeriesTypeType','use'=>'required'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
return $com;
}

/**
 * Multiple Values explicitly specified.
 */
public function IndividualValueSetType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'UnboundedValueType'];
$com['con'][0]['ext'][0]['grp'][0]=['ref'=>'ValueSet'];
return $com;
}

/**
 * Multiple numeric values encoded as a base64 binary string. Uses little-endian byte order.
 */
public function EncodedValueSetType()
{
$com['sim'][0]=[];
$com['sim'][0]['ext'][0]=['base'=>'xsd:base64Binary'];
$com['sim'][0]['ext'][0]['grp'][0]=['ref'=>'ValueSet'];
return $com;
}

/**
 * Lower boundary of an interval or ValueSet.
 */
public function StartValueType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'NumericValueType'];
return $com;
}

/**
 * Upper boundary of an interval.
 */
public function EndValueType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'NumericValueType'];
return $com;
}

/**
 * Increment value
 */
public function IncrementType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'NumericValueType'];
return $com;
}

/**
 * Multiple values given in form of a start value and an increment.
 */
public function AutoIncrementedValueSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['ref'=>'StartValue'];
$com['seq'][0]['ele'][1]=['ref'=>'Increment'];
$com['grp'][0]=['ref'=>'ValueSet'];
return $com;
}

/**
 * Definition of a Scientific Unit.
 */
public function UnitType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'SIUnit'];
// Defines the visual representation of a particular Unit.
$com['att'][0]=['name'=>'label','type'=>'LabelType','use'=>'required'];
// Quantity the unit can be applied to
$com['att'][1]=['name'=>'quantity','type'=>'QuantityType','use'=>'optional'];
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
 * Describes a set of changes made to the particular AnIML document by one user at a given time.
 */
public function AuditTrailEntryType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['ref'=>'Timestamp'];
$com['seq'][0]['ele'][1]=['ref'=>'Author'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'Software'];
$com['seq'][0]['ele'][3]=['ref'=>'Action'];
$com['seq'][0]['ele'][4]=['minOccurs'=>'0','ref'=>'Reason'];
$com['seq'][0]['ele'][5]=['minOccurs'=>'0','ref'=>'Comment'];
$com['seq'][0]['ele'][6]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Diff'];
$com['seq'][0]['ele'][7]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Reference'];
$com['grp'][0]=['ref'=>'SignableItem'];
return $com;
}

/**
 * Machine-readable description of changes made.
 */
public function DiffType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['ref'=>'OldValue'];
$com['seq'][0]['ele'][1]=['ref'=>'NewValue'];
// Scope of diff. May be "element" or "attribute".
$com['att'][0]=['name'=>'scope','type'=>'ScopeType','use'=>'required'];
// ID of the SignableItem that was changed
$com['att'][1]=['name'=>'changedItem','type'=>'xsd:IDREF','use'=>'required'];
return $com;
}

/**
 * Defines a category of Parameters and SeriesSets. Used to model hierarchies.
 */
public function CategoryType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Parameter'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'SeriesSet'];
// Contains multiple subcategories of Category.
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Category'];
$com['grp'][0]=['ref'=>'SignableItemWithName'];
return $com;
}

/**
 * Name/Value Pair.
 */
public function ParameterType()
{
$com['con'][0]=[];
$com['con'][0]['ext'][0]=['base'=>'SingleValueType'];
$com['con'][0]['ext'][0]['grp'][0]=['ref'=>'SignableItemWithName'];
return $com;
}

/**
 * Base type for all value elements for use in Series and parameters. To be restricted.
 */
public function UnboundedValueType()
{
$com['seq'][0]=[];
$com['seq'][0]['cho'][0]=['maxOccurs'=>'unbounded'];
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
 * Elements for IndividualValueSets in Series.
 */
public function SingleValueType()
{
$com['con'][0]=[];
$com['con'][0]['res'][0]=['base'=>'UnboundedValueType'];
$com['con'][0]['res'][0]['seq'][0]=[];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]=[];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][0]=['ref'=>'I'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][1]=['ref'=>'L'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][2]=['ref'=>'F'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][3]=['ref'=>'D'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][4]=['ref'=>'S'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][5]=['ref'=>'Boolean'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][6]=['ref'=>'DateTime'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][7]=['ref'=>'PNG'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][8]=['ref'=>'EmbeddedXML'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][9]=['ref'=>'SVG'];
return $com;
}

/**
 * Elements for numeric Series values.
 */
public function NumericValueType()
{
$com['con'][0]=[];
$com['con'][0]['res'][0]=['base'=>'UnboundedValueType'];
$com['con'][0]['res'][0]['seq'][0]=[];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]=[];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][0]=['ref'=>'I'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][1]=['ref'=>'L'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][2]=['ref'=>'F'];
$com['con'][0]['res'][0]['seq'][0]['cho'][0]['ele'][3]=['ref'=>'D'];
return $com;
}

/**
 * Set of Tag elements.
 */
public function TagSetType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','maxOccurs'=>'unbounded','ref'=>'Tag'];
return $com;
}

/**
 * Tag to mark related data items. When a value is given, it may also serve as a reference to an external data system.
 */
public function TagType()
{
$com['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
$com['att'][1]=['name'=>'value','type'=>'ShortStringType','use'=>'optional'];
return $com;
}

/**
 * Information about a person, a device or a piece of software authoring AnIML files.
 */
public function AuthorType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['ref'=>'Name'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'Affiliation'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'Role'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','ref'=>'Email'];
$com['seq'][0]['ele'][4]=['minOccurs'=>'0','ref'=>'Phone'];
$com['seq'][0]['ele'][5]=['minOccurs'=>'0','ref'=>'Location'];
// Type of user (human, device, software)
$com['att'][0]=['name'=>'userType','type'=>'UserTypeType','use'=>'required'];
return $com;
}

/**
 * Device used to perform experiment.
 */
public function DeviceType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'DeviceIdentifier'];
$com['seq'][0]['ele'][1]=['minOccurs'=>'0','ref'=>'Manufacturer'];
$com['seq'][0]['ele'][2]=['ref'=>'Name'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','ref'=>'FirmwareVersion'];
$com['seq'][0]['ele'][4]=['minOccurs'=>'0','ref'=>'SerialNumber'];
return $com;
}

/**
 * Software used to author this.
 */
public function SoftwareType()
{
$com['seq'][0]=[];
$com['seq'][0]['ele'][0]=['minOccurs'=>'0','ref'=>'Manufacturer'];
$com['seq'][0]['ele'][1]=['ref'=>'Name'];
$com['seq'][0]['ele'][2]=['minOccurs'=>'0','ref'=>'Version'];
$com['seq'][0]['ele'][3]=['minOccurs'=>'0','ref'=>'OperatingSystem'];
return $com;
}

}
?>