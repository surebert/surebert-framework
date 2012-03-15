sb.include('Array.prototype.natsort');
/**
@Name: Array.prototype.range
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Determines the range of values in a numeric array.  That is the highest value minus the loweest value
@Return: Number The range of the values
@Example:
var myArray = [1,10,2,3,4,5];
var answer = myArray.range();
//answer = 9; //<--the difference between 10 (the highest number) and 1 (the lowest number)
*/
Array.prototype.range = function(){
	var a = this.natsort();
	return this[a.length-1]-a[0];
};