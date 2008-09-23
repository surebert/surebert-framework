/**
@Name: sb.strings.ltrim
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Trims all white space off the left side of a string
@Return: String The original text with whitespace removed from the left
@Example:
var myString = '           hello';

var newString = myString.ltrim();
//newString = 'hello';

//or
sb.strings.ltrim.call(myString);
*/
sb.strings.ltrim = function(){
	return this.replace(/^\s+/, "");
};

String.prototype.ltrim = sb.strings.ltrim;