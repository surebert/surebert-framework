sb.include('Array.prototype.sum');
/**
@Name: Array.prototype.avg
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Used to determine the average value from an array of values
@Return: Number The average value
@Example:
var myArray = [1,3,4,5];
var average = myArray.avg();
average = 3.25
*/
Array.prototype.avg = function(){
	var tl = this.sum();
	return tl/this.length;
};