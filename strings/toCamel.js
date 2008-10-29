/**
@Name: sb.strings.toCamel
@Description: Converts all dashes to camelStyle
@Return: String The original string with dashes converted to camel - useful when switching between CSS and javascript style properties
@Example:
var str = 'background-color';

var newString = str.toCamel();
//newString = 'backgroundColor'

//without globals
sb.strings.toCamel.call(str);
*/
toCamel = function(){
	return String(this).replace(/-\D/gi, function(m){
		return m.charAt(m.length - 1).toUpperCase();
	});
};

String.prototype.toCamel = sb.strings.toCamel;