/**
@Name: sb.strings.stripWhitespace
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Removes all whitespace from a string
@Return: String The original string without any whitespace
@Example:
var myString = 'hello world on earth';

var newString = myString.stripWhitespace();
//newString = 'helloworldonearth'
*/
sb.strings.stripWhitespace = function(){
	return this.replace(new RegExp("\\s", "g"), "");
};

String.prototype.stripWhitespace = sb.strings.stripWhitespace;