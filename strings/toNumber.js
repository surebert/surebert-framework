/**
@Name: sb.strings.toNumber
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Converts a numeric string into an integer or float
@Return: Float If the original value has a decimal in it, a float is returned
@Return: Number If the original value is an interger, an integer value is returned
@Example:
var myString = '12';
var num = myString.toNumber() +2;
//num = 14 //without running toNumber it would return '122'

var myString = '12.4';
var num = myString.toNumber() +2;
//num = 14.4 //without running toNumber it would return '12.42'
*/
sb.strings.toNumber = function(){
	if(this.match(/\./)){
		return parseFloat(this, 10);
	} else {
		return parseInt(this, 10);
	}
};

String.prototype.toNumber = sb.strings.toNumber;