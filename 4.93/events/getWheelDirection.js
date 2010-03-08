/**
@Name: sb.events.getWheelDirection
@Description: gets the mouse wheel delta e.g. the direction and amount it is being spun
@Param: Object event An event reference as passed to a handler function as e
@Return: Number The wheel delta either 1 or -1 depending on the direction
@Example:
function printDelta(e){
	document.title = sb.events.getWheelDirection(e);
}

//firefox/safari/opera
sb.events.add('#box', 'DOMMouseScroll', printDelta);
//ie
sb.events.add('#box', 'mousewheel', printDelta);
*/
sb.events.getWheelDirection = function(e){
	var delta = 0;
	if (e.wheelDelta) {
		delta = e.wheelDelta;
		if (window.opera) {delta = -delta;}
	} else if (e.detail) { delta = -e.detail;}
	delta = Math.round(delta);
	return (delta > 0 ) ? 1 : -1;
};