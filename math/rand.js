/**
@Name: sb.math.rand
@Description: Used to generate a random number between a min and max value
@Param: min Number The minimum value to return
@Param: max Number The maximum value to return
@Return Number Returns a random number between min and max
@Example
var x = sb.math.rand(1,10);
*/
sb.math.rand = function(min,max){
	min = min || 0;
	max = max || 100;
	return Math.floor(Math.random()*max+min);
};