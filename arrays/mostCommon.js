/**
@Name: sb.arrays.mostCommon
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Finds the most common value in an array.  If no value is most common then it returns false.

@Return: String/Number/Object Returns the most common value in the array or false if no value is most common.
@Example:

var myArray = [5, 10, 15];
var answer = myArray.mostCommon();
//answer = false;

var myArray = [5, 5, 10, 15];
var answer = myArray.mostCommon();
//answer = 5;

//or
sb.arrays.mostCommon.call(myArray);
*/
sb.arrays.mostCommon = function(){
	var count=0,max=0,num=0,mode=0;
	this.sort();
	this.forEach(function(v){
		
		if(num != v){
			num=v;
			count=1;
		} else {
			count++;
		}
		
		if(count > max){
			max = count;
			mode = num;
		}
	});
	if(max ==1){mode = false;}
	return mode;
};

Array.prototype.mostCommon = sb.arrays.mostCommon;