/**
@Name: String.prototype.unhtmlspecialchars
@Author: Paul Visco
@Version: 1.0 12/14/12
@Description: Escapes same chars as PHP htmlspecialchars
@Return: Boolean Returns true if the string is empty, false otherwise
@Example:
var str = '<p>hello</p>';
var newString = str.htmlspecialchars();
//newString = '&lt;p&gt;hello&lt;/p&gt;'
*/
String.prototype.htmlspecialchars = function(){
	var s = this.replace(/&/g, '&amp;');
    s = s.replace(/'/g, "&#039;");
    s = s.replace(/"/g, '&quot;');
    s = s.replace(/</g, '&lt;');
    s = s.replace(/>/g, '&gt;');
	return s;
};