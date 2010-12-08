/**
@Name: sb.browser.getScrollPosition
@Description: Gets the window scroll data It is automatically populated on window.onscroll.
@Return Array Returns the an array of x and y scroll pos.
@Example:
var pos = sb.browser.getScrollPosition();
//pos = [400, 300]
*/
sb.browser.getScrollPosition = function(){
	var x=0,y=0;;

    if (typeof window.pageYOffset != 'undefined'){
        x = window.pageXOffset;
        y = window.pageYOffset;
    } else if (typeof document.documentElement.scrollTop != 'undefined'){
        x = document.documentElement.scrollLeft;
        y = document.documentElement.scrollTop;
    } else if (typeof document.body.scrollTop != 'undefined'){
        x = document.body.scrollLeft;
        y = document.body.scrollTop;
    }

    sb.browser.x = x;
    sb.browser.y = y;

    return [x,y];
};

sb.events.add(window, 'scroll', sb.browser.getScrollPosition);