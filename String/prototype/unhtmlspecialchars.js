/**
@Name: String.prototype.unhtmlspecialchars
@Author: Paul Visco
@Version: 1.0 12/14/12
@Description: Escapes same chars as PHP htmlspecialchars
@Return: Boolean Returns true if the string is empty, false otherwise
@Example:
var str = '&lt;p&gt;hello&lt;/p&gt;';
var newString = str.unhtmlspecialchars();
//newString = '<p>hello</p>'
*/

String.prototype.unhtmlspecialchars = function(){
	var s = this.replace(/&amp;/g, '&');
    s = s.replace(/&#039;/g, "'");
    s = s.replace(/&quot;/g, '"');
    s = s.replace(/&lt;/g, '<');
    s = s.replace(/&gt;/g, '>');
	return s;
};