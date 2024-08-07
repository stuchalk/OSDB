// BH 2024.04.16

data = `[[Table Loading files from databases
||
COD 
|
>>
 load =cod/2312394
 load "" packed
 load "" centroid
<<
||
AMS
|
>>
 load =ams/quartz 
 load =ams/quartz 1
 load =ams/quartz 1 packed;
 load =ams/quartz fill 20; animation on 
 animation first 
 animation next 
<<
 

>> load =ams/quartz packed; prompt( getProperty("fileInfo.models").select("(_journal_year)").pivot.format("json") ); <<
||
AFLOW
|
>>
 load =aflowlib/62.51 packed 
 select all; label %[Wyckoff]; set labeloffset 0 0 
 background white; font labels 20; color labels black 
 write aflow-62.51.png as PNGJ 
<<
||
]]

[[Table Jmol LOAD command options relating to crystallographic symmetry
||
Load just the third model
|
>> load =ams/quartz 3; <<
||
Create a na x nb x nc block of unit cells, "packing" the cells with atoms on all faces	
|
>> load =ams/quartz 1 {2 2 2}; <<
||
Add packing atoms on all faces or optionally a range in fractional units beyond the faces	
|
>>
 load =aflowlib/123 packed;
 load =ams/quartz 1 packed 0.6
<<
||
Fill a supercell of the designated size, producing a single, larger cell
|
>>
 load =aflowlib/12.5 SUPERCELL {2 2 1}
 load =ams/quartz 1 supercell "2a, a + 2b, c" 
<<
||
Fill an x by x by x Angstrom block of space with atoms, regardless of the unit cell
|
>> load =ams/halite 1 fill 20 <<
||
Reload the current file, filling its primitive unit cell rather than its conventional cell.
|
>> load =aflowlib/166.51; load "" fill unitcell primitive; <<
||
Load a file, overriding its space group with the one provided
|
>> load =aflowlib/62.3 spacegroup "x,y,z;x,-y,z" <<
||
Apply the specified space group, unit cell, and offset to the file data, which need not be crystallographic. 
|
>>load :caffeine centroid spacegroup "P21" unitcell [10 10 10 90 90 90] offset {0 0 2} <<
||
]] 

[[Table Jmol MODELKIT commands relating to crystallography
||
<hr>
>>
 load =aflowlib/62.4 packed; draw spacegroup all
 modelkit spacegroup "Pbnm"; moveto axis c1; draw spacegroup all 
 modelkit spacegroup "Pnma"; moveto axis c1; draw spacegroup all 
 modelkit spacegroup "62.5"; moveto axis c1; draw spacegroup all 
 modelkit spacegroup "62.1"; moveto axis c1; draw spacegroup all 
<<
|
Create or change the space group to the given name or number. 

||
<hr> 
>>
 zap; modelkit spacegroup "62" 
 modelkit add P {0.12,0.23,.45/1} 
 set elementkey !elementkey 
 modelkit add N wyckoff G 
 modelkit add O {1/4,1/2,1/4} packed 
 modelkit add S {1/2,1/2,0} packed 
 select *; label %[wyckoff]; set labeloffset 0 0; color labels black
 zoomto in
 zoomto out
<<
|
Add sets of equivalent atoms
||
>>
 modelkit moveto @3 {0.27 0.32 0.45/1}
 set picking dragatom # prompt "Now drag an atom."
<<
|
Move an atom to a new position, also adjusting its symmetry-equivalent atoms appropriately. 
||
>>
 modelkit assign @3 Ge
<<
|
Change the element of the specified atom and its equivalent atoms.	
||
>>modelkit connect @4 @5<<
|
Connect two specified atoms as well as their equivalent atoms, accordingy.
||
>>
 modelkit delete @3
 modelkit delete _O
 modelkit delete {wyckoff=d}
 modelkit delete {site=2} 
 modelkit add N wyckoff G 
 modelkit add O {1/4,1/2,1/4} packed 
 modelkit delete {fy > 0.9} 
<<
|
Delete the specified atom or set of atoms and all of their equivalent atoms. 	
||
<hr>
>>
 zap; modelkit spacegroup "P 21 21 2"
 modelkit add F 
 draw spacegroup
<< 
|
Draw the symmetry elements of a space group
||
>>
 draw symop 3
 draw symop @2 @4
 set picking dragatom # prompt "Go ahead and drag one of the atoms."
 draw symop @3 @1
 draw symop {0 1/4 0} {0 3/4 0} 
 set picking symop #; prompt "Now pick pairs of atoms."
<< 
|
Depict a specific symmetry operation
||
]]

[[Figure figure14.png
||
<hr>
>>
 load "=aflowlib/62.77" packed
 select all; label %[Wyckoff]; set labeloffset 0 0 
 background white; font labels 20; color labels black 
 write "figure14.png" as PNGJ
<<
|  Creating a model, showing symmetry operations, and moving atoms
||
]]	 

[[Figure figure15.c2.png
||
<hr>
>>
 zap; modelkit spacegroup "P2/m";
 modelkit add C Wyckoff G 
 rotate x 5; rotate y -10;
 draw spacegroup
 draw spacegroup all
 set picking dragatom # prompt "Now drag an atom."
<<
|  Creating a model, showing symmetry operations, and moving atoms
||
]]	 

 
[[Figure figure16.b2.png  
||
<hr>
>> 
 zap;modelkit spacegroup 18; rotate x 2; rotate y 2
 modelkit add "F" {0.460 0.383 0.288/1}
 draw spacegroup all
 set zshade;set zshadepower 1
 set frank off;set showunitcellinfo false
<<
| 3D interactive general position diagram
||
]]
[[Figure figure16.c2.png  
||
<hr>
>> 
zap;modelkit spacegroup 18; rotate x 2; rotate y 2
modelkit add "F" {0.460 0.383 0.288/1}
label "%[atomno]";color labels black
set labelOffset 4 4;font label 16 bold
draw spacegroup all
set zshade;set zshadepower 1
draw s12 symop @1 @2  
draw s23 symop @2 @3
draw s34 symop @3 @4
draw s41 symop @4 @1
set frank off;set showunitcellinfo false
set picking dragatom # prompt("Notice that all the atoms move simultaneously.")
<<
| Illustrating a general position (Wyckoff 4c) orbit
||
]]
[[Figure figure16.d2.png  
||
<hr>
>> 
zap;modelkit spacegroup 18
rotate x 2; rotate y 2
modelkit add "F" {0.460 0.383 0.288/1}
draw uc18 unitcell color red "uc-18"
modelkit spacegroup "18>sub>4" packed
translate x -5
draw uc4 unitcell color yellow "uc-4"
label "%[atomno]";color labels black
set labelOffset 4 4;font label 16 bold
color property site 
draw s12 symop @1 @2 # prompt "These two atoms are not related in P 21"
draw s23 symop @2 @3
draw s34 symop @3 @4 # prompt "Also not related in P 21"
draw s41 symop @4 @1
set frank off;set showunitcellinfo false
<<
| Illustrating the loss of symmetry and the<br>splitting of the general position going<br> from P 21 21 1 (No. 18) to P 1 21 1 (No. 4)
||
]]
[[Figure figure16.e2.png  
||
<hr>
>> 
zap;modelkit spacegroup 18; rotate x 2; rotate y 2
modelkit add "F" {0.460 0.383 0.288/1}
draw uc18 unitcell color red "uc-18"
modelkit spacegroup "18>a,b,c;1/4,0,0>4" packed
translate y -5
draw uc4 unitcell color yellow "uc-4"
label "%[atomno]";color labels black
set labelOffset 4 4;font label 16 bold
color property site 
draw s23 symop @1 @3
draw s41 symop @2 @4
set frank off;set showunitcellinfo false
set picking dragatom # prompt("Notice that the general position has split. Blue and red are now independent.")
prompt "BCS data used: " + spacegroup(18,4, "subgroups") 
<<
| Illustrating the a different transformation pathway from P 21 21 21 (No.18) to P 21 (No. 4). 
||
]]

[[
more examples
||
>>
zap;modelkit spacegroup 18
rotate x 2; rotate y 2
modelkit add "F" {0.460 0.383 0.288/1}
draw uc18 unitcell color red "uc-18"
draw sg18 spacegroup all 
modelkit spacegroup "18>sub>4" packed
draw uc4 unitcell color yellow "uc-4"
draw sg2 spacegroup all 
draw sg2* off 
draw sg2* on 
draw sg1* off 
draw sg1* on 
<<
| Illustrating the differences in symmetry between P 21 21 21 (No. 18) and its subgroup P 21 (No. 4).
||
<hr>
>>
 load =aflowlib/20.1 packed 
 color property site 
 set picking dragatom # prompt "Now drag various atoms. Notice that some of them are contrained. How are they constrained?"
 display none
 draw uc1 unitcell color red 
 draw sg1 spacegroup all 
 modelkit spacegroup "20>sub>4" packed 
 draw uc2 unitcell 
 draw sg2 spacegroup all 
 color property site 
 draw sg1* off
 draw sg1* on
 draw sg2* off
 draw sg2* on
 display all
 set picking dragatom # prompt "Now drag an atom. Notice that there is a lot more freedom now that some symmetry has been lost. Which symmetry has been lost? How did that result in more freedom for certain atoms?"
<<
|
A comparison of C 2 2 21 (No. 20) <br>and its maximal subgroup P 21 (No. 4)
<br><br>
>>
 prompt spacegroup(20, "subgroups") 
 prompt spacegroup(20,4, "subgroups") 
<<
]]



[[Table Various questions and how to answer them using Jmol scripting functions
||
What's an example of a structure with Pmna symmetry?
|
>>prompt spacegroup("pnma").ita<<

>> load =aflowlib/62 packed <<

||
What are the Wyckoff positions and coordinates for space group 62? 	
|
>>
 zap; modelkit spacegroup 62
 prompt symop("wyckoffm")
<<
||
What is the general position in space group 225?
|
>>
 zap; modelkit spacegroup 225
 prompt symop("wyckoff", "G")
<<
||
What are the representative symmetry operations for space group 145? 	
|
>>prompt show("spacegroup 145") <<
||
What are the generators for the first setting of space group 100?
|
>>	prompt spacegroup(100).its[1].gen <<
||
Which space groups have rhombohedral settings?
|
>> prompt spacegroup("ITA/all").select("(sg) where hm like '*:r'").join(","); <<
||
What are the Wyckoff positions of the copper atoms in this structure?
|
>>
 load =aflowlib/220.3 packed; connect {_Cu} {_Cu} none
 prompt {_Cu}.wyckoffm.pivot .format("JSON")
 color property site 
<<

||
]]

<table border=1 width=95%><tr><td valign="top">

[[Table Jmol on the web 
|| 
[[[Bilbao Crystallographic Server COMPSTRU program|https://www.cryst.ehu.es/cryst/compstru.html]]]
|| 

[[[Crystallographic Open Database|http://www.crystallography.net/cod]]]
|| 
 	
[[[American Mineralogist Crystal Structure Database|https://rruff.geo.arizona.edu/AMS]]]
|| 
[[[AFLOW Encyclopedia of Crystallographic Prototypes|https://www.aflowlib.org/prototype-encyclopedia]]]

|| 
[[[IUCr Journals|https://publcif.iucr.org/cifmoldb/gui/cifjmol.php?cifid=dg3046]]]
|| 

[[[Q-Studio crystal structure builder|https://qs.pwmat.com]]]
|| 

[[[Jmol Crystal Symmetry Explorer|https://chemapps.stolaf.edu/jmol/jsmol/jcse]]]
|| 

[[[Jmol Point Group Explorer|https://chemapps.stolaf.edu/jmol/jsmol/jpge]]]
|| 

[[[IUCr Symmetry Workshop Demo|https://chemapps.stolaf.edu/jmol/jsmol/iucrdemo]]]
|| 
]]

</td><td valign="top">


[[ More fun stuff
|| Thiourea point group | >> load thiourea-pointgroup.png; spin on<<
|| Thiourea comparison   | >> load thiourea19-compare.png <<
|| 
|| Space group 230 S6-symmetry | >> load sg230_S6.png <<
|| Crystallographic kaleidescope|
>> load sg229-wyckoff.png; delay 1;set picking dragatom #; prompt "Ok, now align the structure like a kaliedescope and drag atoms to see how pretty you can make it!";delay 5;load audio Jeopardy-theme-song.mp3 <<  
|| Make a custom nanotube | >> script rabe.spt <<  ([[[read the script|rabe.spt]]])
||
|| [[[All 230 space groups (image)|230-space-groups.png]]]
||
|| These directories hold PNGJ (image+Jmol) files that can be viewed as a standard PNG file or dragged back into the JSmol app on this page or into Jmol/Java. They are created using <<write ?.png AS PNGJ>> Go ahead and click on a model to view it as an image and then drag it to the JSmol app on this page to make it "go live". <b>Can you figure out how that's done?</b>
||
|| [[[Directory - space groups | https://chemapps.stolaf.edu/jmol/ita/sg]]]
|| [[[Directory - wyckoff positions | https://chemapps.stolaf.edu/jmol/ita/wyc]]]
|| [[[Directory - subgroups (No.1-No.50; to index 4) | https://chemapps.stolaf.edu/jmol/ita/sub]]]
||
|| [[[Jmol Project Website | https://jmol.sourceforge.net]]]
|| [[[Download Jmol and JSmol | https://sourceforge.net/projects/jmol/files/latest/download]]]
||
]]
</td></tr></table>

`;

Demo = { 
	sections:[]
}


Demo.setRefs = function(data) {
	var a = data.replaceAll("[[[", "]]]").split("]]]");
	var ret = a[0];
	for (var i = 1; i < a.length; i++) {
		var b = a[i].split("|");
		ret += "<a target=_blank href=\""+b[1].trim()+"\">"+b[0].trim()+"</a>";
		ret += a[++i];
	}
	return ret;
}

Demo.addLinks = function(d) {
	Jmol.setDocument(0);
	var a = d.replaceAll("<<", ">>").split(">>");
	var ret = a[0];
	for (var j = 1; j < a.length; j++) {
	    var lines = a[j].split('\n');
	    var isList = false;
	    for (var i = 0; i < lines.length; i++) {
		var cmd = lines[i].trim();
		if (!cmd) continue;
		var isLoad = (cmd.indexOf("load") >= 0 || cmd.indexOf("zap") >= 0);
		var text = cmd.split("#")[0];
		cmd = cmd.replace("#",";");
		var s = Jmol.jmolLink("jmolApplet0",cmd,text);
		if (isLoad) { 
			if (isList) {
				ret += "</ul>";
			}
			isList = true;
			s = "<b>" + s + "</b><ul>"; 
		} else if (isList) {
			s = "<li>" + s + "</li>";
		}
		ret +=  s;
		ret += (isList ? "": "<br>");
	    }
	    if (isList) {
		ret += "</ul>";
		isList = false;
	    }
	    ret += a[++j];
	} 
	return ret;

}

Demo.setTables = function(d, sections) {
	var a = d.replaceAll("[[", "]]").split("]]");
	for (var i = 0; i < a.length-1; i++) {
		sections.push(a[i++]);
		var rows = a[i].split("||");
		var title = rows[0].trim();
		if (title.indexOf("Figure") == 0) {
			var b = title.split(" ");
			b[1] = Jmol.jmolLink("jmolApplet0","script \"" + b[1] + "\"");
			title = b.join(" ");
		}
		title = title.replace("Table ","");
		var s = "<hr><table cellpadding=3><tr><th colspan=3>" + title + "</th></tr>";
		for (var j = 1; j < rows.length; j++) { 	
			s += "\n<tr>";
			var cols = rows[j].split('|');
			for (var k = 0; k < cols.length; k++) {
				s +="<td valign=top>" + cols[k] + "</td>"; 
			} 
			s += "</tr>"
		}
		s += "</table>";
		sections.push(s);
	}
	sections.push(a[a.length - 1]);
}

Demo.compile = function() {
	var d = Demo.setRefs(data);
	d = Demo.addLinks(d);
	Demo.setTables(d, Demo.sections);
	

}


Demo.compile();


