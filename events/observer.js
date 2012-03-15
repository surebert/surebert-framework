/**
@Name: sb.events.observer
@Version: 1.0 03/11/09 03/11/09
@Description: sets up a global event dispatcher that passes global events to events listeners you set it to observe
@Param: Object event An event reference as passed to a handler function as e
@Return: Return sthe event listener itself, can be used to remove the event listener by passing as the argument to sb.events.observer.unobserve();
@Example:

var eventListener = {
	events : {
		click : function(e){
			alert('click '+e.target);
		},
		submit : function(e){
			e.preventDefault();
			alert('submit');
		}
		
	}
	
};
 
sb.events.observer.observe(eventListener);
//then you can remove with
// sb.events.observer.unobserve(eventListener);
*/

sb.events.observer = {
	
	/**
	@Name: sb.events.observer.html
	@Description: A reference to the HTML node which captures all events
	*/
	html : sb.$('html'),

	/**
	@Name: sb.events.observer.delegateEvents
	@Description: Used internally.  Delgates all the events for the system to
    the various other modules which may be loaded.  This prevents having to
    add many duplicate handlers
	*/
	delegateEvents : function(e){
		
		sb.events.observer.eventHandlers.forEach(function(v){
			if(v.events && v.events[e.type]){
				v.events[e.type](e);
			}
		});
		
	},
	
	/**
	@Name: sb.events.observer.observe
	@Param: adds an eventHandler
	@Description: See sample at top of page
	* 
	*/
	observe : function(eventHandler){
		
		if(!this.eventHandlers.inArray(eventHandler)){
			this.eventHandlers.push(eventHandler);
		}
	},
	
	/**
	@Name: sb.events.observer.unobserve
	@Param: removes an eventHandler
	@Description: See sample at top of page
	* 
	*/
	unobserve : function(eventHandler){
		this.eventHandlers = this.eventHandlers.remove(eventHandler);
	},

	/**
	@Name: sb.events.observer.getEvents
	@Description: used internally.  These events are handled by the observer
	*/
	getEvents : function(){
		return ['change', 'click', 'mouseup', 'mousedown', 'dblclick', 'contextmenu', 'submit', 'keydown', 'keyup', 'keypress', 'mousemove', 'mouseover', 'mouseout', 'dragstart', 'dragend', 'drag', 'dragenter', 'dragleave', 'drop'];
	},

	/**
	@Name: sb.events.observer.handleIEEventBubbles
	@Description: used internally to handle bubbling of submit and change events in
	IE by assigning them on mousedown to the select or submit inputs
	*/
	handleIEEventBubbles : function(){
		sb.events.add(document, 'mousedown', function(e){
			var t= e.target;
			var nn = t.nodeName;
			if(nn === 'SELECT'){
				if(!t.attr('sb_ie_onchange_bubbler')){
					t.attr('sb_ie_onchange_bubbler', 1);
					sb.events.add(t, 'change', sb.events.observer.delegateEvents);
				}
			} else if((nn === 'INPUT' && t.type === 'submit') || nn === 'BUTTON'){
				var form = t.getContaining('form');
				if(form && !form.attr('sb_ie_onsubmit_bubbler')){
					form.attr('sb_ie_onsubmit_bubbler', 1);
					sb.events.add(form, 'submit', sb.events.observer.delegateEvents);
				}
			}

		});
	},
	
	/**
	@Name: sb.events.observer.init
	@Description: Initializes the event observer
	*/
	init : function(){
		this.eventHandlers = [];
		this.html.events({
			click : this.delegateEvents,
			mousedown : this.delegateEvents,
			dblclick : this.delegateEvents,
			mouseover : this.delegateEvents,
			mouseout : this.delegateEvents,
			mousemove : this.delegateEvents,
			drag : this.delegateEvents,
			dragstart : this.delegateEvents,
			dragend : this.delegateEvents,
			dragenter : this.delegateEvents,
			dragleave : this.delegateEvents,
			drop : this.delegateEvents,
			submit : this.delegateEvents,
			change : this.delegateEvents
		});
		
		//handle keyups
		this.documentKeyUp = sb.events.add(document, 'keyup', this.delegateEvents);

		//handle contextmenu
		this.documentContextMenu = sb.events.add(document, 'contextmenu', this.delegateEvents);

		//handle keydowns
		this.documentKeyDown = sb.events.add(document, 'keydown', this.delegateEvents);
		
		//handle keypresses
		this.documentKeyPress = sb.events.add(document, 'keypress', this.delegateEvents);
		
		if(sb.browser.agent === 'ie' && sb.browser.version < 9){
		
			this.handleIEEventBubbles();
		}

	}
};

sb.events.observer.init();