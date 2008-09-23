/**
@Name: sb.strings.strstr
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Returns true if the substring is found in the string
@Param: String needle The substring to search for within the string
@Return: Boolean True if the string is found and false if it isn't
@Example:
var myString = 'hello world on earth';

var answer = myString.strstr('world');
//answer = true;

//or
sb.strings.strstr.call(myString);
*/
sb.strings.strstr =  function(needle){
	var f= this.indexOf(needle)+1;
	return (f===0) ? 0 :1;
};

String.prototype.strstr = sb.strings.strstr;