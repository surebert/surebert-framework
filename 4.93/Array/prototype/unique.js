/**
@Name: Array.prototype.unique
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Removes duplicate values from an array

@Return: Array Returns an array of unique values from the original array
@Example:
var myArray = [5, 5, 10, 15];
var answer = myArray.unique();
//answer =[5,10,15];
*/
Array.prototype.unique = function(){
	var n=[];
	this.forEach(function(v){if(!n.inArray(v)){n.push(v);}});
	return n;
};