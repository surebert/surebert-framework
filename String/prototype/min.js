/**
@Name: sb.arrays.min
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Finds the minimum value in an alpha/numeric array.  Sorts alphanumerically and chooses the lowest.  Numbers are higher than letters, so 'apple' is lower than 1

@Return: String/Number Returns the min value
@Example:

var myArray = [5, 10, 15];
var answer = myArray.min();
//answer = 5;
*/
sb.arrays.min = function(){
	 var min=this[0];
	 this.forEach(function(v){min=(v<min)?v:min;});
	 return min;
};

Array.prototype.min = sb.arrays.min;