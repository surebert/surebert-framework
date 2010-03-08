/**
@Name: Array.prototype.shuffle
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Shuffle the values in an array randomly
@Param: values Array If passed an array of values, all the values in the argument array are removed from the array being manipulated
@Param: value Object/String/Number If a single object, string, number, etc is passed to the function than only that value is removed.
@Return: Array Returns the array minus the values that were specified for removal.
@Example:
var myArray = [5, 10, 15];
myArray.shuffle();
//myArray =[10, 15, 5]; //<-could change each time
*/
Array.prototype.shuffle = function(){
	var i=this.length,j,t;
	
	while(i--)
	{
		j=Math.floor((i+1)*Math.random());
		t=this[i];
		this[i]=this[j];
		this[j]=t;
	}
	return this;
};