/**
@Name: String.prototype.nl2br
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Replaces all new line "\n" with HTML break returns "<br />"
@Return: String The original text with lines returns converted to HTML line breaks
@Example:
var myString = "hello\nworld";

var newString = myString.nl2br();
//newString = 'hello<br />world';

*/
String.prototype.nl2br = function(){
	var re = new RegExp("\n", "ig");
	return this.replace(re, "<br />");
};

