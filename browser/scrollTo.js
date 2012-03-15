sb.include('effect');
sb.include('browser.getScrollPosition');
/**
@Name:  sb.browser.scrollTo
@Author: Paul Visco
@Description: Used to gradually scroll the browser somewhere
@Param: Object with the following properties
integer y Where to scroll to on the y axis
integer x Where to scroll to on the x axis
integer duration The time milliseconds to scroll over
function onEnd Fires if it successfully completes scrolling
function onStopped Fires if the user stops the scrolling by initiating their own scrolling
@Example:
//scrolls to y 500, x: 200 and alerts 'done' when complete or stopped of the user scrolls
var scrollTo = sb.browser.scrollTo({
	y : 500,
	x : 200,
	duration : 24,
	onEnd : function(){
		alert('done');
	},
	onStopped : function(){
		alert('stopped');
	});

*/
sb.browser.scrollTo = function(o){
	
	var scrollPos = sb.browser.getScrollPosition();
	var x = scrollPos[0];
	var y = scrollPos[1];
	o.x = typeof o.x == 'undefined' ? x : Math.round(o.x);
	
	o.y = typeof o.y == 'undefined' ? y : Math.round(o.y);
	var stopScroll = function(complete){
		stop.remove();
		stop2.remove();
		window.clearTimeout(scrolling);
		if(complete){
			if(typeof o.onEnd == 'function'){
				o.onEnd();
			}
		} else {
			if(typeof o.onStopped == 'function'){
				o.onStopped();
			}
		}

	};
	var time = 0;
	var duration = o.duration || 15;
	var beginX = x
	var changeX = o.x-x;
	var tweenX = changeX < 1 ? 'outQuart': 'inQuart';
	var beginY = y;
	var changeY = o.y-y;
	var tweenY = changeY < 1 ? 'outQuart': 'inQuart';
	if(o.debug){
		document.title = [beginX, changeX, beginY, changeY];
	}
	var scrolling = window.setInterval(function(){
		if(time >= duration){
			stopScroll();
			return;
		}
		time++;

		if(y != o.y){
			y = sb.effects.tween[tweenY](time,beginY,changeY,duration);
		}

		if(x != o.x){
			x = sb.effects.tween[tweenX](time,beginX,changeX,duration);
		}

		x = Math.round(x);
		y = Math.round(y);

		window.scrollTo(x, y);
		if(x == o.x && y == o.y){
			stopScroll(true);
		}

	}, 10);

	var stop = sb.events.add(document, 'DOMMouseScroll', function(){stopScroll(false);});
	var stop2 = sb.events.add(document, 'mousewheel', function(){stopScroll(false);});

	scrolling.stop = stopScroll;
	return scrolling;
};