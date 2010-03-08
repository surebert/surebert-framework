/**
@Name: Array.prototype.sum
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Add up the values in an array

@Return: Number Returns the sum of all the values in an array
@Example:
var myArray = [5, 5, 10, 15];
var answer = myArray.sum();
//answer =35;
*/
Array.prototype.sum = function(){
	var val = 0;
	this.forEach(function(v){
		val +=v;
	});
	return val;
};