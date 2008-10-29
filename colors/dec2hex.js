/**
@Name: sb.colors.dec2hex
@Description: coverts decimal values to hex
@Example: 
var hex = sb.colors.dec2hex(255);
*/
sb.colors.dec2hex = function(dec){
	return(this.hexDigit[dec>>4]+this.hexDigit[dec&15]);
};
	
sb.colors.hexDigit = ["0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F"];