Clazz.declarePackage("JS");
Clazz.load(null, "JS.SymmetryInfo", ["JU.PT", "JS.SpaceGroup", "$.SymmetryOperation", "JU.SimpleUnitCell"], function(){
var c$ = Clazz.decorateAsClass(function(){
this.coordinatesAreFractional = false;
this.isMultiCell = false;
this.sgName = null;
this.symmetryOperations = null;
this.additionalOperations = null;
this.infoStr = null;
this.cellRange = null;
this.latticeType = 'P';
this.intlTableNo = null;
this.intlTableNoFull = null;
Clazz.instantialize(this, arguments);}, JS, "SymmetryInfo", null);
/*LV!1824 unnec constructor*/Clazz.defineMethod(c$, "setSymmetryInfo", 
function(info, unitCellParams, sg){
var symmetryCount;
if (sg == null) {
this.cellRange = info.get("unitCellRange");
this.sgName = info.get("spaceGroup");
if (this.sgName == null || this.sgName === "") this.sgName = "spacegroup unspecified";
this.intlTableNo = info.get("intlTableNo");
this.intlTableNoFull = info.get("intlTableNoFull");
var s = info.get("latticeType");
this.latticeType = (s == null ? 'P' : s.charAt(0));
symmetryCount = info.containsKey("symmetryCount") ? (info.get("symmetryCount")).intValue() : 0;
this.symmetryOperations = info.remove("symmetryOps");
this.coordinatesAreFractional = info.containsKey("coordinatesAreFractional") ? (info.get("coordinatesAreFractional")).booleanValue() : false;
this.isMultiCell = (this.coordinatesAreFractional && this.symmetryOperations != null);
this.infoStr = "Spacegroup: " + this.sgName;
} else {
this.cellRange = null;
this.sgName = sg.getName();
this.intlTableNoFull = sg.intlTableNumberFull;
this.intlTableNo = sg.intlTableNumber;
this.latticeType = sg.latticeType;
symmetryCount = sg.getOperationCount();
this.symmetryOperations = sg.finalOperations;
this.coordinatesAreFractional = true;
this.infoStr = "Spacegroup: " + this.sgName;
}if (this.symmetryOperations != null) {
var c = "";
var s = "\nNumber of symmetry operations: " + (symmetryCount == 0 ? 1 : symmetryCount) + "\nSymmetry Operations:";
for (var i = 0; i < symmetryCount; i++) {
var op = this.symmetryOperations[i];
s += "\n" + op.fixMagneticXYZ(op, op.xyz, true);
if (op.isCenteringOp) c += " (" + JU.PT.rep(JU.PT.replaceAllCharacters(op.xyz, "xyz", "0"), "0+", "") + ")";
}
if (c.length > 0) this.infoStr += "\nCentering: " + c;
this.infoStr += s;
this.infoStr += "\n";
}if (unitCellParams == null) unitCellParams = info.get("unitCellParams");
unitCellParams = (JU.SimpleUnitCell.isValid(unitCellParams) ? unitCellParams : null);
if (unitCellParams == null) {
this.coordinatesAreFractional = false;
this.symmetryOperations = null;
this.cellRange = null;
this.infoStr = "";
}return unitCellParams;
}, "java.util.Map,~A,JS.SpaceGroup");
Clazz.defineMethod(c$, "getAdditionalOperations", 
function(){
if (this.additionalOperations == null && this.symmetryOperations != null) {
this.additionalOperations = JS.SymmetryOperation.getAdditionalOperations(this.symmetryOperations);
}return this.additionalOperations;
});
Clazz.defineMethod(c$, "getSpaceGroupFromOperators", 
function(){
return JS.SpaceGroup.determineSpaceGroupN(this.sgName);
});
});
;//5.0.1-v2 Mon Feb 19 09:32:38 CST 2024
