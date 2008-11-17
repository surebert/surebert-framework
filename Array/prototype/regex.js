/**
@Name: sb.arrays.regex
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Limit the values of an array to Values that do  not match the regex expression are excluded

@Return: Array Returns an array of values that match the regex from the original array
@Example:
var myArray = [5, 10, 15];
var answer = myArray.regex(/\d{2}/);
//answer = [10,15] //because they are at least two digits as specified in the regex \d{2}
*/
sb.arrays.regex = function(expression) {
	
	return sb.arrays.filter.call(this, function(v, k, a) {
	 	if(v.toString().match(expression)){return true;}
	});
	
};

Array.prototype.regex = sb.arrays.regex;