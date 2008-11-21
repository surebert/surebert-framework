/**
@Name: String.prototype.trim
@Description: trims whitespace from both left and right side of a string
@Return: String The original string with whitespace removed from left and right side
@Example:
var str = '    hello world       ';

var newString = str.trim();
//newString = 'hello world'
*/
String.prototype.trim = function() {
	return this.replace(/(^\s+|\s+$)/g, '');
};