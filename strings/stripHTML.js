/**
@Name: sb.strings.stripHTML
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Removes all HTML tags from a string
@Return: String The original string without any HTML markup
@Example:
var myString = 'hello <p>world</p> on earth';

var newString = myString.stripHTML();
//newString = 'hello world on earth'

//or
sb.strings.stripHTML.call(myString);
*/
sb.strings.stripHTML = function(){
	var re = new RegExp("(<([^>]+)>)", "ig");
	var str = this.replace(re, "");
	var amps = ["&nbsp;", "&amp;", "&quot;"];
	var replaceAmps =[" ", "&", '"'];
	for(var x=0;x<amps.length;x++){
		str = str.replace(amps[x], replaceAmps[x]);
	}
	
	re = new RegExp("(&(.*?);)", "ig");
	str = str.replace(re, "");
	
	return str;
};

String.prototype.stripHTML = sb.strings.stripHTML;