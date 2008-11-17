/**
@Name: sb.strings.cleanFileName
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: Cleans a filename up making it safe for upload, removes spaces, swicthes to camelStyle and strips extraneos punctuation
@Return: String The original string but replaces breaks with actual new lines
@Example: 
var myString = 'hello there,, file . jpg';
var newString = myString.cleanFileName();
//newString = 'helloThereFile.jpg'
*/
sb.strings.cleanFileName = function(){
	var ext = this.match(/\.\w{2,3}$/);
	var str = this.replace(/ext/, '');
	str = str.replace(/\.\w{2,3}$/, '');
	str = str.replace(/[^A-Z^a-z^0-9]+/g, ' ');
	str = str.ucwords();

	str = str.replace(/ /g, '');
	str +=String(ext).toLowerCase();
	return str;
};

String.prototype.cleanFileName = sb.strings.cleanFileName;