<?php

/**
 * Class CoreSimple
 */

class CoreSimple extends AnimlAppModel
{
public $useTable = false;

/**
 * Describes what kind of container the current sample is.
 */
public function ContainerTypeType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// This container holds exactly one sample (e.g. well, vial, tube).
$sim['res'][0]['opt'][]=['simple'];
// Positions within this container are precisely defined and known in advance (e.g. racks, ...)
$sim['res'][0]['opt'][]=['determinate'];
// Positions within this container are not precisely defined or known in advance (e.g. gels, surfaces)
$sim['res'][0]['opt'][]=['indeterminate'];
// Rectangular tray with unknown (or other) number of positions.
$sim['res'][0]['opt'][]=['rectangular tray'];
// Microtiter plate or tray with 6 positions.
$sim['res'][0]['opt'][]=['6 wells'];
// Microtiter plate or tray with 24 positions.
$sim['res'][0]['opt'][]=['24 wells'];
// Microtiter plate or tray with 96 positions.
$sim['res'][0]['opt'][]=['96 wells'];
// Microtiter plate or tray with 384 positions.
$sim['res'][0]['opt'][]=['384 wells'];
// Microtiter plate or tray with 1536 positions.
$sim['res'][0]['opt'][]=['1536 wells'];
return $sim;
}

/**
 * Date and time of modification.
 */
public function TimestampType()
{
$sim['res'][0]=['base'=>'xsd:dateTime'];
return $sim;
}

/**
 * Specifies the referenced entity is consumed or produced in an experiment.
 */
public function PurposeType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Specifies whether a sample is produced in an experiment.
$sim['res'][0]['opt'][]=['produced'];
// Specifies whether a sample is consumed in an experiment.
$sim['res'][0]['opt'][]=['consumed'];
return $sim;
}

/**
 * Specifies whether a sample is consumed or produced in an experiment.
 */
public function SamplePurposeType()
{
$sim['res'][0]=['base'=>'PurposeType'];
return $sim;
}

/**
 * Specifies whether the referenced ExperimentStep data is consumed or produced in an experiment.
 */
public function ExperimentDataPurposeType()
{
$sim['res'][0]=['base'=>'PurposeType'];
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
 * Quantity a Unit is applicable to.
 */
public function QuantityType()
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
 * Type of change made (created, modified, ...)
 */
public function ActionType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// The given user has created the references item(s).
$sim['res'][0]['opt'][]=['created'];
// Item already existed and has been modified. Modifications are explained in the Description element.
$sim['res'][0]['opt'][]=['modified'];
// Item has been converted into AnIML format.
$sim['res'][0]['opt'][]=['converted'];
// The given user has exercised read access on the referenced item(s).
$sim['res'][0]['opt'][]=['read'];
// The given user has attached a digital signature.
$sim['res'][0]['opt'][]=['signed'];
// The referenced items were deleted. No reference is specified. Description explains what was deleted.
$sim['res'][0]['opt'][]=['deleted'];
return $sim;
}

/**
 * Explanation why changes were made.
 */
public function ReasonType()
{
$sim['res'][0]=['base'=>'xsd:string'];
return $sim;
}

/**
 * Human-readable comment further explaining the changes.
 */
public function CommentType()
{
$sim['res'][0]=['base'=>'xsd:string'];
return $sim;
}

/**
 * Scope of diff. May be "element" or "attribute".
 */
public function ScopeType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// This diff describes the whole element before and after the change.
$sim['res'][0]['opt'][]=['element'];
// This diff only describes a change in attributes. The child elements remain unchanged (and are not reflected in the diff to save space).
$sim['res'][0]['opt'][]=['attributes'];
return $sim;
}

/**
 * ID of the SignableItem that was affected. If none is specified, entire document is covered.
 */
public function ReferenceType()
{
$sim['res'][0]=['base'=>'xsd:IDREF'];
return $sim;
}

/**
 * Specified whether the Series is independent or dependent.
 */
public function DependencyType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Specified that the Series is independent.
$sim['res'][0]['opt'][]=['independent'];
// Specified that the Series is dependent.
$sim['res'][0]['opt'][]=['dependent'];
return $sim;
}

/**
 * Specifies how the data in this Series is typically plotted.
 */
public function PlotScaleType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Specifies that this Series is typically plotted on a linear scale.
$sim['res'][0]['opt'][]=['linear'];
// Specifies that this Series is typically plotted on a common logarithmic scale (base 10).
$sim['res'][0]['opt'][]=['log'];
// Specifies that this Series is typically plotted on a natural logarithmic scale (base e).
$sim['res'][0]['opt'][]=['ln'];
// Specifies that this Series is not plottable.
$sim['res'][0]['opt'][]=['none'];
return $sim;
}

/**
 * Specifes whether a Series should be displayed by default.
 */
public function VisibleType()
{
$sim['res'][0]=['base'=>'xsd:boolean'];
return $sim;
}

/**
 * Individual integer value (32 bits, signed).
 */
public function Int32Type()
{
$sim['res'][0]=['base'=>'xsd:int'];
return $sim;
}

/**
 * Individual long integer value (64 bits, signed).
 */
public function Int64Type()
{
$sim['res'][0]=['base'=>'xsd:long'];
return $sim;
}

/**
 * Individual 32-bit floating point value.
 */
public function Float32Type()
{
$sim['res'][0]=['base'=>'xsd:float'];
return $sim;
}

/**
 * Individual 64-bit floating point value.
 */
public function Float64Type()
{
$sim['res'][0]=['base'=>'xsd:double'];
return $sim;
}

/**
 * Individual string value.
 */
public function StringType()
{
$sim['res'][0]=['base'=>'xsd:string'];
return $sim;
}

/**
 * Individual boolean value.
 */
public function BooleanType()
{
$sim['res'][0]=['base'=>'xsd:boolean'];
return $sim;
}

/**
 * Individual ISO date/time value.
 */
public function DateTimeType()
{
$sim['res'][0]=['base'=>'xsd:dateTime'];
return $sim;
}

/**
 * Base 64 encoded PNG image.
 */
public function PNGType()
{
$sim['res'][0]=['base'=>'xsd:base64Binary'];
return $sim;
}

/**
 * Value governed by a different XML Schema.
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
$sim['res'][0]=['base'=>'EmbeddedXMLType'];
return $sim;
}

/**
 * Names of Data Types for Parameters
 */
public function ParameterTypeType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Represents an individual integer value (32 bits, signed).
$sim['res'][0]['opt'][]=['Int32'];
// Represents an individual long integer value (64 bits, signed).
$sim['res'][0]['opt'][]=['Int64'];
// Represents an individual 32-bit floating point value.
$sim['res'][0]['opt'][]=['Float32'];
// Represents an individual 64-bit floating point value.
$sim['res'][0]['opt'][]=['Float64'];
// Represents an individual string value.
$sim['res'][0]['opt'][]=['String'];
// Represents an individual Boolean value.
$sim['res'][0]['opt'][]=['Boolean'];
// Represents an individual ISO date/time value.
$sim['res'][0]['opt'][]=['DateTime'];
// Represents a Value governed by a different XML Schema..
$sim['res'][0]['opt'][]=['EmbeddedXML'];
// Base 64 encoded PNG image
$sim['res'][0]['opt'][]=['PNG'];
// Value governed by the SVG DTD. Used to represent vector graphic images.
$sim['res'][0]['opt'][]=['SVG'];
return $sim;
}

/**
 * Names of Data Types for Series.
 */
public function SeriesTypeType()
{
$sim['res'][0]=['base'=>'ParameterTypeType'];
return $sim;
}

/**
 * Specifies whether a user is a real person, a device, or a software program.
 */
public function UserTypeType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
// Specifies that the user is a real person.
$sim['res'][0]['opt'][]=['human'];
// Specifies that the user is a device.
$sim['res'][0]['opt'][]=['device'];
// Specifies that the user is a software system.
$sim['res'][0]['opt'][]=['software'];
return $sim;
}

/**
 * Common name.
 */
public function NameType()
{
$sim['res'][0]=['base'=>'ShortStringType'];
return $sim;
}

/**
 * Organization the Author is affiliated with.
 */
public function AffiliationType()
{
$sim['res'][0]=['base'=>'ShortStringType'];
return $sim;
}

/**
 * Role the Author plays within the organization.
 */
public function RoleType()
{
$sim['res'][0]=['base'=>'ShortStringType'];
return $sim;
}

/**
 * RFC822-compliant email address.
 */
public function EmailType()
{
$sim['res'][0]=['base'=>'ShortStringType'];
return $sim;
}

/**
 * Phone number.
 */
public function PhoneType()
{
$sim['res'][0]=['base'=>'ShortStringType'];
return $sim;
}

/**
 * Location or physical address.
 */
public function LocationType()
{
$sim['res'][0]=['base'=>'ShortStringType'];
return $sim;
}

/**
 * Unique name or identifier of the device.
 */
public function DeviceIdentifierType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
return $sim;
}

/**
 * Company name.
 */
public function ManufacturerType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
return $sim;
}

/**
 * Version identifier of firmware release.
 */
public function FirmwareVersionType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
return $sim;
}

/**
 * Unique serial number of device.
 */
public function SerialNumberType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
return $sim;
}

/**
 * Operating system the software was running on.
 */
public function OperatingSystemType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
return $sim;
}

/**
 * Version identifier of software release.
 */
public function VersionType()
{
$sim['res'][0]=['base'=>'ShortTokenType'];
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

/**
 * Individual non-negative Integer Value (32 bits, signed)
 */
public function NonNegativeIntType()
{
$sim['res'][0]=['base'=>'xsd:int'];
$sim['res'][0]['mii'][0]=['0'];
return $sim;
}

}
?>