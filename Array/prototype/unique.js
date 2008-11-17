/**
@Name: sb.arrays.unique
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Removes duplicate values from an array

@Return: Array Returns an array of unique values from the original array
@Example:
var myArray = [5, 5, 10, 15];
var answer = myArray.unique();
//answer =[5,10,15];
*/
sb.arrays.unique = function(){
	var n=[];
	this.forEach(function(v){if(!sb.arrays.inArray.call(n, v)){n.push(v);}});
	return n;
};

Array.prototype.unique = sb.arrays.unique;