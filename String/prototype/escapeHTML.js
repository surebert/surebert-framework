/**
@Name: String.prototype.escapeHTML
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Checks to see if a string is empty or not
@Return: Escapes < and >
@Example:
var str = '<p>hello</p>';
var newString = str.escapeHTML();
//newString = '&lt;p&gt;hello&lt;/p&gt;'
*/
String.prototype.escapeHTML = function(){
	var str = this.replace(/</g, '&lt;');
	return str.replace(/>/g, '&gt;');
};