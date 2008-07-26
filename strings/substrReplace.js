/**
@Name: sb.strings.substrReplace
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Mimics php substrReplace replacing part of the string with another string from an index to a length
@Param: String replaceWith The string to replace with
@Param: Integer start The index to start at
@Param: Integer start The length to replace
@Return: String The string with the replacement
@Example:
var myString = 'hello world';
var answer = myString.substrReplace('girl', 0, 4);
answer = 'girlo world';

//or
sb.strings.substrReplace.call(myString, 'girl', 0, 4);
*/
sb.strings.substrReplace = function(replaceWith, start, length){
	return this.replace(this.substring(start, (start+length)), replaceWith );
};

String.prototype.substrReplace = sb.strings.substrReplace;