/**
@Name: sb.arrays.max
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Finds the maximum value in an alpha/numeric array.  Sorts alphanumerically and chooses the highest.  Number have preference over letters, so 1 is higher than 'apple'

@Return: String/Number Returns the max value
@Example:
var myArray = [5, 10, 15];
var answer = myArray.max();
//answer = 15;

//or
sb.arrays.max.call(myArray);
*/
sb.arrays.max = function(){
	 var max=this[0];
	 this.forEach(function(v){max=(v>max)?v:max;});
	 return max;
};

Array.prototype.max = sb.arrays.max;