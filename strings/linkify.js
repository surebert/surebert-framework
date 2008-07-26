/**
@Name: sb.strings.linkify
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Converts all URLs in a text block into actual html links
@Param: String target The target to open the links in, defaults to blank
@Return: String The original text withe links converted to HTML
@Example:
var myString = 'Here http://www.surebert.com is a great javascript toolkit';

var newString = myString.linkify();
//newString = 'Here <a href="http://www.surebert.com" target="_blank">::link::</a> is a great javascript toolkit';

//or
sb.strings.linkify.call(myString, target);
*/
sb.strings.linkify = function(target){
	target = target || '_blank';
	var match_url = new RegExp("(\s|\n|)([a-z]+?):\/\/([a-z0-9\-\.,\?!%\*_\#:;~\\&$@\/=\+]+)", "i");
	return this.replace(match_url, "<a href=\"$2://$3\" title=\"$2://$3\" target=\""+target+"\">::link::</a>");
	
};

String.prototype.linkify = sb.strings.linkify;