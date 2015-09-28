<?php

/**
 * Class CoreAttrgroup
 */

class CoreAttrgroup extends AnimlAppModel
{
public $useTable = false;

/**
 * Common attributes for sample references.
 */
public function SampleAttributes()
{
// Role this sample plays within the current ExperimentStep.
$att['att'][0]=['name'=>'role','type'=>'ShortTokenType','use'=>'required'];
// Specifies whether the referenced sample is produced or consumed by the current ExperimentStep.
$att['att'][1]=['name'=>'samplePurpose','type'=>'SamplePurposeType','use'=>'required'];
return $att;
}

/**
 * Common attributes for experiment data references
 */
public function ExperimentDataAttributes()
{
$att['att'][0]=['name'=>'role','type'=>'ShortTokenType','use'=>'required'];
$att['att'][1]=['name'=>'dataPurpose','type'=>'ExperimentDataPurposeType','use'=>'required'];
return $att;
}

/**
 * Start and End Index for this Set of Values (zero-based).
 */
public function ValueSet()
{
// Zero-based index of the first entry in this Value Set. The specification is inclusive.
$att['att'][0]=['name'=>'startIndex','type'=>'NonNegativeIntType','use'=>'optional'];
// Zero-based index of the last entry in this Value Set. The specification is inclusive.
$att['att'][1]=['name'=>'endIndex','type'=>'NonNegativeIntType','use'=>'optional'];
return $att;
}

/**
 * ID for Signable Items.
 */
public function SignableItem()
{
// Anchor point for digital signature. This identifier is referred to from the "Reference" element in a Signature. Unique per document.
$att['att'][0]=['name'=>'id','type'=>'xsd:ID','use'=>'optional'];
return $att;
}

/**
 * ID and Name attribute for Signable Items.
 */
public function SignableItemWithName()
{
// Plain-text name of this item.
$att['att'][0]=['name'=>'name','type'=>'ShortTokenType','use'=>'required'];
return $att;
}

/**
 * Attribute group which points to the original data source.
 */
public function SourceDataLocation()
{
// Points to the original data source. May be a file name, uri, database ID, etc.
$att['att'][0]=['name'=>'sourceDataLocation','type'=>'ShortStringType','use'=>'optional'];
return $att;
}

}
?>