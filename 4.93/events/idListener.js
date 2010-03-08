sb.include('events.observer');
sb.include('events.listener');
/**
@Name: sb.events.idListener
@Description: Used to create an idListener that responds to events on nodes.
@Param: object params Used to preseed the listener with id listeners
@Return: type desc
@Example:
var myListener = new sb.events.idListener({
	mousemove : {
		some_id1 : function(e){
			console.log('move '+ new Date());
		}
	},
	click : {
		some_other_id : function(e){
			alert('click');
		}
	}
});

*/
sb.events.idListener = function(params){
	return sb.events.listener.apply(this, arguments);
};

sb.events.idListener.prototype = new sb.events.listener();
sb.events.idListener.prototype.delegate = function(e){
	var target = e.target;
	if(target.id == ''){return false;}

	var id = target.id.replace(' ', '_');

	var type = e.type;

	if(this[type] && typeof this[type][id] == 'function'){

		this[type][id](e);
	}
};