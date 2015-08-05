<?php

/**
 * Class CoreElement
 */

class CoreElement extends AnimlAppModel
{
public $useTable = false;

/**
 * Root Element for AnIML documents.
 */
public function AnIML()
{
$ele['type']='AnIMLType';
$ele['key'][0]=['name'=>'sampleID'];
// An ExperimentDataReference may only reference ExperimentSteps that carry a valid experimentStepID.
$ele['ref'][0]=['name'=>'experimentStepIDUsage','refer'=>'experimentStepID'];
$ele['ref'][0]['sel']=['xpath'=>'.//ExperimentStep/ExperimentDataReferenceSet/ExperimentDataReference'];
$ele['ref'][0]['fld']=['xpath'=>'@experimentStepID'];
// If an ExperimentStep carries an experimentStepID attribute, its value must be unique across the entire document.
$ele['uni'][0]=['name'=>'experimentStepID'];
$ele['uni'][0]['sel']=['xpath'=>'.//ExperimentStep'];
$ele['uni'][0]['fld']=['xpath'=>'@experimentStepID'];
return $ele;
}

/**
 * Container for Samples used in this AnIML document.
 */
public function SampleSet()
{
$ele['type']='SampleSetType';
return $ele;
}

/**
 * Container for multiple ExperimentSteps that describe the process and results.
 */
public function ExperimentStepSet()
{
$ele['type']='ExperimentStepSetType';
$ele['key'][0]=['name'=>'templateID'];
$ele['ref'][0]=['name'=>'templateUsedRef','refer'=>'templateID'];
$ele['ref'][0]['sel']=['xpath'=>'./ExperimentStep'];
$ele['ref'][0]['fld']=['xpath'=>'@templateUsed'];
return $ele;
}

/**
 * Container for audit trail entries describing changes to this document.
 */
public function AuditTrailEntrySet()
{
$ele['type']='AuditTrailEntrySetType';
return $ele;
}

/**
 * Container for digital signatures covering parts of this AnIML document.
 */
public function SignatureSet()
{
$ele['type']='SignatureSetType';
return $ele;
}

/**
 * Individual Sample, referenced from other parts of this AnIML document.
 */
public function Sample()
{
$ele['type']='SampleType';
return $ele;
}

/**
 * Container that documents a step in an experiment. Use one ExperimentStep per application of a Technique.
 */
public function ExperimentStep()
{
$ele['type']='ExperimentStepType';
return $ele;
}

/**
 * Represents a template for an ExperimentStep.
 */
public function Template()
{
$ele['type']='TemplateType';
return $ele;
}

/**
 * Date and time of modification.
 */
public function Timestamp()
{
$ele['type']='TimestampType';
return $ele;
}

/**
 * Describes how this Experiment was performed.
 */
public function Method()
{
$ele['type']='MethodType';
return $ele;
}

/**
 * Contains references to the context of this Experiment.
 */
public function Infrastructure()
{
$ele['type']='InfrastructureType';
return $ele;
}

/**
 * Set of Samples used in this Experiment.
 */
public function SampleReferenceSet()
{
$ele['type']='SampleReferenceSetType';
return $ele;
}

/**
 * Reference to Technique Definition used in this Experiment.
 */
public function Technique()
{
$ele['type']='TechniqueType';
return $ele;
}

/**
 * Reference to an Extension to amend the active Technique Definition.
 */
public function Extension()
{
$ele['type']='ExtensionType';
return $ele;
}

/**
 * Contains references to the parent Result.
 */
public function ParentDataPointReferenceSet()
{
$ele['type']='ParentDataPointReferenceSetType';
return $ele;
}

/**
 * Reference to a data point or value range in an independent Series in the parent Result.
 */
public function ParentDataPointReference()
{
$ele['type']='ParentDataPointReferenceType';
return $ele;
}

/**
 * Reference to a Sample used in this Experiment.
 */
public function SampleReference()
{
$ele['type']='SampleReferenceType';
return $ele;
}

/**
 * Indicates that a Sample was inherited from the parent ExperimentStep.
 */
public function SampleInheritance()
{
$ele['type']='SampleInheritanceType';
return $ele;
}

/**
 * Set of Experiment Steps consumed by this Experiment Step.
 */
public function ExperimentDataReferenceSet()
{
$ele['type']='ExperimentDataReferenceSetType';
return $ele;
}

/**
 * Reference to an Experiment Step whose data are consumed.
 */
public function ExperimentDataReference()
{
$ele['type']='ExperimentDataReferenceType';
return $ele;
}

/**
 * Prefix-based reference to a set of Experiment Steps whose data are consumed.
 */
public function ExperimentDataBulkReference()
{
$ele['type']='ExperimentDataBulkReferenceType';
return $ele;
}

/**
 * Container for Data derived from Experiment.
 */
public function Result()
{
$ele['type']='ResultType';
return $ele;
}

/**
 * Container for n-dimensional Data.
 */
public function SeriesSet()
{
$ele['type']='SeriesSetType';
return $ele;
}

/**
 * Container for multiple Values.
 */
public function Series()
{
$ele['type']='SeriesType';
return $ele;
}

/**
 * Multiple Values explicitly specified.
 */
public function IndividualValueSet()
{
$ele['type']='IndividualValueSetType';
return $ele;
}

/**
 * Multiple numeric values encoded as a base64 binary string. Uses little-endian byte order.
 */
public function EncodedValueSet()
{
$ele['type']='EncodedValueSetType';
return $ele;
}

/**
 * Lower boundary of an interval or ValueSet.
 */
public function StartValue()
{
$ele['type']='StartValueType';
return $ele;
}

/**
 * Upper boundary of an interval.
 */
public function EndValue()
{
$ele['type']='EndValueType';
return $ele;
}

/**
 * Increment value
 */
public function Increment()
{
$ele['type']='IncrementType';
return $ele;
}

/**
 * Multiple values given in form of a start value and an increment.
 */
public function AutoIncrementedValueSet()
{
$ele['type']='AutoIncrementedValueSetType';
return $ele;
}

/**
 * Definition of a Scientific Unit.
 */
public function Unit()
{
$ele['type']='UnitType';
return $ele;
}

/**
 * Combination of SI Units used to represent Scientific Unit.
 */
public function SIUnit()
{
$ele['type']='SIUnitType';
return $ele;
}

/**
 * Describes a set of changes made to the particular AnIML document by one user at a given time.
 */
public function AuditTrailEntry()
{
$ele['type']='AuditTrailEntryType';
return $ele;
}

/**
 * Type of change made (created, modified, ...)
 */
public function Action()
{
$ele['type']='ActionType';
return $ele;
}

/**
 * Explanation why changes were made.
 */
public function Reason()
{
$ele['type']='ReasonType';
return $ele;
}

/**
 * Human-readable comment further explaining the changes.
 */
public function Comment()
{
$ele['type']='CommentType';
return $ele;
}

/**
 * Machine-readable description of changes made.
 */
public function Diff()
{
$ele['type']='DiffType';
return $ele;
}

/**
 * Value before the change.
 */
public function OldValue()
{
$ele['type']='xsd:string';
return $ele;
}

/**
 * Value after the change.
 */
public function NewValue()
{
$ele['type']='xsd:string';
return $ele;
}

/**
 * ID of the SignableItem that was affected. If none is specified, entire document is covered.
 */
public function Reference()
{
$ele['type']='ReferenceType';
return $ele;
}

/**
 * Digital Signature that has been applied to a part of this AnIML document. Uses the W3C XML-DSIG specification.
 */
public function Signature()
{
$ele['type']='ds:SignatureType';
return $ele;
}

/**
 * Defines a category of Parameters and SeriesSets. Used to model hierarchies.
 */
public function Category()
{
$ele['type']='CategoryType';
return $ele;
}

/**
 * Name/Value Pair.
 */
public function Parameter()
{
$ele['type']='ParameterType';
return $ele;
}

/**
 * Individual integer value (32 bits, signed).
 */
public function I()
{
$ele['type']='Int32Type';
return $ele;
}

/**
 * Individual long integer value (64 bits, signed).
 */
public function L()
{
$ele['type']='Int64Type';
return $ele;
}

/**
 * Individual 32-bit floating point value.
 */
public function F()
{
$ele['type']='Float32Type';
return $ele;
}

/**
 * Individual 64-bit floating point value.
 */
public function D()
{
$ele['type']='Float64Type';
return $ele;
}

/**
 * Individual string value.
 */
public function S()
{
$ele['type']='StringType';
return $ele;
}

/**
 * Individual boolean value.
 */
public function Boolean()
{
$ele['type']='BooleanType';
return $ele;
}

/**
 * Individual ISO date/time value.
 */
public function DateTime()
{
$ele['type']='DateTimeType';
return $ele;
}

/**
 * Base 64 encoded PNG image.
 */
public function PNG()
{
$ele['type']='PNGType';
return $ele;
}

/**
 * Value governed by a different XML Schema.
 */
public function EmbeddedXML()
{
$ele['type']='EmbeddedXMLType';
return $ele;
}

/**
 * Value governed by the SVG DTD. Used to represent vector graphic images.
 */
public function SVG()
{
$ele['type']='SVGType';
return $ele;
}

/**
 * Set of Tag elements.
 */
public function TagSet()
{
$ele['type']='TagSetType';
return $ele;
}

/**
 * Tag to mark related data items. When a value is given, it may also serve as a reference to an external data system.
 */
public function Tag()
{
$ele['type']='TagType';
return $ele;
}

/**
 * Information about a person, a device or a piece of software authoring AnIML files.
 */
public function Author()
{
$ele['type']='AuthorType';
return $ele;
}

/**
 * Common name.
 */
public function Name()
{
$ele['type']='NameType';
return $ele;
}

/**
 * Organization the Author is affiliated with.
 */
public function Affiliation()
{
$ele['type']='AffiliationType';
return $ele;
}

/**
 * Role the Author plays within the organization.
 */
public function Role()
{
$ele['type']='RoleType';
return $ele;
}

/**
 * RFC822-compliant email address.
 */
public function Email()
{
$ele['type']='EmailType';
return $ele;
}

/**
 * Phone number.
 */
public function Phone()
{
$ele['type']='PhoneType';
return $ele;
}

/**
 * Location or physical address.
 */
public function Location()
{
$ele['type']='LocationType';
return $ele;
}

/**
 * Device used to perform experiment.
 */
public function Device()
{
$ele['type']='DeviceType';
return $ele;
}

/**
 * Unique name or identifier of the device.
 */
public function DeviceIdentifier()
{
$ele['type']='DeviceIdentifierType';
return $ele;
}

/**
 * Company name.
 */
public function Manufacturer()
{
$ele['type']='ManufacturerType';
return $ele;
}

/**
 * Version identifier of firmware release.
 */
public function FirmwareVersion()
{
$ele['type']='FirmwareVersionType';
return $ele;
}

/**
 * Unique serial number of device.
 */
public function SerialNumber()
{
$ele['type']='SerialNumberType';
return $ele;
}

/**
 * Software used to author this.
 */
public function Software()
{
$ele['type']='SoftwareType';
return $ele;
}

/**
 * Operating system the software was running on.
 */
public function OperatingSystem()
{
$ele['type']='OperatingSystemType';
return $ele;
}

/**
 * Version identifier of software release.
 */
public function Version()
{
$ele['type']='VersionType';
return $ele;
}

}
?>