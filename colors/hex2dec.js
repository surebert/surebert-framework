/**
@Name: sb.colors.hex2dec
@Description: coverts hex values to decimal
@Example: 
var hex = sb.colors.hex2dec('AC');
*/
sb.colors.hex2dec = function(hex){
	return parseInt(hex,16);
};