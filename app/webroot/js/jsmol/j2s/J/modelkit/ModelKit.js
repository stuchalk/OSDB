Clazz.declarePackage ("J.modelkit");
Clazz.load (["JU.BS", "$.P3", "J.i18n.GT"], "J.modelkit.ModelKit", ["java.lang.Boolean", "$.Float", "$.IllegalArgumentException", "java.util.Arrays", "$.Hashtable", "JU.AU", "$.Lst", "$.Measure", "$.P4", "$.PT", "$.SB", "$.V3", "JM.Atom", "JS.SV", "JU.BSUtil", "$.Edge", "$.Elements", "$.Escape", "$.Logger", "$.SimpleUnitCell", "JV.Viewer"], function () {
c$ = Clazz.decorateAsClass (function () {
this.vwr = null;
this.menu = null;
this.state = 0;
this.rotationDeg = 0;
this.atomHoverLabel = "C";
this.bondHoverLabel = J.i18n.GT.$ ("increase order");
this.xtalHoverLabel = null;
this.hasUnitCell = false;
this.allOperators = null;
this.currentModelIndex = -1;
this.alertedNoEdit = false;
this.lastModelSet = null;
this.pickAtomAssignType = "C";
this.lastElementType = "C";
this.pickBondAssignType = 'p';
this.$isPickAtomAssignCharge = false;
this.bsHighlight = null;
this.bondIndex = -1;
this.bondAtomIndex1 = -1;
this.bondAtomIndex2 = -1;
this.bsRotateBranch = null;
this.branchAtomIndex = 0;
this.isRotateBond = false;
this.screenXY = null;
this.showSymopInfo = true;
this.addXtalHydrogens = true;
this.clickToSetElement = true;
this.autoBond = false;
this.centerPoint = null;
this.spherePoint = null;
this.viewOffset = null;
this.centerDistance = 0;
this.symop = null;
this.centerAtomIndex = -1;
this.secondAtomIndex = -1;
this.atomIndexSphere = -1;
this.drawData = null;
this.drawScript = null;
this.iatom0 = 0;
this.bondRotationName = ".modelkitMenu.bondMenu.rotateBondP!RD";
this.lastCenter = "0 0 0";
this.lastOffset = "0 0 0";
this.a0 = null;
this.a3 = null;
this.constraint = null;
this.atomConstraints = null;
Clazz.instantialize (this, arguments);
}, J.modelkit, "ModelKit");
Clazz.prepareFields (c$, function () {
this.bsHighlight =  new JU.BS ();
this.screenXY =  Clazz.newIntArray (2, 0);
});
Clazz.makeConstructor (c$, 
function () {
});
Clazz.defineMethod (c$, "setMenu", 
function (menu) {
this.menu = menu;
this.vwr = menu.vwr;
menu.modelkit = this;
this.initializeForModel ();
}, "J.modelkit.ModelKitPopup");
Clazz.defineMethod (c$, "initializeForModel", 
function () {
this.resetBondFields ();
this.allOperators = null;
this.currentModelIndex = -999;
this.iatom0 = 0;
this.atomIndexSphere = this.centerAtomIndex = this.secondAtomIndex = -1;
this.centerPoint = this.spherePoint = null;
this.symop = null;
this.setDefaultState (0);
});
Clazz.defineMethod (c$, "showMenu", 
function (x, y) {
this.menu.jpiShow (x, y);
}, "~N,~N");
Clazz.defineMethod (c$, "getDefaultModel", 
function () {
return (this.addXtalHydrogens ? "5\n\nC 0 0 0\nH .63 .63 .63\nH -.63 -.63 .63\nH -.63 .63 -.63\nH .63 -.63 -.63" : "1\n\nC 0 0 0\n");
});
Clazz.defineMethod (c$, "updateMenu", 
function () {
this.menu.jpiUpdateComputedMenus ();
});
Clazz.defineMethod (c$, "dispose", 
function () {
this.menu.jpiDispose ();
this.menu.modelkit = null;
this.menu = null;
this.vwr = null;
});
Clazz.defineMethod (c$, "isPickAtomAssignCharge", 
function () {
return this.$isPickAtomAssignCharge;
});
Clazz.defineMethod (c$, "isHidden", 
function () {
return this.menu.hidden;
});
Clazz.defineMethod (c$, "getActiveMenu", 
function () {
return this.menu.activeMenu;
});
Clazz.defineMethod (c$, "getRotateBondIndex", 
function () {
return (this.getMKState () == 0 && this.isRotateBond ? this.bondIndex : -1);
});
Clazz.defineMethod (c$, "getProperty", 
function (name) {
name = name.toLowerCase ().intern ();
if (name === "constraint") {
return this.constraint;
}if (name === "ismolecular") {
return Boolean.$valueOf (this.getMKState () == 0);
}if (name === "alloperators") {
return this.allOperators;
}if (name === "data") {
return this.getinfo ();
}return this.setProperty (name, null);
}, "~S");
Clazz.defineMethod (c$, "setProperty", 
function (key, value) {
try {
key = key.toLowerCase ().intern ();
if (key === "constraint") {
this.constraint = null;
this.clearAtomConstraints ();
var o = value;
if (o != null) {
var v1 = o[0];
var v2 = o[1];
var plane = o[2];
if (v1 != null && v2 != null) {
this.constraint =  new J.modelkit.ModelKit.Constraint (null, 4,  Clazz.newArray (-1, [v1, v2]));
} else if (plane != null) {
this.constraint =  new J.modelkit.ModelKit.Constraint (null, 5,  Clazz.newArray (-1, [plane]));
} else if (v1 != null) this.constraint =  new J.modelkit.ModelKit.Constraint (null, 6, null);
}return null;
}if (key === "reset") {
return null;
}if (key === "addhydrogen" || key === "addhydrogens") {
if (value != null) this.addXtalHydrogens = J.modelkit.ModelKit.isTrue (value);
return Boolean.$valueOf (this.addXtalHydrogens);
}if (key === "autobond") {
if (value != null) this.autoBond = J.modelkit.ModelKit.isTrue (value);
return Boolean.$valueOf (this.autoBond);
}if (key === "clicktosetelement") {
if (value != null) this.clickToSetElement = J.modelkit.ModelKit.isTrue (value);
return Boolean.$valueOf (this.clickToSetElement);
}if (key === "hidden") {
if (value != null) {
this.menu.hidden = J.modelkit.ModelKit.isTrue (value);
this.vwr.setBooleanProperty ("modelkitMode", true);
}return Boolean.$valueOf (this.menu.hidden);
}if (key === "showsymopinfo") {
if (value != null) this.showSymopInfo = J.modelkit.ModelKit.isTrue (value);
return Boolean.$valueOf (this.showSymopInfo);
}if (key === "symop") {
this.setDefaultState (1);
if (value != null) {
this.symop = value;
this.showSymop (this.symop);
}return this.symop;
}if (key === "atomtype") {
if (value != null) {
this.pickAtomAssignType = value;
this.$isPickAtomAssignCharge = (this.pickAtomAssignType.equals ("pl") || this.pickAtomAssignType.equals ("mi"));
if (!this.$isPickAtomAssignCharge && !"X".equals (this.pickAtomAssignType)) {
this.lastElementType = this.pickAtomAssignType;
}}return this.pickAtomAssignType;
}if (key === "bondtype") {
if (value != null) {
var s = (value).substring (0, 1).toLowerCase ();
if (" 012345pm".indexOf (s) > 0) this.pickBondAssignType = s.charAt (0);
this.isRotateBond = false;
}return "" + this.pickBondAssignType;
}if (key === "bondindex") {
if (value != null) {
this.setBondIndex ((value).intValue (), false);
}return (this.bondIndex < 0 ? null : Integer.$valueOf (this.bondIndex));
}if (key === "rotatebondindex") {
if (value != null) {
this.setBondIndex ((value).intValue (), true);
}return (this.bondIndex < 0 ? null : Integer.$valueOf (this.bondIndex));
}if (key === "offset") {
if (value === "none") {
this.viewOffset = null;
} else if (value != null) {
this.viewOffset = (Clazz.instanceOf (value, JU.P3) ? value : J.modelkit.ModelKit.pointFromTriad (value.toString ()));
if (this.viewOffset != null) this.setSymViewState (8);
}this.showXtalSymmetry ();
return this.viewOffset;
}if (key === "screenxy") {
if (value != null) {
this.screenXY = value;
}return this.screenXY;
}if (key === "bondatomindex") {
var i = (value).intValue ();
if (i != this.bondAtomIndex2) this.bondAtomIndex1 = i;
this.bsRotateBranch = null;
return null;
}if (key === "highlight") {
if (value == null) this.bsHighlight =  new JU.BS ();
 else this.bsHighlight = value;
return null;
}if (key === "mode") {
var isEdit = ("edit".equals (value));
this.setMKState ("view".equals (value) ? 1 : isEdit ? 2 : 0);
if (isEdit) this.addXtalHydrogens = false;
return null;
}if (key === "symmetry") {
this.setDefaultState (2);
key = (value).toLowerCase ().intern ();
this.setSymEdit (key === "applylocal" ? 32 : key === "retainlocal" ? 64 : key === "applyfull" ? 128 : 0);
this.showXtalSymmetry ();
return null;
}if (key === "unitcell") {
var isPacked = "packed".equals (value);
this.setUnitCell (isPacked ? 0 : 256);
this.viewOffset = (isPacked ? J.modelkit.ModelKit.Pt000 : null);
return null;
}if (key === "center") {
this.setDefaultState (1);
this.centerPoint = value;
this.lastCenter = this.centerPoint.x + " " + this.centerPoint.y + " " + this.centerPoint.z;
this.centerAtomIndex = (Clazz.instanceOf (this.centerPoint, JM.Atom) ? (this.centerPoint).i : -1);
this.atomIndexSphere = -1;
this.secondAtomIndex = -1;
this.processAtomClick (this.centerAtomIndex);
return null;
}if (key === "scriptassignbond") {
this.appRunScript ("modelkit assign bond [{" + value + "}] \"" + this.pickBondAssignType + "\"");
return null;
}if (key === "hoverlabel") {
return this.getHoverLabel ((value).intValue ());
}if (key === "invariant") {
var iatom = (Clazz.instanceOf (value, JU.BS) ? (value).nextSetBit (0) : -1);
var atom = this.vwr.ms.getAtom (iatom);
return (atom == null ? null : this.vwr.getSymmetryInfo (iatom, null, -1, null, atom, atom, 1275068418, null, 0, 0, 0));
}if (key === "distance") {
this.setDefaultState (2);
var d = (value == null ? NaN : Clazz.instanceOf (value, Float) ? (value).floatValue () : JU.PT.parseFloat (value));
if (!Float.isNaN (d)) {
J.modelkit.ModelKit.notImplemented ("setProperty: distance");
this.centerDistance = d;
}return Float.$valueOf (this.centerDistance);
}if (key === "point") {
if (value != null) {
J.modelkit.ModelKit.notImplemented ("setProperty: point");
this.setDefaultState (2);
this.spherePoint = value;
this.atomIndexSphere = (Clazz.instanceOf (this.spherePoint, JM.Atom) ? (this.spherePoint).i : -1);
}return this.spherePoint;
}if (key === "addconstraint") {
J.modelkit.ModelKit.notImplemented ("setProperty: addConstraint");
return null;
}if (key === "removeconstraint") {
J.modelkit.ModelKit.notImplemented ("setProperty: removeConstraint");
return null;
}if (key === "removeallconstraints") {
J.modelkit.ModelKit.notImplemented ("setProperty: removeAllConstraints");
return null;
}System.err.println ("ModelKitPopup.setProperty? " + key + " " + value);
} catch (e) {
if (Clazz.exceptionOf (e, Exception)) {
return "?";
} else {
throw e;
}
}
return null;
}, "~S,~O");
Clazz.defineMethod (c$, "setBondMeasure", 
function (bi, mp) {
if (this.branchAtomIndex < 0) return null;
var b = this.vwr.ms.bo[bi];
var a1 = b.atom1;
var a2 = b.atom2;
this.a0 = this.a3 = null;
if (a1.getCovalentBondCount () == 1 || a2.getCovalentBondCount () == 1) return null;
mp.addPoint ((this.a0 = this.getOtherAtomIndex (a1, a2)).i, null, true);
mp.addPoint (a1.i, null, true);
mp.addPoint (a2.i, null, true);
mp.addPoint ((this.a3 = this.getOtherAtomIndex (a2, a1)).i, null, true);
mp.mad = 50;
mp.inFront = true;
return mp;
}, "~N,JM.MeasurementPending");
Clazz.defineMethod (c$, "actionRotateBond", 
function (deltaX, deltaY, x, y, forceFull) {
if (this.bondIndex < 0) return;
var bsBranch = this.bsRotateBranch;
var atomFix;
var atomMove;
var ms = this.vwr.ms;
var b = ms.bo[this.bondIndex];
if (forceFull) {
bsBranch = null;
this.branchAtomIndex = -1;
}if (bsBranch == null) {
atomMove = (this.branchAtomIndex == b.atom1.i ? b.atom1 : b.atom2);
atomFix = (atomMove === b.atom1 ? b.atom2 : b.atom1);
this.vwr.undoMoveActionClear (atomFix.i, 2, true);
if (this.branchAtomIndex >= 0) bsBranch = this.vwr.getBranchBitSet (atomMove.i, atomFix.i, true);
if (bsBranch != null) for (var n = 0, i = atomFix.bonds.length; --i >= 0; ) {
if (bsBranch.get (atomFix.getBondedAtomIndex (i)) && ++n == 2) {
bsBranch = null;
break;
}}
if (bsBranch == null) {
bsBranch = ms.getMoleculeBitSetForAtom (atomFix.i);
forceFull = true;
}this.bsRotateBranch = bsBranch;
this.bondAtomIndex1 = atomFix.i;
this.bondAtomIndex2 = atomMove.i;
} else {
atomFix = ms.at[this.bondAtomIndex1];
atomMove = ms.at[this.bondAtomIndex2];
}var v1 = JU.V3.new3 (atomMove.sX - atomFix.sX, atomMove.sY - atomFix.sY, 0);
v1.scale (1 / v1.length ());
var v2 = JU.V3.new3 (deltaX, deltaY, 0);
v1.cross (v1, v2);
var f = (v1.z > 0 ? 1 : -1);
var degrees = f * (Clazz.doubleToInt (Clazz.floatToInt (v2.length ()) / 2) + 1);
if (!forceFull && this.a0 != null) {
var ang0 = JU.Measure.computeTorsion (this.a0, b.atom1, b.atom2, this.a3, true);
var ang1 = Math.round (ang0 + degrees);
degrees = ang1 - ang0;
}var bs = JU.BSUtil.copy (bsBranch);
bs.andNot (this.vwr.slm.getMotionFixedAtoms ());
this.vwr.rotateAboutPointsInternal (null, atomFix, atomMove, 0, degrees, false, bs, null, null, null, null);
}, "~N,~N,~N,~N,~B");
Clazz.defineMethod (c$, "handleAssignNew", 
function (pressed, dragged, mp, dragAtomIndex) {
var inRange = pressed.inRange (10, dragged.x, dragged.y);
if (inRange) {
dragged.x = pressed.x;
dragged.y = pressed.y;
}if (this.handleDragAtom (pressed, dragged, mp.countPlusIndices)) return true;
var isCharge = this.$isPickAtomAssignCharge;
var atomType = this.pickAtomAssignType;
if (mp.count == 2) {
this.vwr.undoMoveActionClear (-1, 4146, true);
if ((mp.getAtom (1)).isBonded (mp.getAtom (2))) {
this.appRunScript ("modelkit assign bond " + mp.getMeasurementScript (" ", false) + "'p'");
} else {
this.appRunScript ("modelkit connect " + mp.getMeasurementScript (" ", false));
}} else {
if (atomType.equals ("Xx")) {
atomType = this.lastElementType;
}if (inRange) {
var s = "modelkit assign atom ({" + dragAtomIndex + "}) \"" + atomType + "\" true";
if (isCharge) {
s += ";{atomindex=" + dragAtomIndex + "}.label='%C'; ";
this.vwr.undoMoveActionClear (dragAtomIndex, 4, true);
} else {
this.vwr.undoMoveActionClear (-1, 4146, true);
}this.appRunScript (s);
} else if (!isCharge) {
this.vwr.undoMoveActionClear (-1, 4146, true);
var a = this.vwr.ms.at[dragAtomIndex];
if (a.getElementNumber () == 1) {
this.assignAtomClick (dragAtomIndex, "X", null);
} else {
var x = dragged.x;
var y = dragged.y;
if (this.vwr.antialiased) {
x <<= 1;
y <<= 1;
}var ptNew = JU.P3.new3 (x, y, a.sZ);
this.vwr.tm.unTransformPoint (ptNew, ptNew);
this.assignAtomClick (dragAtomIndex, atomType, ptNew);
}}}return true;
}, "JV.MouseState,JV.MouseState,JM.MeasurementPending,~N");
Clazz.defineMethod (c$, "isXtalState", 
function () {
return ((this.state & 3) != 0);
});
Clazz.defineMethod (c$, "setMKState", 
function (bits) {
this.state = (this.state & -4) | (this.hasUnitCell ? bits : 0);
}, "~N");
Clazz.defineMethod (c$, "getMKState", 
function () {
return this.state & 3;
});
Clazz.defineMethod (c$, "setSymEdit", 
function (bits) {
this.state = (this.state & -225) | bits;
}, "~N");
Clazz.defineMethod (c$, "getSymEditState", 
function () {
return this.state & 224;
});
Clazz.defineMethod (c$, "setSymViewState", 
function (bits) {
this.state = (this.state & -29) | bits;
}, "~N");
Clazz.defineMethod (c$, "getSymViewState", 
function () {
return this.state & 28;
});
Clazz.defineMethod (c$, "setUnitCell", 
function (bits) {
this.state = (this.state & -1793) | bits;
}, "~N");
Clazz.defineMethod (c$, "getUnitCellState", 
function () {
return this.state & 1792;
});
Clazz.defineMethod (c$, "exitBondRotation", 
function (text) {
this.isRotateBond = false;
if (text != null) this.bondHoverLabel = text;
this.vwr.highlight (null);
this.vwr.setPickingMode (null, 33);
}, "~S");
Clazz.defineMethod (c$, "resetBondFields", 
function () {
this.bsRotateBranch = null;
this.branchAtomIndex = this.bondAtomIndex1 = this.bondAtomIndex2 = -1;
});
Clazz.defineMethod (c$, "processXtalClick", 
function (id, action) {
if (this.processSymop (id, false)) return;
action = action.intern ();
if (action.startsWith ("mkmode_")) {
if (!this.alertedNoEdit && action === "mkmode_edit") {
this.alertedNoEdit = true;
this.vwr.alert ("ModelKit xtal edit has not been implemented");
return;
}this.processModeClick (action);
} else if (action.startsWith ("mksel_")) {
this.processSelClick (action);
} else if (action.startsWith ("mkselop_")) {
while (action != null) action = this.processSelOpClick (action);

} else if (action.startsWith ("mksymmetry_")) {
this.processSymClick (action);
} else if (action.startsWith ("mkunitcell_")) {
this.processUCClick (action);
} else {
J.modelkit.ModelKit.notImplemented ("XTAL click " + action);
}this.menu.updateAllXtalMenuOptions ();
}, "~S,~S");
Clazz.defineMethod (c$, "processSymop", 
function (id, isFocus) {
var pt = id.indexOf (".mkop_");
if (pt >= 0) {
var op = this.symop;
this.symop = Integer.$valueOf (id.substring (pt + 6));
this.showSymop (this.symop);
if (isFocus) this.symop = op;
return true;
}return false;
}, "~S,~B");
Clazz.defineMethod (c$, "setDefaultState", 
function (mode) {
if (!this.hasUnitCell) mode = 0;
if (!this.hasUnitCell || this.isXtalState () != this.hasUnitCell) {
this.setMKState (mode);
switch (mode) {
case 0:
break;
case 1:
if (this.getSymViewState () == 0) this.setSymViewState (8);
break;
case 2:
break;
}
}}, "~N");
Clazz.defineMethod (c$, "getAllOperators", 
function () {
if (this.allOperators != null) return this.allOperators;
var data = this.runScriptBuffered ("show symop");
this.allOperators = JU.PT.split (data.trim ().$replace ('\t', ' '), "\n");
return this.allOperators;
});
Clazz.defineMethod (c$, "setHasUnitCell", 
function () {
return this.hasUnitCell = (this.vwr.getOperativeSymmetry () != null);
});
Clazz.defineMethod (c$, "checkNewModel", 
function () {
var isNew = false;
if (this.vwr.ms !== this.lastModelSet) {
this.lastModelSet = this.vwr.ms;
isNew = true;
}this.currentModelIndex = Math.max (this.vwr.am.cmi, 0);
this.iatom0 = this.vwr.ms.am[this.currentModelIndex].firstAtomIndex;
return isNew;
});
Clazz.defineMethod (c$, "getSymopText", 
function () {
return (this.symop == null || this.allOperators == null ? null : Clazz.instanceOf (this.symop, Integer) ? this.allOperators[(this.symop).intValue () - 1] : this.symop.toString ());
});
Clazz.defineMethod (c$, "getCenterText", 
function () {
return (this.centerAtomIndex < 0 && this.centerPoint == null ? null : this.centerAtomIndex >= 0 ? this.vwr.getAtomInfo (this.centerAtomIndex) : this.centerPoint.toString ());
});
Clazz.defineMethod (c$, "resetAtomPickType", 
function () {
this.pickAtomAssignType = this.lastElementType;
});
Clazz.defineMethod (c$, "setHoverLabel", 
function (activeMenu, text) {
if (text == null) return;
if (activeMenu === "bondMenu") {
this.bondHoverLabel = text;
} else if (activeMenu === "atomMenu") {
this.atomHoverLabel = text;
} else if (activeMenu === "xtalMenu") {
this.xtalHoverLabel = this.atomHoverLabel = text;
}}, "~S,~S");
Clazz.defineMethod (c$, "getElementFromUser", 
function () {
var element = this.promptUser (J.i18n.GT.$ ("Element?"), "");
return (element == null || JU.Elements.elementNumberFromSymbol (element, true) == 0 ? null : element);
});
Clazz.defineMethod (c$, "processMKPropertyItem", 
function (name, TF) {
name = name.substring (2);
var pt = name.indexOf ("_");
if (pt > 0) {
this.setProperty (name.substring (0, pt), name.substring (pt + 1));
} else {
this.setProperty (name, Boolean.$valueOf (TF));
}}, "~S,~B");
Clazz.defineMethod (c$, "assignAtom", 
 function (atomIndex, type, autoBond, addHsAndBond, isClick, bsAtoms) {
if (isClick) {
if (this.isVwrRotateBond ()) {
this.bondAtomIndex1 = atomIndex;
return -1;
}if (this.processAtomClick (atomIndex) || !this.clickToSetElement && this.vwr.ms.getAtom (atomIndex).getElementNumber () != 1) return -1;
}if (bsAtoms != null) {
var n = -1;
for (var i = bsAtoms.nextSetBit (0); i >= 0; i = bsAtoms.nextSetBit (i + 1)) {
n = this.assignAtom (i, type, autoBond, addHsAndBond, isClick, null);
}
return n;
}var atom = this.vwr.ms.at[atomIndex];
if (atom == null) return -1;
this.vwr.ms.clearDB (atomIndex);
if (type == null) type = "C";
var bs =  new JU.BS ();
var wasH = (atom.getElementNumber () == 1);
var atomicNumber = (type.equals ("X") ? -1 : type.equals ("Xx") ? 0 : JU.PT.isUpperCase (type.charAt (0)) ? JU.Elements.elementNumberFromSymbol (type, true) : -1);
var isDelete = false;
if (atomicNumber >= 0) {
var doTaint = (atomicNumber > 1 || !addHsAndBond);
this.vwr.ms.setElement (atom, atomicNumber, doTaint);
this.vwr.shm.setShapeSizeBs (0, 0, this.vwr.rd, JU.BSUtil.newAndSetBit (atomIndex));
this.vwr.ms.setAtomName (atomIndex, type + atom.getAtomNumber (), doTaint);
if (this.vwr.getBoolean (603983903)) this.vwr.ms.am[atom.mi].isModelKit = true;
if (!this.vwr.ms.am[atom.mi].isModelKit || atomicNumber > 1) this.vwr.ms.taintAtom (atomIndex, 0);
} else if (type.toLowerCase ().equals ("pl")) {
atom.setFormalCharge (atom.getFormalCharge () + 1);
} else if (type.toLowerCase ().equals ("mi")) {
atom.setFormalCharge (atom.getFormalCharge () - 1);
} else if (type.equals ("X")) {
isDelete = true;
} else if (!type.equals (".") || !this.addXtalHydrogens) {
return -1;
}if (!addHsAndBond) return atomicNumber;
this.vwr.ms.removeUnnecessaryBonds (atom, isDelete);
var dx = 0;
if (atom.getCovalentBondCount () == 1) if (wasH) {
dx = 1.50;
} else if (!wasH && atomicNumber == 1) {
dx = 1.0;
}if (dx != 0) {
var v = JU.V3.newVsub (atom, this.vwr.ms.at[atom.getBondedAtomIndex (0)]);
var d = v.length ();
v.normalize ();
v.scale (dx - d);
this.vwr.ms.setAtomCoordRelative (atomIndex, v.x, v.y, v.z);
}var bsA = JU.BSUtil.newAndSetBit (atomIndex);
if (isDelete) {
this.vwr.deleteAtoms (bsA, false);
}if (atomicNumber != 1 && autoBond) {
this.vwr.ms.validateBspf (false);
bs = this.vwr.ms.getAtomsWithinRadius (1.0, bsA, false, null);
bs.andNot (bsA);
if (bs.nextSetBit (0) >= 0) this.vwr.deleteAtoms (bs, false);
bs = this.vwr.getModelUndeletedAtomsBitSet (atom.mi);
bs.andNot (this.vwr.ms.getAtomBitsMDa (1612709900, null,  new JU.BS ()));
this.vwr.ms.makeConnections2 (0.1, 1.8, 1, 1073741904, bsA, bs, null, false, false, 0);
}if (this.addXtalHydrogens) this.vwr.addHydrogens (bsA, 1);
return atomicNumber;
}, "~N,~S,~B,~B,~B,JU.BS");
Clazz.defineMethod (c$, "assignBond", 
 function (bondIndex, type) {
var bondOrder = type.charCodeAt (0) - 48;
var bond = this.vwr.ms.bo[bondIndex];
this.vwr.ms.clearDB (bond.atom1.i);
switch (type) {
case '0':
case '1':
case '2':
case '3':
case '4':
case '5':
break;
case 'p':
case 'm':
bondOrder = JU.Edge.getBondOrderNumberFromOrder (bond.getCovalentOrder ()).charCodeAt (0) - 48 + (type == 'p' ? 1 : -1);
if (bondOrder > 3) bondOrder = 1;
 else if (bondOrder < 0) bondOrder = 3;
break;
default:
return null;
}
var bsAtoms =  new JU.BS ();
try {
if (bondOrder == 0) {
var bs =  new JU.BS ();
bs.set (bond.index);
bsAtoms.set (bond.atom1.i);
bsAtoms.set (bond.atom2.i);
this.vwr.ms.deleteBonds (bs, false);
} else {
bond.setOrder (bondOrder | 131072);
if (bond.atom1.getElementNumber () != 1 && bond.atom2.getElementNumber () != 1) {
this.vwr.ms.removeUnnecessaryBonds (bond.atom1, false);
this.vwr.ms.removeUnnecessaryBonds (bond.atom2, false);
}bsAtoms.set (bond.atom1.i);
bsAtoms.set (bond.atom2.i);
}} catch (e) {
if (Clazz.exceptionOf (e, Exception)) {
JU.Logger.error ("Exception in seBondOrder: " + e.toString ());
} else {
throw e;
}
}
if (type != '0' && this.addXtalHydrogens) this.vwr.addHydrogens (bsAtoms, 1);
return bsAtoms;
}, "~N,~S");
Clazz.defineMethod (c$, "cmdAssignSpaceGroup", 
function (bs, name) {
var isP1 = (name.equalsIgnoreCase ("P1") || name.equals ("1"));
this.clearAtomConstraints ();
try {
if (bs != null && bs.isEmpty ()) return "";
var uc = this.vwr.getOperativeSymmetry ();
if (uc == null) uc = this.vwr.getSymTemp ().setUnitCell ( Clazz.newFloatArray (-1, [10, 10, 10, 90, 90, 90]), false);
var bsAtoms = this.vwr.getThisModelAtoms ();
var bsCell = (isP1 ? bsAtoms : JS.SV.getBitSet (this.vwr.evaluateExpressionAsVariable ("{within(unitcell)}"), true));
if (bs == null) {
bs = bsAtoms;
}if (bs != null) {
bsAtoms.and (bs);
if (!isP1) bsAtoms.and (bsCell);
}var noAtoms = bsAtoms.isEmpty ();
var mi = (noAtoms ? 0 : this.vwr.ms.at[bsAtoms.nextSetBit (0)].getModelIndex ());
var m = uc.getUnitCellMultiplier ();
if (m != null && m.z == 1) {
m.z = 0;
}var supercell;
var oabc;
var ita;
var basis;
var sg = (noAtoms || isP1 ? null : this.vwr.findSpaceGroup (bsAtoms, null, false));
if (sg == null) {
name = "P1";
supercell = JU.P3.new3 (1, 1, 1);
oabc = uc.getUnitCellVectors ();
ita = "1";
basis = null;
} else {
supercell = sg.get ("supercell");
oabc = sg.get ("unitcell");
name = sg.get ("name");
ita = sg.get ("itaFull");
basis = sg.get ("basis");
}uc.getUnitCell (oabc, false, null);
uc.setSpaceGroupTo (ita);
uc.setSpaceGroupName (name);
if (basis == null) basis = uc.removeDuplicates (this.vwr.ms, bsAtoms);
this.vwr.ms.setSpaceGroup (mi, uc, basis);
var pt = JU.SimpleUnitCell.ptToIJK (supercell, 1);
this.vwr.ms.setUnitCellOffset (uc, pt, 0);
return name + " basis=" + basis;
} catch (e) {
if (Clazz.exceptionOf (e, Exception)) {
if (!JV.Viewer.isJS) e.printStackTrace ();
return e.getMessage ();
} else {
throw e;
}
}
}, "JU.BS,~S");
Clazz.defineMethod (c$, "cmdAssignDeleteAtoms", 
function (bs) {
this.clearAtomConstraints ();
bs.and (this.vwr.getThisModelAtoms ());
bs = this.vwr.ms.getSymmetryEquivAtoms (bs);
if (!bs.isEmpty ()) this.vwr.deleteAtoms (bs, false);
return bs.cardinality ();
}, "JU.BS");
Clazz.defineMethod (c$, "setBondIndex", 
 function (index, isRotate) {
if (!isRotate && this.isVwrRotateBond ()) {
this.vwr.setModelKitRotateBondIndex (index);
return;
}var haveBond = (this.bondIndex >= 0);
if (!haveBond && index < 0) return;
if (index < 0) {
this.resetBondFields ();
return;
}this.bsRotateBranch = null;
this.branchAtomIndex = -1;
this.bondIndex = index;
this.isRotateBond = isRotate;
this.bondAtomIndex1 = this.vwr.ms.bo[index].getAtomIndex1 ();
this.bondAtomIndex2 = this.vwr.ms.bo[index].getAtomIndex2 ();
this.menu.setActiveMenu ("bondMenu");
}, "~N,~B");
Clazz.defineMethod (c$, "handleDragAtom", 
 function (pressed, dragged, countPlusIndices) {
switch (this.getMKState ()) {
case 0:
return false;
case 2:
if (countPlusIndices[0] > 2) return true;
J.modelkit.ModelKit.notImplemented ("drag atom for XTAL edit");
break;
case 1:
if (this.getSymViewState () == 0) this.setSymViewState (8);
switch (countPlusIndices[0]) {
case 1:
this.centerAtomIndex = countPlusIndices[1];
this.secondAtomIndex = -1;
break;
case 2:
this.centerAtomIndex = countPlusIndices[1];
this.secondAtomIndex = countPlusIndices[2];
break;
}
this.showXtalSymmetry ();
return true;
}
return true;
}, "JV.MouseState,JV.MouseState,~A");
Clazz.defineMethod (c$, "showSymop", 
 function (symop) {
this.secondAtomIndex = -1;
this.symop = symop;
this.showXtalSymmetry ();
}, "~O");
Clazz.defineMethod (c$, "showXtalSymmetry", 
 function () {
var script = null;
switch (this.getSymViewState ()) {
case 0:
script = "draw * delete";
break;
case 8:
default:
var offset = null;
if (this.secondAtomIndex >= 0) {
script = "draw ID sym symop " + (this.centerAtomIndex < 0 ? this.centerPoint : " {atomindex=" + this.centerAtomIndex + "}") + " {atomindex=" + this.secondAtomIndex + "}";
} else {
offset = this.viewOffset;
if (this.symop == null) this.symop = Integer.$valueOf (1);
var iatom = (this.centerAtomIndex >= 0 ? this.centerAtomIndex : this.centerPoint != null ? -1 : this.iatom0);
script = "draw ID sym symop " + (this.symop == null ? "1" : Clazz.instanceOf (this.symop, String) ? "'" + this.symop + "'" : JU.PT.toJSON (null, this.symop)) + (iatom < 0 ? this.centerPoint : " {atomindex=" + iatom + "}") + (offset == null ? "" : " offset " + offset);
}this.drawData = this.runScriptBuffered (script);
this.drawScript = script;
this.drawData = (this.showSymopInfo ? this.drawData.substring (0, this.drawData.indexOf ("\n") + 1) : "");
this.appRunScript (";refresh;set echo top right;echo " + this.drawData.$replace ('\t', ' '));
break;
}
});
Clazz.defineMethod (c$, "getinfo", 
 function () {
var info =  new java.util.Hashtable ();
this.addInfo (info, "addHydrogens", Boolean.$valueOf (this.addXtalHydrogens));
this.addInfo (info, "autobond", Boolean.$valueOf (this.autoBond));
this.addInfo (info, "clickToSetElement", Boolean.$valueOf (this.clickToSetElement));
this.addInfo (info, "hidden", Boolean.$valueOf (this.menu.hidden));
this.addInfo (info, "showSymopInfo", Boolean.$valueOf (this.showSymopInfo));
this.addInfo (info, "centerPoint", this.centerPoint);
this.addInfo (info, "centerAtomIndex", Integer.$valueOf (this.centerAtomIndex));
this.addInfo (info, "secondAtomIndex", Integer.$valueOf (this.secondAtomIndex));
this.addInfo (info, "symop", this.symop);
this.addInfo (info, "offset", this.viewOffset);
this.addInfo (info, "drawData", this.drawData);
this.addInfo (info, "drawScript", this.drawScript);
this.addInfo (info, "isMolecular", Boolean.$valueOf (this.getMKState () == 0));
return info;
});
Clazz.defineMethod (c$, "addInfo", 
 function (info, key, value) {
if (value != null) info.put (key, value);
}, "java.util.Map,~S,~O");
Clazz.defineMethod (c$, "processAtomClick", 
 function (atomIndex) {
switch (this.getMKState ()) {
case 0:
return this.isVwrRotateBond ();
case 1:
this.centerAtomIndex = atomIndex;
if (this.getSymViewState () == 0) this.setSymViewState (8);
this.showXtalSymmetry ();
return true;
case 2:
if (atomIndex == this.centerAtomIndex) return true;
J.modelkit.ModelKit.notImplemented ("edit click");
return false;
}
J.modelkit.ModelKit.notImplemented ("atom click unknown XTAL state");
return false;
}, "~N");
Clazz.defineMethod (c$, "processModeClick", 
 function (action) {
this.processMKPropertyItem (action, false);
}, "~S");
Clazz.defineMethod (c$, "processSelClick", 
 function (action) {
if (action === "mksel_atom") {
this.centerPoint = null;
this.centerAtomIndex = -1;
this.secondAtomIndex = -1;
} else if (action === "mksel_position") {
var pos = this.promptUser ("Enter three fractional coordinates", this.lastCenter);
if (pos == null) return;
this.lastCenter = pos;
var p = J.modelkit.ModelKit.pointFromTriad (pos);
if (p == null) {
this.processSelClick (action);
return;
}this.centerAtomIndex = -2147483647;
this.centerPoint = p;
this.showXtalSymmetry ();
}}, "~S");
Clazz.defineMethod (c$, "processSelOpClick", 
 function (action) {
this.secondAtomIndex = -1;
if (action === "mkselop_addoffset") {
var pos = this.promptUser ("Enter i j k for an offset for viewing the operator - leave blank to clear", this.lastOffset);
if (pos == null) return null;
this.lastOffset = pos;
if (pos.length == 0 || pos === "none") {
this.setProperty ("offset", "none");
return null;
}var p = J.modelkit.ModelKit.pointFromTriad (pos);
if (p == null) {
return action;
}this.setProperty ("offset", p);
} else if (action === "mkselop_atom2") {
J.modelkit.ModelKit.notImplemented (action);
}return null;
}, "~S");
Clazz.defineMethod (c$, "processSymClick", 
 function (action) {
if (action === "mksymmetry_none") {
this.setSymEdit (0);
} else {
this.processMKPropertyItem (action, false);
}}, "~S");
Clazz.defineMethod (c$, "processUCClick", 
 function (action) {
this.processMKPropertyItem (action, false);
this.showXtalSymmetry ();
}, "~S");
Clazz.defineMethod (c$, "getHoverLabel", 
 function (atomIndex) {
var state = this.getMKState ();
var msg = null;
switch (state) {
case 1:
if (this.symop == null) this.symop = Integer.$valueOf (1);
msg = "view symop " + this.symop + " for " + this.vwr.getAtomInfo (atomIndex);
break;
case 2:
msg = "start editing for " + this.vwr.getAtomInfo (atomIndex);
break;
case 0:
var atoms = this.vwr.ms.at;
if (this.isRotateBond) {
if (atomIndex == this.bondAtomIndex1 || atomIndex == this.bondAtomIndex2) {
msg = "rotate branch " + atoms[atomIndex].getAtomName ();
this.branchAtomIndex = atomIndex;
this.bsRotateBranch = null;
} else {
msg = "rotate bond" + this.getBondLabel (atoms);
this.bsRotateBranch = null;
this.branchAtomIndex = -1;
}}if (this.bondIndex < 0) {
if (this.atomHoverLabel.length <= 2) {
msg = this.atomHoverLabel = "Click to change to " + this.atomHoverLabel + " or drag to add " + this.atomHoverLabel;
} else {
msg = atoms[atomIndex].getAtomName () + ": " + this.atomHoverLabel;
this.vwr.highlight (JU.BSUtil.newAndSetBit (atomIndex));
}} else {
if (msg == null) {
switch (this.bsHighlight.cardinality ()) {
case 0:
this.vwr.highlight (JU.BSUtil.newAndSetBit (atomIndex));
case 1:
if (!this.isRotateBond) this.menu.setActiveMenu ("atomMenu");
if (this.atomHoverLabel.indexOf ("charge") >= 0) {
var ch = this.vwr.ms.at[atomIndex].getFormalCharge ();
ch += (this.atomHoverLabel.indexOf ("increase") >= 0 ? 1 : -1);
msg = this.atomHoverLabel + " to " + (ch > 0 ? "+" : "") + ch;
} else {
msg = this.atomHoverLabel;
}msg = atoms[atomIndex].getAtomName () + ": " + msg;
break;
case 2:
msg = this.bondHoverLabel + this.getBondLabel (atoms);
break;
}
}}break;
}
return msg;
}, "~N");
Clazz.defineMethod (c$, "getBondLabel", 
 function (atoms) {
return " for " + atoms[Math.min (this.bondAtomIndex1, this.bondAtomIndex2)].getAtomName () + "-" + atoms[Math.max (this.bondAtomIndex1, this.bondAtomIndex2)].getAtomName ();
}, "~A");
Clazz.defineMethod (c$, "getOtherAtomIndex", 
 function (a1, a2) {
var b = a1.bonds;
var a;
var ret = null;
var zmin = 2147483647;
for (var i = -1; ++i < b.length; ) {
if (b[i] != null && b[i].isCovalent () && (a = b[i].getOtherAtom (a1)) !== a2 && a.sZ < zmin) {
zmin = a.sZ;
ret = a;
}}
return ret;
}, "JM.Atom,JM.Atom");
Clazz.defineMethod (c$, "isVwrRotateBond", 
 function () {
return (this.vwr.acm.getBondPickingMode () == 34);
});
Clazz.defineMethod (c$, "promptUser", 
 function (msg, def) {
return this.vwr.prompt (msg, def, null, false);
}, "~S,~S");
Clazz.defineMethod (c$, "appRunScript", 
 function (script) {
this.vwr.runScript (script);
}, "~S");
Clazz.defineMethod (c$, "runScriptBuffered", 
 function (script) {
var sb =  new JU.SB ();
try {
(this.vwr.eval).runBufferedSafely (script, sb);
} catch (e) {
if (Clazz.exceptionOf (e, Exception)) {
e.printStackTrace ();
} else {
throw e;
}
}
return sb.toString ();
}, "~S");
c$.isTrue = Clazz.defineMethod (c$, "isTrue", 
 function (value) {
return (Boolean.$valueOf (value.toString ()) === Boolean.TRUE);
}, "~O");
c$.pointFromTriad = Clazz.defineMethod (c$, "pointFromTriad", 
 function (pos) {
var a = JU.PT.parseFloatArray (JU.PT.replaceAllCharacters (pos, "{,}", " "));
return (a.length == 3 && !Float.isNaN (a[2]) ? JU.P3.new3 (a[0], a[1], a[2]) : null);
}, "~S");
c$.notImplemented = Clazz.defineMethod (c$, "notImplemented", 
 function (action) {
System.err.println ("ModelKit.notImplemented(" + action + ")");
}, "~S");
Clazz.defineMethod (c$, "cmdAssignAtom", 
function (atomIndex, pt, type, cmd, isClick) {
var bs = (atomIndex < 0 ? null : JU.BSUtil.newAndSetBit (atomIndex));
this.assignAtoms (pt, (pt != null), -1, type, cmd, isClick, bs, 0, 0, null, null, null);
}, "~N,JU.P3,~S,~S,~B");
Clazz.defineMethod (c$, "assignAtoms", 
 function (pt, newPoint, atomIndex, type, cmd, isClick, bs, atomicNo, site, uc, points, packing) {
var haveAtom = (atomIndex >= 0);
if (bs == null) bs =  new JU.BS ();
var nIgnored = 0;
var np = 0;
if (!haveAtom) atomIndex = bs.nextSetBit (0);
var atom = (atomIndex < 0 ? null : this.vwr.ms.at[atomIndex]);
var bd = (pt != null && atom != null ? pt.distance (atom) : -1);
if (points != null) {
np = nIgnored = points.size ();
uc.toFractional (pt, true);
points.addLast (pt);
if (newPoint && haveAtom) nIgnored++;
uc.getEquivPointList (points, nIgnored, packing + (newPoint && atomIndex < 0 ? "newpt" : ""));
}var bsEquiv = (atom == null ? null : this.vwr.ms.getSymmetryEquivAtoms (bs));
var bs0 = JU.BSUtil.copy (bsEquiv);
var mi = (atom == null ? this.vwr.am.cmi : atom.mi);
var ac = this.vwr.ms.ac;
var state = this.getMKState ();
var isDelete = type.equals ("X");
var isXtal = (this.vwr.getOperativeSymmetry () != null);
try {
if (isDelete) {
if (isClick) {
this.vwr.setModelKitRotateBondIndex (-1);
}this.getConstraint (null, atomIndex, J.modelkit.ModelKit.GET_DELETE);
}if (pt == null && points == null) {
if (atom == null) return;
this.vwr.sm.setStatusStructureModified (atomIndex, mi, 1, cmd, 1, bsEquiv);
this.assignAtom (atomIndex, type, this.autoBond, !isXtal, true, bsEquiv);
if (!JU.PT.isOneOf (type, ";Mi;Pl;X;")) this.vwr.ms.setAtomNamesAndNumbers (atomIndex, -ac, null, true);
this.vwr.sm.setStatusStructureModified (atomIndex, mi, -1, "OK", 1, bsEquiv);
this.vwr.refresh (3, "assignAtom");
return;
}this.setMKState (0);
var pts;
if (points == null) {
pts =  Clazz.newArray (-1, [pt]);
} else {
pts =  new Array (Math.max (0, points.size () - np));
for (var i = pts.length; --i >= 0; ) {
pts[i] = points.get (np + i);
}
}var vConnections =  new JU.Lst ();
var isConnected = false;
if (site == 0) {
if (atom != null) {
if (bs.cardinality () <= 1) {
vConnections.addLast (atom);
isConnected = true;
} else if (uc != null) {
var p = JU.P3.newP (atom);
uc.toFractional (p, true);
bs.or (bsEquiv);
var list = uc.getEquivPoints (null, p, packing);
for (var j = 0, n = list.size (); j < n; j++) {
for (var i = bs.nextSetBit (0); i >= 0; i = bs.nextSetBit (i + 1)) {
if (this.vwr.ms.at[i].distanceSquared (list.get (j)) < 0.001) {
vConnections.addLast (this.vwr.ms.at[i]);
bs.clear (i);
}}
}
}isConnected = (vConnections.size () == pts.length);
if (isConnected) {
var d = 3.4028235E38;
for (var i = pts.length; --i >= 0; ) {
var d1 = vConnections.get (i).distance (pts[i]);
if (d == 3.4028235E38) d1 = d;
 else if (Math.abs (d1 - d) > 0.001) {
isConnected = false;
break;
}}
}if (!isConnected) {
vConnections.clear ();
}this.vwr.sm.setStatusStructureModified (atomIndex, mi, 3, cmd, 1, null);
}if (pt != null || points != null) {
var bsM = this.vwr.getThisModelAtoms ();
for (var i = bsM.nextSetBit (0); i >= 0; i = bsM.nextSetBit (i + 1)) {
var as = this.vwr.ms.at[i].getAtomSite ();
if (as > site) site = as;
}
site++;
}}var pickingMode = this.vwr.acm.getAtomPickingMode ();
var wasHidden = this.menu.hidden;
var isMK = this.vwr.getBoolean (603983903);
if (!isMK) {
this.vwr.setBooleanProperty ("modelkitmode", true);
this.menu.hidden = true;
this.menu.allowPopup = false;
}var htParams =  new java.util.Hashtable ();
if (site > 0) htParams.put ("fixedSite", Integer.$valueOf (site));
bs = this.vwr.addHydrogensInline (bs, vConnections, pts, htParams);
if (bd > 0 && !isConnected && vConnections.isEmpty ()) {
this.appRunScript ("connect " + (bd - 0.1) + " " + (bd + 0.01) + " " + bs0 + " " + bs);
}if (!isMK) {
this.vwr.setBooleanProperty ("modelkitmode", false);
this.menu.hidden = wasHidden;
this.menu.allowPopup = true;
this.vwr.acm.setPickingMode (pickingMode);
this.menu.hidePopup ();
}var atomIndexNew = bs.nextSetBit (0);
if (points == null) {
this.assignAtom (atomIndexNew, type, false, atomIndex >= 0 && !isXtal, true, null);
if (atomIndex >= 0) this.assignAtom (atomIndex, ".", false, !isXtal, isClick, null);
this.vwr.ms.setAtomNamesAndNumbers (atomIndexNew, -ac, null, true);
this.vwr.sm.setStatusStructureModified (atomIndexNew, mi, -3, "OK", 1, bs);
return;
}if (atomIndexNew >= 0) {
for (var i = atomIndexNew; i >= 0; i = bs.nextSetBit (i + 1)) {
this.assignAtom (i, type, false, false, true, null);
this.vwr.ms.setSite (this.vwr.ms.at[i], site, true);
}
this.vwr.ms.updateBasisFromSite (atomIndexNew);
}var firstAtom = this.vwr.ms.am[mi].firstAtomIndex;
if (atomicNo >= 0) {
atomicNo = JU.Elements.elementNumberFromSymbol (type, true);
var bsM = this.vwr.getThisModelAtoms ();
for (var i = bsM.nextSetBit (0); i >= 0; i = bsM.nextSetBit (i + 1)) {
if (this.vwr.ms.at[i].getAtomSite () == site) this.vwr.ms.setElement (this.vwr.ms.at[i], atomicNo, true);
}
}this.vwr.ms.setAtomNamesAndNumbers (firstAtom, -ac, null, true);
this.vwr.sm.setStatusStructureModified (-1, mi, -3, "OK", 1, bs);
} catch (ex) {
if (Clazz.exceptionOf (ex, Exception)) {
ex.printStackTrace ();
} else {
throw ex;
}
} finally {
this.setMKState (state);
}
}, "JU.P3,~B,~N,~S,~S,~B,JU.BS,~N,~N,J.api.SymmetryInterface,JU.Lst,~S");
Clazz.defineMethod (c$, "cmdAssignBond", 
function (bondIndex, type, cmd) {
var modelIndex = -1;
var state = this.getMKState ();
try {
this.setMKState (0);
if (type == '-') type = this.pickBondAssignType;
var a1 = this.vwr.ms.bo[bondIndex].atom1;
modelIndex = a1.mi;
var ac = this.vwr.ms.ac;
this.vwr.sm.setStatusStructureModified (bondIndex, modelIndex, 6, cmd, 1, null);
var bsAtoms = this.assignBond (bondIndex, type);
this.vwr.ms.setAtomNamesAndNumbers (a1.i, -ac, null, true);
if (bsAtoms == null || type == '0') this.vwr.refresh (3, "setBondOrder");
this.vwr.sm.setStatusStructureModified (bondIndex, modelIndex, -6, "" + type, 1, null);
} catch (ex) {
if (Clazz.exceptionOf (ex, Exception)) {
JU.Logger.error ("assignBond failed");
this.vwr.sm.setStatusStructureModified (bondIndex, modelIndex, -2, "ERROR " + ex, 1, null);
} else {
throw ex;
}
} finally {
this.setMKState (state);
}
}, "~N,~S,~S");
Clazz.defineMethod (c$, "cmdAssignConnect", 
function (index, index2, type, cmd) {
var state = this.getMKState ();
try {
var connections = JU.AU.newFloat2 (1);
connections[0] =  Clazz.newFloatArray (-1, [index, index2]);
var modelIndex = this.vwr.ms.at[index].mi;
this.vwr.sm.setStatusStructureModified (index, modelIndex, 6, cmd, 1, null);
this.vwr.ms.connect (connections);
var ac = this.vwr.ms.ac;
this.assignAtom (index, ".", true, true, false, null);
this.assignAtom (index2, ".", true, true, false, null);
this.vwr.ms.setAtomNamesAndNumbers (index, -ac, null, true);
this.vwr.sm.setStatusStructureModified (index, modelIndex, -6, "OK", 1, null);
if (type != '1') {
var bs = JU.BSUtil.newAndSetBit (index);
bs.set (index2);
bs = this.vwr.getBondsForSelectedAtoms (bs);
var bondIndex = bs.nextSetBit (0);
this.cmdAssignBond (bondIndex, type, cmd);
}this.vwr.refresh (3, "assignConnect");
} catch (e) {
if (Clazz.exceptionOf (e, Exception)) {
} else {
throw e;
}
} finally {
this.setMKState (state);
}
}, "~N,~N,~S,~S");
Clazz.defineMethod (c$, "assignAtomClick", 
function (atomIndex, element, ptNew) {
this.appRunScript ("modelkit " + (this.vwr.getOperativeSymmetry () == null ? "assign atom" : "ADD") + " ({" + atomIndex + "}) \"" + element + "\" " + (ptNew == null ? "" : JU.Escape.eP (ptNew)) + " true");
}, "~N,~S,JU.P3");
Clazz.defineMethod (c$, "cmdAssignAddAtoms", 
function (type, pt, bsAtoms, packing, cmd, isClick) {
try {
this.vwr.pushHoldRepaintWhy ("modelkit");
var isPoint = (bsAtoms == null);
var isConnected = (pt != null && !isPoint);
var atomIndex = (isPoint ? -1 : bsAtoms.nextSetBit (0));
if (!isPoint && atomIndex < 0) return 0;
var uc = this.vwr.getOperativeSymmetry ();
if (uc == null) {
if (isPoint) this.assignAtoms (pt, true, -1, type, cmd, false, null, 1, -1, null, null, "");
return (isPoint ? 1 : 0);
}var bsM = this.vwr.getThisModelAtoms ();
var n = bsM.cardinality ();
var stype = "" + type;
var pf = null;
isPoint = (pt != null);
if (isPoint) {
pf = JU.P3.newP (pt);
uc.toFractional (pf, true);
}var list =  new JU.Lst ();
var atomicNo = -1;
var site = 0;
for (var i = bsM.nextSetBit (0); i >= 0; i = bsM.nextSetBit (i + 1)) {
var p = JU.P3.newP (this.vwr.ms.at[i]);
uc.toFractional (p, true);
if (pf != null && pf.distanceSquared (p) < 1.96E-6) {
site = this.vwr.ms.at[i].getAtomSite ();
if (type == null) type = this.vwr.ms.at[i].getElementSymbolIso (true);
}list.addLast (p);
}
var nIgnored = list.size ();
packing = "fromfractional;tocartesian;" + packing;
if (type != null) atomicNo = JU.Elements.elementNumberFromSymbol (type, true);
if (isPoint) {
var bsEquiv = (bsAtoms == null ? null : this.vwr.ms.getSymmetryEquivAtoms (bsAtoms));
this.assignAtoms (JU.P3.newP (pt), true, atomIndex, stype, null, false, bsEquiv, atomicNo, site, uc, list, packing);
} else {
var sites =  new JU.BS ();
for (var i = bsAtoms.nextSetBit (0); i >= 0; i = bsAtoms.nextSetBit (i + 1)) {
var a = this.vwr.ms.at[i];
site = a.getAtomSite ();
if (sites.get (site)) continue;
sites.set (site);
stype = (type == null ? a.getElementSymbolIso (true) : stype);
this.assignAtoms (JU.P3.newP (a), false, -1, stype, null, false, null, atomicNo, site, uc, list, packing);
for (var j = list.size (); --j >= nIgnored; ) list.removeItemAt (j);

}
}if (isClick) {
this.vwr.setPickingMode ("dragAtom", 0);
}n = this.vwr.getThisModelAtoms ().cardinality () - n;
return n;
} catch (e) {
if (Clazz.exceptionOf (e, Exception)) {
e.printStackTrace ();
return 0;
} else {
throw e;
}
} finally {
this.vwr.popHoldRepaint ("modelkit");
}
}, "~S,JU.P3,JU.BS,~S,~S,~B");
Clazz.defineMethod (c$, "cmdAssignMoveAtom", 
function (iatom, p) {
var bsFixed = this.vwr.getMotionFixedAtoms ();
var bs =  new JU.BS ();
var checkOcc = false;
var a = this.vwr.ms.at[iatom];
if (!checkOcc || this.vwr.ms.getOccupancyFloat (a.i) == 100) {
bs.set (a.i);
} else {
this.vwr.getAtomsNearPt (0.0001, a, bs);
for (var i = bs.nextSetBit (0); i >= 0; i = bs.nextSetBit (i + 1)) {
if (this.vwr.ms.getOccupancyFloat (i) == 100) bs.clear (i);
}
}var n = 0;
var isOccSet = (bs.cardinality () > 1);
if ((n = this.constrain (iatom, p, bsFixed, !isOccSet)) == 0 || Float.isNaN (p.x) || !isOccSet) return n;
for (var i = bs.nextSetBit (0); i >= 0; i = bs.nextSetBit (i + 1)) {
iatom = (this.constraint == null ? this.vwr.ms.getBasisAtom (i).i : i);
n += this.assignMoveAtom (iatom, p, null);
}
return n;
}, "~N,JU.P3");
Clazz.defineMethod (c$, "assignMoveAtom", 
function (iatom, pt, bsFixed) {
if (Float.isNaN (pt.x) || iatom < 0) return 0;
if (iatom < 0) return 0;
var bs = JU.BSUtil.newAndSetBit (iatom);
bs.and (this.vwr.getThisModelAtoms ());
if (bs.isEmpty ()) return 0;
var state = this.getMKState ();
this.setMKState (0);
try {
var bseq = this.vwr.ms.getSymmetryEquivAtoms (bs);
var sg = this.vwr.getCurrentUnitCell ();
if (this.getConstraint (sg, bseq.nextSetBit (0), J.modelkit.ModelKit.GET_CREATE).type == 6) {
return 0;
}if (bsFixed != null) bseq.andNot (bsFixed);
var n = bseq.cardinality ();
if (n == 0) {
return 0;
}var a = this.vwr.ms.at[iatom];
var v0 = sg.getInvariantSymops (a, null);
var v1 = sg.getInvariantSymops (pt, v0);
if ((v1 == null) != (v0 == null) || !java.util.Arrays.equals (v0, v1)) return 0;
var points =  new Array (n);
var ia0 = bseq.nextSetBit (0);
if (!this.fillPointsForMove (sg, bseq, ia0, a, pt, points)) {
return 0;
}var mi = this.vwr.ms.at[ia0].mi;
this.vwr.sm.setStatusStructureModified (ia0, mi, 3, "dragatom", n, bseq);
for (var k = 0, ia = bseq.nextSetBit (0); ia >= 0; ia = bseq.nextSetBit (ia + 1)) {
var p = points[k++];
this.vwr.ms.setAtomCoord (ia, p.x, p.y, p.z);
}
this.vwr.sm.setStatusStructureModified (ia0, mi, -3, "dragatom", n, bseq);
return n;
} finally {
this.setMKState (state);
}
}, "~N,JU.P3,JU.BS");
Clazz.defineMethod (c$, "fillPointsForMove", 
 function (sg, bseq, i0, a, pt, points) {
var d = a.distance (pt);
var fa = JU.P3.newP (a);
var fb = JU.P3.newP (pt);
sg.toFractional (fa, true);
sg.toFractional (fb, true);
for (var k = 0, i = i0; i >= 0; i = bseq.nextSetBit (i + 1)) {
var p = JU.P3.newP (this.vwr.ms.at[i]);
sg.toFractional (p, true);
var m = sg.getTransform (fa, p, false);
if (m == null) {
return false;
}var p2 = JU.P3.newP (fb);
m.rotTrans (p2);
sg.toCartesian (p2, true);
if (Math.abs (d - this.vwr.ms.at[i].distance (p2)) > 0.001) return false;
points[k++] = p2;
}
fa.setT (points[0]);
sg.toFractional (fa, true);
for (var k = points.length; --k >= 0; ) {
fb.setT (points[k]);
sg.toFractional (fb, true);
var m = sg.getTransform (fa, fb, false);
if (m == null) {
return false;
}for (var i = points.length; --i > k; ) {
if (points[i].distance (points[k]) < 0.1) return false;
}
}
return true;
}, "J.api.SymmetryInterface,JU.BS,~N,JU.P3,JU.P3,~A");
Clazz.defineMethod (c$, "clearAtomConstraints", 
function () {
if (this.atomConstraints != null) {
for (var i = this.atomConstraints.length; --i >= 0; ) this.atomConstraints[i] = null;

}});
Clazz.defineMethod (c$, "hasConstraint", 
function (iatom, ignoreGeneral, addNew) {
var c = this.getConstraint (this.vwr.getOperativeSymmetry (), iatom, addNew ? J.modelkit.ModelKit.GET_CREATE : J.modelkit.ModelKit.GET);
return (c != null && (!ignoreGeneral || c.type != 7));
}, "~N,~B,~B");
Clazz.defineMethod (c$, "constrain", 
function (iatom, ptNew, bsFixed, doAssign) {
var n = 0;
var sym;
if (iatom < 0 || (sym = this.vwr.getOperativeSymmetry ()) == null) {
return 0;
}if (bsFixed != null) bsFixed = this.vwr.getMotionFixedAtoms ();
var a = this.vwr.ms.at[iatom];
var c = this.constraint;
if (c == null) {
c = this.getConstraint (sym, iatom, J.modelkit.ModelKit.GET_CREATE);
if (c.type == 6) {
iatom = -1;
} else {
var b = this.vwr.ms.getBasisAtom (iatom);
var fa = JU.P3.newP (a);
sym.toFractional (fa, true);
var fb = JU.P3.newP (b);
sym.toFractional (fb, true);
var m = sym.getTransform (fa, fb, true);
if (m == null) {
System.err.println ("ModelKit - null matrix for " + iatom + " " + a + " to " + b);
iatom = -1;
} else {
sym.toFractional (ptNew, true);
m.rotTrans (ptNew);
sym.toCartesian (ptNew, true);
c.constrain (b, ptNew);
iatom = b.i;
}}} else {
c.constrain (this.vwr.ms.at[iatom], ptNew);
}if (iatom >= 0 && !Float.isNaN (ptNew.x)) {
if (!doAssign) return 1;
n = this.assignMoveAtom (iatom, ptNew, null);
}ptNew.x = NaN;
return n;
}, "~N,JU.P3,JU.BS,~B");
Clazz.defineMethod (c$, "getConstraint", 
 function (sym, ia, mode) {
if (ia < 0) return null;
var a = this.vwr.ms.getBasisAtom (ia);
var iatom = a.i;
var ac = (this.atomConstraints != null && iatom < this.atomConstraints.length ? this.atomConstraints[iatom] : null);
if (ac != null || mode != J.modelkit.ModelKit.GET_CREATE) {
if (ac != null && mode == J.modelkit.ModelKit.GET_DELETE) {
this.atomConstraints[iatom] = null;
}return ac;
}if (sym == null) return this.addConstraint (iatom,  new J.modelkit.ModelKit.Constraint (a, 0, null));
var ops = sym.getInvariantSymops (a, null);
if (ops.length == 0) return this.addConstraint (iatom,  new J.modelkit.ModelKit.Constraint (a, 7, null));
var plane1 = null;
var line1 = null;
for (var i = ops.length; --i >= 0; ) {
var line2 = null;
var c = sym.getSymmetryInfoAtom (this.vwr.ms, iatom, null, ops[i], null, a, null, "invariant", 1275068418, 0, -1, 0);
if (Clazz.instanceOf (c, String)) {
return J.modelkit.ModelKit.locked;
} else if (Clazz.instanceOf (c, JU.P4)) {
var plane = c;
if (plane1 == null) {
plane1 = plane;
continue;
}var line = JU.Measure.getIntersectionPP (plane1, plane);
if (line == null || line.size () == 0) {
return J.modelkit.ModelKit.locked;
}line2 =  Clazz.newArray (-1, [line.get (0), line.get (1)]);
} else if (Clazz.instanceOf (c, JU.P3)) {
return J.modelkit.ModelKit.locked;
} else {
line2 = c;
}if (line2 != null) {
if (line1 == null) {
line1 = line2;
} else {
var v1 = line1[1];
if (Math.abs (v1.dot (line2[1])) < 0.999) return J.modelkit.ModelKit.locked;
}}}
return this.addConstraint (iatom, plane1 != null ?  new J.modelkit.ModelKit.Constraint (a, 5,  Clazz.newArray (-1, [plane1])) : line1 != null ?  new J.modelkit.ModelKit.Constraint (a, 4, line1) :  new J.modelkit.ModelKit.Constraint (a, 7, null));
}, "J.api.SymmetryInterface,~N,~N");
Clazz.defineMethod (c$, "addConstraint", 
 function (iatom, c) {
if (c == null) {
if (this.atomConstraints != null && this.atomConstraints.length > iatom) {
this.atomConstraints[iatom] = null;
}return null;
}if (this.atomConstraints == null) {
this.atomConstraints =  new Array (this.vwr.ms.ac + 10);
}if (this.atomConstraints.length < iatom + 10) {
var a =  new Array (this.vwr.ms.ac + 10);
System.arraycopy (this.atomConstraints, 0, a, 0, this.atomConstraints.length);
this.atomConstraints = a;
}return this.atomConstraints[iatom] = c;
}, "~N,J.modelkit.ModelKit.Constraint");
Clazz.defineMethod (c$, "addLockedAtoms", 
function (bs) {
var sg = this.vwr.getOperativeSymmetry ();
if (sg == null) return;
var bsm = this.vwr.getThisModelAtoms ();
for (var i = bsm.nextSetBit (0); i >= 0; i = bsm.nextSetBit (i + 1)) {
if (this.getConstraint (sg, i, J.modelkit.ModelKit.GET_CREATE).type == 6) {
bs.set (i);
}}
}, "JU.BS");
Clazz.pu$h(self.c$);
c$ = Clazz.decorateAsClass (function () {
this.type = 0;
this.pt = null;
this.points = null;
this.offset = null;
this.plane = null;
this.unitVector = null;
this.value = 0;
Clazz.instantialize (this, arguments);
}, J.modelkit.ModelKit, "Constraint");
Clazz.makeConstructor (c$, 
function (a, b, c) {
this.pt = a;
this.type = b;
switch (b) {
case 0:
case 7:
case 6:
break;
case 4:
this.offset = c[0];
this.unitVector = JU.V3.newV (c[1]);
this.unitVector.normalize ();
break;
case 5:
this.plane = c[0];
break;
case 1:
this.value = (c[0]).doubleValue ();
this.points =  Clazz.newArray (-1, [c[1], null]);
break;
case 2:
this.value = (c[0]).doubleValue ();
this.points =  Clazz.newArray (-1, [c[1], c[2], null]);
break;
case 3:
this.value = (c[0]).doubleValue ();
this.points =  Clazz.newArray (-1, [c[1], c[2], c[3], null]);
break;
default:
throw  new IllegalArgumentException ();
}
}, "JU.P3,~N,~A");
Clazz.defineMethod (c$, "constrain", 
function (a, b) {
var c =  new JU.V3 ();
var d = JU.P3.newP (a);
switch (this.type) {
case 0:
return;
case 7:
return;
case 6:
b.x = NaN;
return;
case 4:
if (this.pt == null) {
JU.Measure.projectOntoAxis (d, this.offset, this.unitVector, c);
if (d.distanceSquared (a) >= 1.96E-6) {
b.x = NaN;
return;
}}JU.Measure.projectOntoAxis (b, this.offset, this.unitVector, c);
break;
case 5:
if (this.pt == null) {
if (Math.abs (JU.Measure.getPlaneProjection (d, this.plane, c, c)) > 0.01) {
b.x = NaN;
return;
}}JU.Measure.getPlaneProjection (b, this.plane, c, c);
b.setT (c);
break;
}
}, "JU.P3,JU.P3");
Clazz.defineStatics (c$,
"TYPE_NONE", 0,
"TYPE_DISTANCE", 1,
"TYPE_ANGLE", 2,
"TYPE_DIHEDRAL", 3,
"TYPE_VECTOR", 4,
"TYPE_PLANE", 5,
"TYPE_LOCKED", 6,
"TYPE_GENERAL", 7);
c$ = Clazz.p0p ();
c$.locked = c$.prototype.locked =  new J.modelkit.ModelKit.Constraint (null, 6, null);
c$.none = c$.prototype.none =  new J.modelkit.ModelKit.Constraint (null, 0, null);
Clazz.defineStatics (c$,
"MODE_OPTIONS", ";view;edit;molecular;",
"SYMMETRY_OPTIONS", ";none;applylocal;retainlocal;applyfull;",
"UNITCELL_OPTIONS", ";packed;extend;",
"BOOLEAN_OPTIONS", ";autobond;hidden;showsymopinfo;clicktosetelement;addhydrogen;addhydrogens;",
"SET_OPTIONS", ";element;",
"STATE_MOLECULAR", 0x00,
"STATE_XTALVIEW", 0x01,
"STATE_XTALEDIT", 0x02,
"STATE_BITS_XTAL", 0x03,
"STATE_BITS_SYM_VIEW", 0x1c,
"STATE_SYM_NONE", 0x00,
"STATE_SYM_SHOW", 0x08,
"STATE_BITS_SYM_EDIT", 0xe0,
"STATE_SYM_APPLYLOCAL", 0x20,
"STATE_SYM_RETAINLOCAL", 0x40,
"STATE_SYM_APPLYFULL", 0x80,
"STATE_BITS_UNITCELL", 0x700,
"STATE_UNITCELL_PACKED", 0x000,
"STATE_UNITCELL_EXTEND", 0x100);
c$.Pt000 = c$.prototype.Pt000 =  new JU.P3 ();
Clazz.defineStatics (c$,
"GET", 0,
"GET_CREATE", 1,
"GET_DELETE", 2);
});
