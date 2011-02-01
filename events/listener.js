sb.include('events.observer');
/**
@Name: sb.events.listener
@Description: Used Internally for other event listeners
@Param: object params Used to preseed the listener with class listeners
@Return: object
*/
sb.events.listener = function(params){
	sb.objects.infuse(params, this);
	this.observe();
};
sb.events.listener.prototype = {
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
		sb.events.observer.getEvents().forEach(function(evt){

			if(self[evt]){
				self.events[evt] = function(e){
					self.delegate(e);
				};
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
	}
};