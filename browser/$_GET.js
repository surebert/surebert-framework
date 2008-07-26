/**
@Name: sb.browser.$_GET
@Description: An array of all query params e.g. url?name=paul -> $_GET['name'] = 'paul'.  There is a global reference to this name $_GET.  Keys that are not foudn return false;
@Example: 
//if the url of the page was http://www.surebert.com?name=paul you could reference that query data like this
if($_GET['name'] =='paul){
	alert('hello paul');
}
*/
sb.browser.$_GET = [];
	
$_GET = sb.browser.$_GET;

/**
@Name: sb.browser.populateGET
@Description: Used Internally
*/
sb.browser.populateGET = function (){
	var i,s,val,key;
	var q = window.location.search.substring(1);
	var v = q.split("&");

	for (i=0;i<v.length;i++) {
		s = v[i].split("=");
		key = unescape(s[0]);
		val = unescape(s[1]);
		sb.browser.$_GET[key] = val.replace("+", " ");
	 }
};
sb.browser.populateGET();