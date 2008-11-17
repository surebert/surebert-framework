/**
@Name: sb.arrays.inject
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Finds the index of the value given within the array.  Return the position of the first matching value.  Rememeber that array start at 0.
@Param: Object/String/Number val The value to inject into the array. 
@Return: Integer
@Example:
var myArray = ['zero', 'one', 'two'];
var answer = myArray.inject(1, 'bagel');

//answer is now ['zero', 'bagel', 'one', 'two'];
*/
sb.arrays.inject = function(index, val){
	if(index <0){return this;}
	var a = this.slice(0, index), b = this.splice(index, this.length-index);
	
	a[index] = val;
	return a.concat(b);
};

Array.prototype.inject = sb.arrays.inject;