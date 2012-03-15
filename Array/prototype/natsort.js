/**
@Name: Array.prototype.natsort
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Sort the array values in a natural alpha numeric way so that 1,10,2,3,4,5 becomes 1,2,3,4,5,10
@Param: Number direction Accepts either 1 for ascending order or -1 for decending order. If not specified that ascending order is the default. 
@Return: Array Returns The array sorted naturally.
@Example:

var myArray = [1,10,2,3,4,5];
var answer = myArray.natsort();
//answer = [1,2,3,4,5,10];
*/
Array.prototype.natsort = function(direction){
	direction = (direction ==-1) ? -1 : 1;
	if(direction == -1){
		this.sort(function(a,b){return (b-a);});
	} else {
		this.sort(function(a,b){return (a-b);});
	}
	return this;
};