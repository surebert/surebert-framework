sb.include('events.observer');
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
		some_other_od : function(e){
			alert('click');
		}
	}
});

*/
sb.events.idListener = function(params){

	sb.objects.infuse(params, this);
	var self = this;
	this.observe();
};

sb.events.idListener.prototype = {
	/**
	@Name: sb.events.classListener.observe
	@Description: Observes the events the object has. You must rerun this if you are
	adding new events.  Not required if just adding new classname to manage.
	@Return: object The event observer
	@Example:
	myListener.unobserve();
	*/
	observe : function(){
		var self = this;
		this.events = {};
		['click', 'mouseup', 'mousedown', 'dblclick', 'submit', 'keydown', 'keyup', 'keypress', 'mousemove', 'mouseover', 'mouseout'].forEach(function(evt){

			if(self[evt]){
				self.events[evt] = function(e){
					self.delegate(e);
				}
			}
		});


		return sb.events.observer.observe(this);
	},

	/**
	@Name: sb.events.classListener.observe
	@Description: Observes the events the object has
	@Return: object The event observer
	@Example:
	myListener.unobserve();
	*/
	unobserve : function(){
		return sb.events.observer.unobserve(this);
	},

	/**
	@Name: sb.events.classListener.delegate
	@Description: Used Internally to determine if target has className
	*/
	delegate : function(e){
		var target = e.target;
		if(target.id == ''){return false;}

		var id = target.id.replace(' ', '_');

		var type = e.type;

		if(this[type] && typeof this[type][id] == 'function'){

			this[type][id](e);
		}
	}
};