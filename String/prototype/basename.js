/**
@Name: String.prototype.basename
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Grabs the basename from a url
@Return: String The filename part of the original string
@Example:
var myString = 'http://www.google.com/logo.gif';
var newString = myString.basename();
//newString = 'logo.gif';

*/
String.prototype.basename = function(){
	var re = new RegExp("/\\/", "g");
	var str = this.replace(re, "/");
	var filename=str.split("/");
	return filename[(filename.length - 1)];
};