/**
@Name: String.prototype.isNumeric
@Author: Paul Visco
@Version: 1.1 11/27/07
@Description: Checks to see if a string is numeric (a float or number)
@Return: Boolean True if the the string represnts numeric data, false otherwise
@Example:
var str = '12';

var answer = str.isNumeric();
//answer = true
*/
String.prototype.isNumeric = function(){
	return /^\d+?(\.\d+)?$/.test(this);
};