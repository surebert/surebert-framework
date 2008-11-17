/**
@Name: sb.strings.escapeHTML
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Checks to see if a string is empty or not
@Return: Boolean Returns true if the string is empty, false otherwise
@Example:
var str = '<p>hello</p>';
var newString = str.escapeHTML();
//newString = '&lt;p&gt;hello&lt;/p&gt;'
*/
sb.strings.escapeHTML = function(){
	var str = this.replace(/</g, '&lt;');
	return str.replace(/>/g, '&gt;');
};

String.prototype.escapeHTML = sb.strings.escapeHTML;