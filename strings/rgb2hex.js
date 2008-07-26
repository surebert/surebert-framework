/**
@Name: String.prototype.rgb2hex
@Author: Paul Visco
@Version: 1.01 12/20/07
@Description: Takes an rgb string and converts it to hex color rgb(255,255,255) -> #FFFFFF
@Param: Number asArray If set to 1 then it returns the values as an array
@Return: String A hex color string
@Return: Array A hex color array ['FF', 'FF', 'FF']
@Example:
var myString = 'rgb(255,255,255)';

var newString = myString.rgb2hex();
//newString = '#FFFFFF'

//or
sb.strings.rgb2hex.call(myString);
*/

sb.strings.rgb2hex = function(asArray){

	if(!this.match(/^rgb/i)){return false;}

	var re = new RegExp('rgb\\((\\d{1,}),(\\d{1,}),(\\d{1,})\\)', "ig");
	var colors = re.exec(this.replace(new RegExp("\\s", "g"), ""));
	//var colors = re.exec(sb.strings.stripWhitespace.call(this));
	var r= parseInt(colors[1], 10).toString(16);
	var g= parseInt(colors[2], 10).toString(16);
	var b= parseInt(colors[3], 10).toString(16);
	
	r = (r.length<2) ? r+r : r;
	g = (g.length<2) ? g+g : g;
	b = (b.length<2) ? b+b : b;
	
	if(asArray){
		return [r,g,b];
	} else {
		return '#'+r+''+g+''+b;
	}
};

String.prototype.rgb2hex = sb.strings.rgb2hex;