sb.include('arrays.sum');
/**
@Name: sb.arrays.avg
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description:used to determine the average value from an array of values
@Return: Number The average value
@Example:
var myArray = [1,3,4,5];
var average = myArray.avg();
average = 3.25

//or
sb.arrays.avg.call(myArray);
*/
sb.arrays.avg = function(){
	var tl = sb.arrays.sum.call(this);
	return tl/this.length;
};

Array.prototype.avg = sb.arrays.avg;