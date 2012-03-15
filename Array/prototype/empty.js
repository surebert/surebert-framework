/**
@Name: Array.prototype.empty
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: empties an array
@Return: returns the array emptied
@Example:
myArray.empty();
*/
Array.prototype.empty = function(){
	this.length =0;
	return this;
};