/**
@Name: sb.arrays.random
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Grab a random value from the array.  The value is randomly selected each time the value is run.
@Return: Object/String/Number Returns a random value from the array.  Type is the same as the value.
@Example:
var myArray = [1,10,2,3,4,5];
var answer = myArray.random();
//answer = 4; //<--could change each time

//or
sb.arrays.random.call(myArray);
*/
sb.arrays.random = function(){
		return this[sb.math.rand(0,this.length)];
};

Array.prototype.random = sb.arrays.random;