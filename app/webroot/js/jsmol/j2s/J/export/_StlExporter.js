Clazz.declarePackage("J.export");
Clazz.load(["J.export._VrmlExporter", "JU.M4"], "J.export._StlExporter", ["java.io.ByteArrayOutputStream", "JU.Lst", "$.Measure", "$.OC", "JU.Logger", "JV.Viewer"], function(){
var c$ = Clazz.decorateAsClass(function(){
this.isDebug = false;
this.header = null;
this.oc = null;
this.bos = null;
this.m4 = null;
this.lstMatrix = null;
this.m4a = null;
this.nTri = 0;
Clazz.instantialize(this, arguments);}, J["export"], "_StlExporter", J["export"]._VrmlExporter);
Clazz.prepareFields (c$, function(){
this.m4a =  new JU.M4();
});
Clazz.makeConstructor(c$, 
function(){
Clazz.superConstructor(this, J["export"]._StlExporter);
this.useTable = null;
this.noColor = true;
this.isDebug = JU.Logger.debugging;
if (!this.isDebug) {
this.oc =  new JU.OC();
this.oc.setBigEndian(false);
this.oc.setParams(null, null, false, this.bos =  new java.io.ByteArrayOutputStream());
}});
Clazz.overrideMethod(c$, "outputHeader", 
function(){
this.header = ("solid model generated by Jmol " + JV.Viewer.getJmolVersion() + "                                                                                ").substring(0, 80);
if (this.isDebug) {
this.out.append(this.header);
this.out.append("\n");
} else {
this.oc.write(this.header.getBytes(), 0, 80);
this.oc.write( Clazz.newByteArray (4, 0), 0, 4);
}this.lstMatrix =  new JU.Lst();
this.m4 =  new JU.M4();
this.m4.setIdentity();
this.lstMatrix.addLast(this.m4);
this.outputInitialTransform();
});
Clazz.overrideMethod(c$, "pushMatrix", 
function(){
this.lstMatrix.addLast(this.m4);
this.m4 = JU.M4.newM4(this.m4);
});
Clazz.overrideMethod(c$, "popMatrix", 
function(){
this.m4 = this.lstMatrix.removeItemAt(this.lstMatrix.size() - 1);
});
Clazz.defineMethod(c$, "output", 
function(data){
}, "~S");
Clazz.overrideMethod(c$, "outputChildStart", 
function(){
});
Clazz.overrideMethod(c$, "outputChildClose", 
function(){
});
Clazz.overrideMethod(c$, "outputRotation", 
function(a){
this.m4a.setToAA(a);
this.m4.mul(this.m4a);
}, "JU.A4");
Clazz.overrideMethod(c$, "outputAttrPt", 
function(attr, pt){
this.outputAttr(attr, pt.x, pt.y, pt.z);
}, "~S,JU.T3");
Clazz.overrideMethod(c$, "outputAttr", 
function(attr, x, y, z){
this.m4a.setIdentity();
if (attr === "scale") {
this.m4a.m00 = x;
this.m4a.m11 = y;
this.m4a.m22 = z;
} else if (attr === "translation") {
this.m4a.m03 = x;
this.m4a.m13 = y;
this.m4a.m23 = z;
}this.m4.mul(this.m4a);
}, "~S,~N,~N,~N");
Clazz.overrideMethod(c$, "outputGeometry", 
function(vertices, normals, colixes, indices, polygonColixes, nVertices, nPolygons, bsPolygons, faceVertexMax, colorList, htColixes, offset){
for (var i = 0; i < nPolygons; i++) {
if (bsPolygons != null && !bsPolygons.get(i)) continue;
var face = indices[i];
this.writeFacet(vertices, face, 0, 1, 2);
if (faceVertexMax == 4 && face.length >= 4 && face[2] != face[3]) this.writeFacet(vertices, face, 2, 3, 0);
}
}, "~A,~A,~A,~A,~A,~N,~N,JU.BS,~N,JU.Lst,java.util.Map,JU.P3");
Clazz.defineMethod(c$, "writeFacet", 
function(vertices, face, i, j, k){
this.tempQ1.setT(vertices[face[i]]);
this.tempQ2.setT(vertices[face[j]]);
this.tempQ3.setT(vertices[face[k]]);
this.m4.rotTrans(this.tempQ1);
this.m4.rotTrans(this.tempQ2);
this.m4.rotTrans(this.tempQ3);
JU.Measure.calcNormalizedNormal(this.tempQ1, this.tempQ2, this.tempQ3, this.tempV1, this.tempV2);
if (Float.isNaN(this.tempV1.x)) {
return;
}this.writePoint("facet normal", this.tempV1);
this.writePoint("outer loop\nvertex", this.tempQ1);
this.writePoint("vertex", this.tempQ2);
this.writePoint("vertex", this.tempQ3);
if (this.isDebug) {
this.out.append("endloop\nendfacet\n");
} else {
this.oc.writeByteAsInt(0);
this.oc.writeByteAsInt(0);
}this.nTri++;
}, "~A,~A,~N,~N,~N");
Clazz.overrideMethod(c$, "finalizeOutput", 
function(){
if (this.isDebug) {
this.out.append("endsolid model\n");
} else {
var b = this.bos.toByteArray();
b[80] = (this.nTri & 0xff);
b[81] = ((this.nTri >> 8) & 0xff);
b[82] = ((this.nTri >> 16) & 0xff);
b[83] = ((this.nTri >> 24) & 0xff);
this.out.write(b, 0, b.length);
}return this.finalizeOutput2();
});
Clazz.overrideMethod(c$, "outputCircle", 
function(pt1, pt2, radius, colix, doFill){
}, "JU.P3,JU.P3,~N,~N,~B");
Clazz.overrideMethod(c$, "plotText", 
function(x, y, z, colix, text, font3d){
}, "~N,~N,~N,~N,~S,JU.Font");
Clazz.defineMethod(c$, "writePoint", 
function(s, p){
if (this.isDebug) this.out.append(s);
this.writeFloat(p.x);
this.writeFloat(p.y);
this.writeFloat(p.z);
if (this.isDebug) this.out.append("\n");
}, "~S,JU.T3");
Clazz.defineMethod(c$, "writeFloat", 
function(f){
if (this.isDebug) this.out.append(" " + f);
 else this.oc.writeInt(Float.floatToIntBits(f));
}, "~N");
});
;//5.0.1-v2 Mon Feb 19 09:32:38 CST 2024
