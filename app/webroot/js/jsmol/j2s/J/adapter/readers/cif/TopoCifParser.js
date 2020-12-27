Clazz.declarePackage ("J.adapter.readers.cif");
Clazz.load (["J.adapter.readers.cif.CifReader", "JU.Lst"], "J.adapter.readers.cif.TopoCifParser", ["java.lang.Float", "java.util.Hashtable", "JU.BS", "$.P3", "$.V3", "J.adapter.readers.cif.Cif2DataParser", "JU.Logger"], function () {
c$ = Clazz.decorateAsClass (function () {
this.reader = null;
this.links = null;
this.allowedTypes = "+v+hb+";
this.bondlist = "";
if (!Clazz.isClassDefined ("J.adapter.readers.cif.TopoCifParser.TopoLink")) {
J.adapter.readers.cif.TopoCifParser.$TopoCifParser$TopoLink$ ();
}
if (!Clazz.isClassDefined ("J.adapter.readers.cif.TopoCifParser.TopoPrimitive")) {
J.adapter.readers.cif.TopoCifParser.$TopoCifParser$TopoPrimitive$ ();
}
this.index = 0;
this.debugging = false;
Clazz.instantialize (this, arguments);
}, J.adapter.readers.cif, "TopoCifParser", null, J.adapter.readers.cif.CifReader.Parser);
Clazz.prepareFields (c$, function () {
this.links =  new JU.Lst ();
});
Clazz.makeConstructor (c$, 
function () {
this.debugging = JU.Logger.isActiveLevel (5);
});
Clazz.overrideMethod (c$, "setReader", 
function (reader) {
if (reader.checkFilterKey ("TOPOS_IGNORE")) {
return this;
}this.reader = reader;
var types = reader.getFilter ("TOPOS_TYPES");
if (types != null && types.length > 1) types = "+" + types.substring (1).toLowerCase () + "+";
if (reader.doApplySymmetry) reader.asc.setNoAutoBond ();
return this;
}, "J.adapter.readers.cif.CifReader");
Clazz.overrideMethod (c$, "processBlock", 
function (key) {
if (this.reader == null) return;
if (this.reader.ucItems != null) {
this.reader.allow_a_len_1 = true;
for (var i = 0; i < 6; i++) this.reader.setUnitCellItem (i, this.reader.ucItems[i]);

}this.reader.parseLoopParameters (J.adapter.readers.cif.TopoCifParser.topolFields);
while (this.reader.cifParser.getData ()) {
this.index++;
var name1 = null;
var name2 = null;
var d = 0;
var op1 = 1;
var op2 = 1;
var t1 =  Clazz.newIntArray (3, 0);
var t2 =  Clazz.newIntArray (3, 0);
var type = "v";
var multiplicity;
var angle = 0;
type = this.reader.getField (11);
if (this.allowedTypes.indexOf ("+" + type + "+") < 0) continue;
var a1 = null;
var a2 = null;
if ((a1 = this.reader.asc.getAtomFromName (name1 = this.reader.getField (0))) == null || (a2 = this.reader.asc.getAtomFromName (name2 = this.reader.getField (1))) == null) {
JU.Logger.warn ("TopoCifParser atom " + (a1 == null ? name1 : name2) + " could not be found");
continue;
}d = this.getFloat (2);
if (!(d > 0)) {
JU.Logger.warn ("TopoCifParser invalid distance");
continue;
}multiplicity = this.getInt (12);
angle = this.getFloat (13);
op1 = this.getInt (3);
op2 = this.getInt (7);
var field;
field = this.reader.getField (14);
if (field.length > 1) {
t1 = J.adapter.readers.cif.Cif2DataParser.getIntArrayFromStringList (field, 3);
} else {
t1[0] = this.getInt (4);
t1[1] = this.getInt (5);
t1[2] = this.getInt (6);
}field = this.reader.getField (15);
if (field.length > 1) {
t2 = J.adapter.readers.cif.Cif2DataParser.getIntArrayFromStringList (field, 3);
} else {
t2[0] = this.getInt (8);
t2[1] = this.getInt (9);
t2[2] = this.getInt (10);
}this.links.addLast (Clazz.innerTypeInstance (J.adapter.readers.cif.TopoCifParser.TopoLink, this, null, this.index, a1, a2, d, op1, t1, op2, t2, multiplicity, type, angle));
}
}, "~S");
Clazz.overrideMethod (c$, "finalizeReader", 
function () {
if (this.reader == null) return false;
this.reader.applySymmetryAndSetTrajectory ();
return true;
});
Clazz.overrideMethod (c$, "finalizeSymmetry", 
function (haveSymmetry) {
if (this.reader == null || !haveSymmetry || !this.reader.doApplySymmetry) return;
var sym = this.reader.asc.getXSymmetry ().getBaseSymmetry ();
var nOps = sym.getSpaceGroupOperationCount ();
var operations =  new Array (nOps);
for (var i = 0; i < nOps; i++) {
operations[i] = sym.getSpaceGroupOperationRaw (i);
}
var carts =  new Array (this.reader.asc.ac);
var atoms = this.reader.asc.atoms;
for (var i = this.reader.asc.ac; --i >= 0; ) {
carts[i] = JU.P3.newP (atoms[i]);
sym.toCartesian (carts[i], true);
}
var n = 0;
var bsConnected =  new JU.BS ();
var nLinks = this.links.size ();
for (var i = 0; i < nLinks; i++) {
var link = this.links.get (i);
link.setPrimitives (sym, operations);
n += this.setBonds (i, atoms, carts, link, sym, nOps, bsConnected);
}
if (bsConnected.cardinality () > 0) this.reader.asc.bsAtoms = bsConnected;
this.reader.appendLoadNote ("TopoCifParser read " + nLinks + " links; created " + n + " edges and " + bsConnected.cardinality () + " nodes");
var info =  new JU.Lst ();
for (var i = 0; i < nLinks; i++) {
info.addLast (this.links.get (i).getInfo ());
}
this.reader.asc.setCurrentModelInfo ("topology", info);
}, "~B");
Clazz.defineMethod (c$, "setBonds", 
 function (index, atoms, carts, link, sym, nOps, bsConnected) {
var nbonds = 0;
var bs1 =  new JU.BS ();
var bs2 =  new JU.BS ();
for (var i = this.reader.asc.ac; --i >= 0; ) {
var a = atoms[i];
if (a.atomSite == link.a1.atomSite) {
bs1.set (i);
}if (a.atomSite == link.a2.atomSite) {
bs2.set (i);
}}
var pa =  new JU.P3 ();
var bsym =  new JU.BS ();
for (var i1 = bs1.nextSetBit (0); i1 >= 0; i1 = bs1.nextSetBit (i1 + 1)) {
var at1 = atoms[i1];
bsym.clearAll ();
for (var i = 0; i < nOps; i++) {
var prim = link.primitives[i];
if (prim == null) continue;
pa.setT (at1);
sym.unitize (pa);
if (J.adapter.readers.cif.TopoCifParser.isEqualD (pa, prim.p1u, 0)) {
if (this.debugging) JU.Logger.debug ("TopoCifParser " + link.info () + " primitive: " + prim.info ());
bsym.set (i);
}}
link.symops = bsym;
for (var i2 = bs2.nextSetBit (0); i2 >= 0; i2 = bs2.nextSetBit (i2 + 1)) {
var at2 = atoms[i2];
if (i1 == i2 || !J.adapter.readers.cif.TopoCifParser.isEqualD (carts[i1], carts[i2], link.d)) continue;
var va12 = JU.V3.newVsub (at2, at1);
for (var i = bsym.nextSetBit (0); i >= 0; i = bsym.nextSetBit (i + 1)) {
if (!J.adapter.readers.cif.TopoCifParser.isEqualD (va12, link.primitives[i].v12f, 0)) continue;
var key = "," + at1.index + "," + at2.index + ",";
if (this.bondlist.indexOf (key) >= 0) continue;
this.bondlist += key + at1.index + ",";
nbonds++;
if (this.debugging) JU.Logger.debug (nbonds + " " + at1 + " " + at2 + " " + at1.index + " " + at2.index);
this.reader.asc.addNewBondWithOrderA (at1, at2, link.order);
bsConnected.set (at1.index);
bsConnected.set (at2.index);
}
}
}
return nbonds;
}, "~N,~A,~A,J.adapter.readers.cif.TopoCifParser.TopoLink,J.api.SymmetryInterface,~N,JU.BS");
c$.isEqualD = Clazz.defineMethod (c$, "isEqualD", 
function (p1, p2, d) {
return Math.abs (p1.distance (p2) - d) < J.adapter.readers.cif.TopoCifParser.ERROR_TOLERANCE;
}, "JU.T3,JU.T3,~N");
Clazz.defineMethod (c$, "getInt", 
 function (field) {
return this.reader.parseIntStr (this.reader.getField (field));
}, "~N");
Clazz.defineMethod (c$, "getFloat", 
 function (field) {
return this.reader.parseFloatStr (this.reader.getField (field));
}, "~N");
c$.$TopoCifParser$TopoLink$ = function () {
Clazz.pu$h(self.c$);
c$ = Clazz.decorateAsClass (function () {
Clazz.prepareCallback (this, arguments);
this.idx = 0;
this.a1 = null;
this.a2 = null;
this.op1 = 0;
this.op2 = 0;
this.t1 = null;
this.t2 = null;
this.dt = null;
this.type = null;
this.voronoiAngle = 0;
this.multiplicity = 0;
this.m1 = null;
this.m2 = null;
this.p1f = null;
this.p2f = null;
this.d = 0;
this.order = 0;
this.primitives = null;
this.symops = null;
Clazz.instantialize (this, arguments);
}, J.adapter.readers.cif.TopoCifParser, "TopoLink");
Clazz.makeConstructor (c$, 
function (a, b, c, d, e, f, g, h, i, j, k) {
this.idx = a;
this.a1 = b;
this.a2 = c;
this.d = d;
this.op1 = e - 1;
this.op2 = g - 1;
this.type = j;
this.order = ("vw".equals (j) ? 33 : "hb".equals (j) ? 2048 : 1);
this.t1 = JU.P3.new3 (f[0], f[1], f[2]);
this.t2 = JU.P3.new3 (h[0], h[1], h[2]);
this.dt = JU.P3.new3 ((h[0] - f[0]), (h[1] - f[1]), (h[2] - f[2]));
this.multiplicity = i;
this.voronoiAngle = k;
}, "~N,J.adapter.smarter.Atom,J.adapter.smarter.Atom,~N,~N,~A,~N,~A,~N,~S,~N");
Clazz.defineMethod (c$, "getInfo", 
function () {
var a =  new java.util.Hashtable ();
a.put ("label1", this.a1.atomName);
a.put ("label2", this.a2.atomName);
a.put ("distance", Float.$valueOf (this.d));
a.put ("symop1", Integer.$valueOf (this.op1 + 1));
a.put ("symop2", Integer.$valueOf (this.op2 + 1));
a.put ("t1", this.t1);
a.put ("t2", this.t2);
a.put ("multiplicity", Integer.$valueOf (this.multiplicity));
a.put ("type", this.type);
a.put ("voronoiSolidAngle", Float.$valueOf (this.voronoiAngle));
a.put ("atomIndex1", Integer.$valueOf (this.a1.index));
a.put ("atomIndex2", Integer.$valueOf (this.a2.index));
a.put ("index", Integer.$valueOf (this.idx));
a.put ("op1", this.m1);
a.put ("op2", this.m2);
a.put ("dt", this.dt);
a.put ("primitive1", this.p1f);
a.put ("primitive2", this.p2f);
var b =  Clazz.newIntArray (this.symops.cardinality (), 0);
for (var c = 0, d = this.symops.nextSetBit (0); d >= 0; d = this.symops.nextSetBit (d + 1)) {
b[c++] = d + 1;
}
a.put ("primitiveSymops", b);
a.put ("order", Integer.$valueOf (this.order));
return a;
});
Clazz.defineMethod (c$, "setPrimitives", 
function (a, b) {
var c = b.length;
this.p1f = JU.P3.new3 (this.a1.x, this.a1.y, this.a1.z);
this.p2f = JU.P3.new3 (this.a2.x, this.a2.y, this.a2.z);
(this.m1 = b[this.op1]).rotTrans (this.p1f);
(this.m2 = b[this.op2]).rotTrans (this.p2f);
this.p2f.add (this.dt);
this.primitives =  new Array (c);
for (var d = 0; d < c; d++) {
var e = Clazz.innerTypeInstance (J.adapter.readers.cif.TopoCifParser.TopoPrimitive, this, null, this, d + 1, a, b[d]);
if (!e.isValid) continue;
this.primitives[d] = e;
}
}, "J.api.SymmetryInterface,~A");
Clazz.defineMethod (c$, "info", 
function () {
return "[link " + this.idx + " " + this.a1.atomName + " " + this.a2.atomName + " " + this.d + " " + this.type + "]";
});
Clazz.overrideMethod (c$, "toString", 
function () {
return this.info ();
});
c$ = Clazz.p0p ();
};
c$.$TopoCifParser$TopoPrimitive$ = function () {
Clazz.pu$h(self.c$);
c$ = Clazz.decorateAsClass (function () {
Clazz.prepareCallback (this, arguments);
this.p1u = null;
this.p2u = null;
this.v12f = null;
this.isValid = false;
this.symop = 0;
Clazz.instantialize (this, arguments);
}, J.adapter.readers.cif.TopoCifParser, "TopoPrimitive");
Clazz.makeConstructor (c$, 
function (a, b, c, d) {
this.symop = b;
var e =  new JU.P3 ();
var f =  new JU.P3 ();
e.setT (a.p1f);
f.setT (a.p2f);
d.rotTrans (e);
d.rotTrans (f);
this.p1u = JU.P3.newP (e);
c.unitize (this.p1u);
this.p2u = JU.P3.newP (f);
this.p2u.add (JU.V3.newVsub (this.p1u, e));
this.v12f = JU.V3.newVsub (this.p2u, this.p1u);
e.setT (this.p1u);
f.setT (this.p2u);
c.toCartesian (e, true);
c.toCartesian (f, true);
this.isValid = J.adapter.readers.cif.TopoCifParser.isEqualD (e, f, a.d);
}, "J.adapter.readers.cif.TopoCifParser.TopoLink,~N,J.api.SymmetryInterface,JU.M4");
Clazz.defineMethod (c$, "info", 
function () {
return "op=" + this.symop + " pt=" + this.p1u + " v=" + this.v12f;
});
Clazz.overrideMethod (c$, "toString", 
function () {
return this.info ();
});
c$ = Clazz.p0p ();
};
Clazz.defineStatics (c$,
"ERROR_TOLERANCE", 0.001,
"topolFields",  Clazz.newArray (-1, ["_topol_link_node_label_1", "_topol_link_node_label_2", "_topol_link_distance", "_topol_link_site_symmetry_symop_1", "_topol_link_site_symmetry_translation_1_x", "_topol_link_site_symmetry_translation_1_y", "_topol_link_site_symmetry_translation_1_z", "_topol_link_site_symmetry_symop_2", "_topol_link_site_symmetry_translation_2_x", "_topol_link_site_symmetry_translation_2_y", "_topol_link_site_symmetry_translation_2_z", "_topol_link_type", "_topol_link_multiplicity", "_topol_link_voronoi_solidangle", "_topol_link_site_symmetry_translation_1", "_topol_link_site_symmetry_translation_2"]),
"topol_link_node_label_1", 0,
"topol_link_node_label_2", 1,
"topol_link_distance", 2,
"topol_link_site_symmetry_symop_1", 3,
"topol_link_site_symmetry_translation_1_x", 4,
"topol_link_site_symmetry_translation_1_y", 5,
"topol_link_site_symmetry_translation_1_z", 6,
"topol_link_site_symmetry_symop_2", 7,
"topol_link_site_symmetry_translation_2_x", 8,
"topol_link_site_symmetry_translation_2_y", 9,
"topol_link_site_symmetry_translation_2_z", 10,
"topol_link_type", 11,
"topol_link_multiplicity", 12,
"topol_link_voronoi_solidangle", 13,
"topol_link_site_symmetry_translation_1", 14,
"topol_link_site_symmetry_translation_2", 15);
});
