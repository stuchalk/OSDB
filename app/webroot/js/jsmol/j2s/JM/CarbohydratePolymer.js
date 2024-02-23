Clazz.declarePackage("JM");
Clazz.load(["JM.BioPolymer"], "JM.CarbohydratePolymer", null, function(){
var c$ = Clazz.declareType(JM, "CarbohydratePolymer", JM.BioPolymer);
Clazz.makeConstructor(c$, 
function(monomers){
this.set(monomers);
this.type = 3;
}, "~A");
});
;//5.0.1-v2 Mon Feb 19 09:32:38 CST 2024
