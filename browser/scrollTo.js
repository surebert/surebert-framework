sb.include('effect');
sb.include('browser.getScrollPosition');
/**
@Name:  sb.browser.scrollTo
@Author: Paul Visco
@Version: 1.0
@Description: Used to gradually scroll the browser somewhere
@Param: integer y Where to scroll to on the y axis
@Param: integer duration The time milliseconds to scroll over
Example: 
//scrolls to y 500 and alerts 'done' at the end
var scrollTo = sb.browser.scrollTo({y : 500, duration : 24, onEnd : function(){
	alert('done');
}});

*/
sb.browser.scrollTo = function(o){
	if(sb.browser.scrolling){
		sb.browser.scrolling.reset();
	}
	//percent, duration, onEnd
	sb.browser.scrolling = new sb.effect({
		onChange : function(){
			window.scrollTo(0, this.value);
		}
	});
	
	sb.events.add(document, 'DOMMouseScroll', function(){sb.browser.scrolling.stop();});
	sb.events.add(document, 'mousewheel', function(){sb.browser.scrolling.stop();});
	
	var scrollTo = function(o){
			var scrollPos = sb.browser.getScrollPosition();
			sb.browser.scrolling.duration = o.duration || 45;
			sb.browser.scrolling.begin = scrollPos[1]; 
			
			sb.browser.scrolling.onEnd = o.onEnd || 0;
			
			if(typeof o.y =='number'){
				sb.browser.scrolling.change = o.y-sb.browser.scrolling.begin;
			}
			
			sb.browser.scrolling.tween = (sb.browser.scrolling.change < 0) ? 'outQuart' : 'inQuart';
			
			sb.browser.scrolling.restart();
			return scrollTo;
	};
	
	scrollTo(o);
	return scrollTo;
	
};