/**
@Name: sb.colors.toArray
@Author: Paul Visco
@Vesion: 1.0 11/19/07
@Description: Converts hex or rgb formatted color strings to an array
@Param: An rgb or hex color string
@Return: Array An array of the r,g and b colors e.g. [255,255,255]
@Example:
var colorArray = sb.colors.toArray('#ffffff');
var colorArray = sb.colors.toArray('rbg(255,255,255)');

//both return [255,255,255]
*/
sb.colors.toArray = function(color){

	sb.include('strings.stripWhiteSpace');

	if(color.match(/\#/)){
		color = sb.strings.hex2rgb.call(color);
	}
	
	var re = new RegExp('rgb\\((\\d{1,}),(\\d{1,}),(\\d{1,})\\)', "ig");
	var colors = re.exec(sb.strings.stripWhitespace.call(color));
	
	return [colors[1], colors[2], colors[3]];
	
};