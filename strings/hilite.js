/**
@Name: sb.strings.hilite
@Author: Paul Visco
@Version: 1.1 05/29/07
@Description: Hilites a string within a text block
@Param: String needle The text to find
@Param: String className The className to use for hiliting overrides default yellow background style
@Return: String witht he needle underlied and hilited
@Example:
var myString = 'There was a dog on earth';

var newString = myString.hilite('dog');
//newString = 'There was a <u style="backgroundColor:yellow;">dog</u> on earth';

//or
sb.strings.hilite.call(myString, 'dog');
*/
sb.strings.hilite = function(needle, className){
	className = (typeof className != 'undefined') ? ' class="'+className+'" ' : ' style="background-color:yellow;" ';
	
	var matches = new RegExp( "("+needle+")", "ig");
	return this.replace(matches, "<u "+className+">$1</u>");
};

String.prototype.hilite =  sb.strings.hilite;