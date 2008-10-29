/**
@Name: sb.browser.getScrollPosition
@Description: Gets the window scroll data It is automatically populated on window.onscroll.
@Return Array Returns the an array of x and y scroll pos.
@Example:
var pos = sb.browser.getScrollPosition();
//pos = [400, 300]
*/
sb.browser.getScrollPosition = function(){
	var x=0,y=0;
	if(window.pageYOffset){
		y = window.pageYOffset;
	} else if (document.documentElement && document.documentElement.scrollTop){
		y= document.documentElement.scrollTop;
	} 
	sb.browser.scrollY = y;

	if(window.pageXSOffset){
		x = window.pageXOffset;
	} else if (document.documentElement && document.documentElement.scrollLeft){
		x = document.documentElement.scrollLeft;
	} 
	
	sb.browser.scrollX = x;
	return [sb.browser.scrollX, sb.browser.scrollY];
};

sb.events.add(window, 'scroll', sb.browser.getScrollPosition);