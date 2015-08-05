<?php

/**
 * Class TechSimple
 */

class TechSimple extends AnimlAppModel
{
public $useTable = false;

/**
 * Does the Series represent an independent or dependent variable?
 */
public function DependencyType()
{
$sim['res'][0]=['base'=>'xsd:token'];
// Specifies that the Series represents an independent variable.
$sim['res'][0]['opt'][]=['independent'];
// Specifies that the Series represents a dependent variable..
$sim['res'][0]['opt'][]=['dependent'];
return $sim;
}

/**
 * Specifies how the data in this Series is typically plotted.
 */
public function PlotScaleType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Specifies that the Series data is typically plotted on a linear scale.
$sim['res'][0]['opt'][]=['linear'];
// Specifies that the Series data is typically plotted on a common logarithmic scale (base 10).
$sim['res'][0]['opt'][]=['log'];
// Specifies that the Series data is typically plotted on a natural logarithmic scale (base e).
$sim['res'][0]['opt'][]=['ln'];
// Specifies that the Series data is typically not plotted.
$sim['res'][0]['opt'][]=['none'];
return $sim;
}

/**
 * Data type which indicates whether a sample or ExperimentStep is consumed or produced in an experiment.
 */
public function PurposeType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Indicates that a sample is produced in an experiment.
$sim['res'][0]['opt'][]=['produced'];
// Indicates that a sample is consumed in an experiment.
$sim['res'][0]['opt'][]=['consumed'];
return $sim;
}

/**
 * Names of Data types usable for Series.
 */
public function SeriesTypeType()
{
$sim['res'][0]=['base'=>'AllTypeNameList'];
return $sim;
}

/**
 * Names of Data types usable for Parameters.
 */
public function ParameterTypeType()
{
$sim['res'][0]=['base'=>'AllTypeNameList'];
return $sim;
}

/**
 * String with two allowed values: "required" and "optional".
 */
public function ModalityType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Specifies that the corresponding entity is required.
$sim['res'][0]['opt'][]=['required'];
// Specifies that the corresponding entity is optional.
$sim['res'][0]['opt'][]=['optional'];
return $sim;
}

/**
 * Represents the list value "unbounded".
 */
public function UnboundedType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
$sim['res'][0]['opt'][]=['unbounded'];
return $sim;
}

/**
 * A positive Integer or "unbounded" (see UnboundedType)
 */
public function MaxOccursType()
{
$sim['unn'][0]=['PositiveIntType UnboundedType'];
return $sim;
}

/**
 * String representation of a particular Unit.
 */
public function LabelType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
$sim['res'][0]['mil'][0]=['1'];
return $sim;
}

/**
 * Human-readable name of a Quantity.
 */
public function QuantityNameType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
$sim['res'][0]['mil'][0]=['1'];
return $sim;
}

/**
 * Names of all SI Units
 */
public function SIUnitNameList()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Represents a dimensionless unit.
$sim['res'][0]['opt'][]=['1'];
// Represents the SI unit "meter".
$sim['res'][0]['opt'][]=['m'];
// Represents the SI unit "kilogram".
$sim['res'][0]['opt'][]=['kg'];
// Represents the SI unit "second".
$sim['res'][0]['opt'][]=['s'];
// Represents the SI unit "ampere".
$sim['res'][0]['opt'][]=['A'];
// Represents the SI unit "kelvin".
$sim['res'][0]['opt'][]=['K'];
// Represents the SI unit "mol".
$sim['res'][0]['opt'][]=['mol'];
// Represents the SI unit "candela".
$sim['res'][0]['opt'][]=['cd'];
return $sim;
}

/**
 * Enumeration of data types used in AnIML.
 */
public function AllTypeNameList()
{
$sim['res'][0]=['base'=>'xsd:token'];
// Single 32-bit or 64-bit signed integer value.
$sim['res'][0]['opt'][]=['Int'];
// Single 32-bit or 64-bit floating point value.
$sim['res'][0]['opt'][]=['Float'];
// Single 32-bit or 64-bit integer or floating point value.
$sim['res'][0]['opt'][]=['Numeric'];
// Single string value.
$sim['res'][0]['opt'][]=['String'];
// Single Boolean value.
$sim['res'][0]['opt'][]=['Boolean'];
// Single ISO date and time Value.
$sim['res'][0]['opt'][]=['DateTime'];
// Single XML value governed by a non-AnIML XML schema.
$sim['res'][0]['opt'][]=['EmbeddedXML'];
// Single Base64 binary encoded PNG image.
$sim['res'][0]['opt'][]=['PNG'];
// Value governed by the SVG DTD. Used to represent vector graphic images.
$sim['res'][0]['opt'][]=['SVG'];
return $sim;
}

/**
 * Single 32-bit signed integer value.
 */
public function Int32Type()
{
$sim['res'][0]=['base'=>'xsd:int'];
return $sim;
}

/**
 * Single 32-bit positive integer value.
 */
public function PositiveIntType()
{
$sim['res'][0]=['base'=>'xsd:int'];
$sim['res'][0]['mii'][0]=['1'];
return $sim;
}

/**
 * Single 64-bit signed integer value.
 */
public function Int64Type()
{
$sim['res'][0]=['base'=>'xsd:long'];
return $sim;
}

/**
 * Single 32-bit floating point value.
 */
public function Float32Type()
{
$sim['res'][0]=['base'=>'xsd:float'];
return $sim;
}

/**
 * Single 64-bit floating point value.
 */
public function Float64Type()
{
$sim['res'][0]=['base'=>'xsd:double'];
return $sim;
}

/**
 * Single string value.
 */
public function StringType()
{
$sim['res'][0]=['base'=>'xsd:string'];
return $sim;
}

/**
 * Single Boolean value.
 */
public function BooleanType()
{
$sim['res'][0]=['base'=>'xsd:boolean'];
return $sim;
}

/**
 * Single ISO date and time value.
 */
public function DateTimeType()
{
$sim['res'][0]=['base'=>'xsd:dateTime'];
return $sim;
}

/**
 * Base64 binary encoded PNG image.
 */
public function PNGType()
{
$sim['res'][0]=['base'=>'xsd:base64Binary'];
return $sim;
}

/**
 * Value governed by a non-AnIML XML Schema.
 */
public function EmbeddedXMLType()
{
$sim['res'][0]=['base'=>'xsd:string'];
return $sim;
}

/**
 * Value governed by the SVG DTD. Used to represent vector graphic images.
 */
public function SVGType()
{
$sim['res'][0]=['base'=>'xsd:string'];
return $sim;
}

/**
 * String with up to 1024 characters
 */
public function ShortStringType()
{
$sim['res'][0]=['base'=>'xsd:string'];
$sim['res'][0]['mxl'][0]=['1024'];
return $sim;
}

/**
 * Token with up to 1024 characters
 */
public function ShortTokenType()
{
$sim['res'][0]=['base'=>'xsd:token'];
$sim['res'][0]['mxl'][0]=['1024'];
return $sim;
}

}
?>