/**
@Name: sb.arrays.empty
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: empties an array
@Return: returns the array emptied
@Example:
myArray.empty();

//or
sb.arrays.empty.call(myArray);
*/
sb.arrays.empty = function(){
	this.length =0;
	return this;
};

Array.prototype.empty = sb.arrays.empty;