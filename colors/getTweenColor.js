/**
@Name: sb.colors.getTweenColor
@Author: Paul Visco
@version: 1.1 11/19/07 11/16/08
@Description: Used to get color values between two colors at a certain percentage
@Example:
var colorInBetween = sb.colors.getTweenColor('#ACACAC', '#FF0000', 30%);
*/
sb.include('colors.hex2dec');
sb.include('colors.dec2hex');
sb.include('strings.rgb2hex');

sb.colors.getTweenColor = function(start, end, percent){
	
	if(start.match(/rgb/)){
		start = start.rgb2hex();
	}
	
	if(end.match(/rgb/)){
		end = end.rgb2hex();
	}
	
	start = start.replace(/\#/, '');
	end = end.replace(/\#/, '');
	
	var r1=this.hex2dec(start.slice(0,2));
    var g1=this.hex2dec(start.slice(2,4));
    var b1=this.hex2dec(start.slice(4,6));

    var r2=this.hex2dec(end.slice(0,2));
    var g2=this.hex2dec(end.slice(2,4));
    var b2=this.hex2dec(end.slice(4,6));

    percent = percent/100;

    var r= Math.floor(r1+(percent*(r2-r1)) + 0.5);
    var g= Math.floor(g1+(percent*(g2-g1)) + 0.5);
    var b= Math.floor(b1+(percent*(b2-b1)) + 0.5);
	
    return("#" + this.dec2hex(r) + this.dec2hex(g) + this.dec2hex(b));
};