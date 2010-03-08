/**
@Name: String.prototype.br2nl
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Converts HTML line breaks "<br />" to new lines "\n"
@Return: String The original string but replaces breaks with actual new lines
@Example:
var myString = 'hello<br />there';
var newString = myString.br2nl();
//newString = "hello\nthere";
*/
String.prototype.br2nl = function(){
	var re = new RegExp("<[br /|br]>", "ig");
	return this.replace(re, "\n");
};