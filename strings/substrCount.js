/**
@Name: sb.strings.substrCount
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Returns the numbers of times a substring is found in a string
@Param: String needle The substring to search for within the string
@Param: Boolean caseInsensitive Optional - If true the search is case insensitive
@Return: Number The number of times the substring is found in the original string
@Example:
var myString = 'hello world on earth';

var answer = myString.substrCount('world');
//answer = 1;

var answer = myString.substrCount('World', true);
//answer = 1;

//or
sb.strings.substrCount.call(myString);
*/
sb.strings.substrCount = function(needle, caseInsensitive){
	var matches, ig = (caseInsensitive === undefined) ? 'g' : 'ig';
		
	var re = new RegExp(needle, ig);
	matches = this.match(re);
	if(matches !==null){
		return matches.length;
	} else {
	 	return false;
	}
};

String.prototype.substrCount = sb.strings.substrCount;