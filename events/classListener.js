sb.include('events.observer');
sb.include('events.listener');
/**
@Name: sb.events.classListener
@Description: Used to create an eventlistener that responds to events on nodes
with a specific className or combo of classNames in order.  Observes by default
@Param: object params Used to preseed the listener with class listeners
@Return: type desc
@Example:
var myListener = new sb.events.classListener({
	mousemove : {
		some_classname : function(e){
			console.log('move '+ new Date());
		}
	},
	click : {
		some_classname : function(e){
			alert('click');
		}
	}
});

*/

/**
@Name: sb.events.listener
@Description: Used Internally for other event listeners
@Param: object params Used to preseed the listener with class listeners
@Return: object
*/
/**
@Name: sb.events.listener
@Description: Used Internally for other event listeners
@Param: object params Used to preseed the listener with class listeners
@Return: object
*/
sb.events.classListener = function(params){
	return sb.events.listener.apply(this, arguments);
};

sb.events.classListener.prototype = new sb.events.listener();
sb.events.classListener.prototype.delegate = function(e){

	var cl = e.target.className;
	
	if(cl == ''){return false;}

	cl = cl.replace(' ', '_');
	
	var type = e.type;

	if(this[type] && typeof this[type][cl] == 'function'){

		this[type][cl](e);
	}
};