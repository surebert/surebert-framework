/**
@Name: sb.arrays.remove
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Removes a value or a set of values from an array.
@Param: values Array If passed an array of values, all the values in the argument array are removed from the array being manipulated
@Param: value Object/String/Number If a single object, string, number, etc is passed to the function than only that value is removed.
@Return: Array Returns the array minus the values that were specified for removal.
@Example:
var myArray = [5, 10, 15];
var answer = myArray.remove([10,5]);
//answer =[15];

var answer = myArray.remove(5);
//answer =[10, 15];

//or
sb.arrays.remove.call(myArray, [10, 15]);
*/
sb.arrays.remove = function(values){
	
	return this.filter(function(v){
		if(sb.typeOf(values) !='array'){
			return v != values;
		} else {
			return !sb.arrays.inArray.call(values, v);
		}
	});
};

Array.prototype.remove = sb.arrays.remove;