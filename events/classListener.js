sb.include('events.observer');
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
sb.events.classListener = function(params){

	sb.objects.infuse(params, this);
	var self = this;
	this.observe();
};

sb.events.classListener.prototype = {
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
		['click', 'dblclick', 'submit', 'keydown', 'keyup', 'keypress', 'mousemove', 'mouseover', 'mouseout'].forEach(function(evt){

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
		if(target.className == ''){return false;}

		var cl = target.className.replace(' ', '_');

		var type = e.type;

		if(this[type] && typeof this[type][cl] == 'function'){

			if(target.nodeName != 'INPUT'  && target.type !='checkbox'){
				e.preventDefault();
			}
			this[type][cl](e);
			if(typeof target.blur == 'function'){
				target.blur();
			}


			return;
		}
	}
};