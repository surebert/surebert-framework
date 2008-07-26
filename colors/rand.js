/**
@Name: sb.colors.rand
@Author: Paul Visco
@version: 1.0 11/19/07
@Description: Returns a random color string that can be used to set an elements style properties
@Param: Boolean grey If set to one, it returns only greyscale colors meaning r,g, and b values are the same
@Return: Array An rgb color string rgb(234,34,156);
@Example:
var colorArray = sb.colors.rand();
colorArray = rgb(234,34,156);//<-one possible return value

var colorArray = sb.colors.rand(1);
colorArray = rgb(34,34,34);//<-one possible return value
//both return [255,255,255]
*/
sb.colors.rand = function(grey){
	var rand = sb.math.rand;
	if(grey == 1){
		grey = rand(0,255);
		return "rgb("+grey+","+grey+","+grey+")";	
	} else {
		return "rgb("+rand(0,255)+","+rand(0,255)+","+rand(0,255)+")";	
	}
};