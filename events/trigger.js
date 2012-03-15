/**
@Name: sb.events.trigger
@Description: Used to trigger js events
@Example: sb.events.trigger('#myButton', 'click');
*/
sb.events.trigger = function(target, event){
	var target = $(target);
	var event = event || 'click';
	if(document.createEvent){
		var e = document.createEvent('MouseEvents');
		e.initEvent(event, true, true );
		target.dispatchEvent(e);
	} else if(target[event]){
		target[event]();
	}
};