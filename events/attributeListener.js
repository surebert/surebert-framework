sb.include('events.observer');
sb.include('events.listener');
/**
@Name: sb.events.attributeListener
@Description: Used to create an eventlistener that responds to events on nodes
with a specific className or combo of classNames in order.  Observes by default
@Param: object params Used to preseed the listener with class listeners
@Return: type desc
@Example:
var myListener = new sb.events.attributeListener({
	attribute: 'some_attribute',
	mousemove : {
		some_attribute : function(e){
			console.log('move '+ new Date());
		}
	},
	click : {
		some_attribute : function(e){
			alert('click');
		}
	}
});

*/
sb.events.attributeListener = function(params){
	return sb.events.listener.apply(this, arguments);
};
sb.events.attributeListener.prototype = new sb.events.listener();
sb.events.attributeListener.prototype.delegate = function(e){
	var target = e.target;
	var attr = target.getAttribute(this.attribute);
	if(!attr){return false;}

	attr = attr.replace(' ', '_');

	var type = e.type;

	if(this[type] && typeof this[type][attr] == 'function'){

		this[type][attr](e);
	}
};