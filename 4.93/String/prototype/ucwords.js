/**
@Name: String.prototype.ucwords
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Converts all first letters of words in a string to uppercase.  Great for titles.
@Return: String The original string with all first letters of words converted to uppercase.
@Example:
var myString = 'hello world';

var newString = myString.ucwords();
//newString = 'Hello World'
*/

String.prototype.ucwords = function(){
	var arr = this.split(' ');
	
	var str ='';
	arr.forEach(function(v){
		str += v.charAt(0).toUpperCase()+v.slice(1,v.length)+' ';
	});
	return str;
};

