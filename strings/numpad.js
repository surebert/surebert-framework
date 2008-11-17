/**
@Name: sb.strings.numpad
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Pads all numbers under 9 with a zero on the left
@Return: String The original number padded to left with zero
@Example:
var myString = 9;

var newString = myString.numpad();
//newString = '09'
*/
sb.strings.numpad = function(){
	return (this<=9) ? '0'+this : this;
};

String.prototype.numpad = sb.strings.numpad;