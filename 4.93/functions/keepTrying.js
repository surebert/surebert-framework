/**
@Name: sb.functions.keepTrying
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: You can pass it any number of functions as arrays and the one that works with return
@Param: Any number of functions, can be either function references or inline anonymous functions
@Return: returns the return value of the first funciton that returns true
@Example:
var x = sb.utils.keepTrying(function1, function2, function3);
*/
sb.functions.keepTrying= function(){
	for(var x=0;x<arguments.length;x++){
		try{return arguments[x]();} catch(e){}
	}
};