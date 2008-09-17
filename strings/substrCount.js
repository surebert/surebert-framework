/**
@Name: sb.strings.substrCount
@Author: Paul Visco
@Version: 1.1 11/19/07 09/18/08
@Description: Returns the numbers of times a substring is found in a string
@Param: String needle The substring to search for within the string
@Return: Number The number of times the substring is found in the original string
@Example:
var myString = 'hello world on earth';

var answer = myString.substrCount('world');
//answer = 1;

//or
sb.strings.substrCount.call(myString);
*/
sb.strings.substrCount = function(needle){
	var cnt = 0;
	for (var i=0;i<this.length;i++) {
	if (needle == this.substr(i,needle.length))
		cnt++;
	} 
	return cnt;
};

String.prototype.substrCount = sb.strings.substrCount;

