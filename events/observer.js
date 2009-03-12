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
// sb.events.observer.myObserver(eventListener);
*/

sb.events.observer = {
	
	/**
	@Name: sb.events.observer.html
	@Description: A reference to the HTML node which captures all events
	*/
	html : $('html'),

	/**
	@Name: sb.events.observer.delegateEvents
	@Description: Delgates all the events for the system to the various other modules which may be loaded.  This prevents having to add many duplicate handlers
	*/
	delegateEvents : function(e){

		sb.events.observer.eventHandlers.forEach(function(v){
		
			if(v.events[e.type]){
				v.events[e.type](e);
			}
		});
		
	},
	
	/**
	@Name: sb.events.observer.observeFormSubmits
	@Description: In IE 6 and IE7, there the document object has no submit event to capture all forms submits, this function forces any currenly existing form, the use the events observers submit handler.  It is automatically called on sb.events.observer.init to handle any forms on the page, and then is used by sb.element when new forms are created and added to the DOM in IE 6 or 7
	* 
	*/
	observeFormSubmits : function(){
		var self = this;
		
		$('form').nodes.forEach(function(v){
			if(!v._sb_on_submit){
				v._sb_on_submit = sb.events.add(v, 'submit', self.delegateEvents);
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
	
	eventHandlers : [],
	
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
			submit : this.delegateEvents
		});
		
		//handle keyups
		this.documentKeyUp = sb.events.add(document, 'keyup', this.delegateEvents);
		
		//handle keydowns
		this.documentKeyDown = sb.events.add(document, 'keydown', this.delegateEvents);
		
		//handle keypresses
		this.documentKeyPress = sb.events.add(document, 'keypress', this.delegateEvents);
		
		if(sb.browser.agent == 'ie' && sb.browser.version < 8){
			this.observeFormSubmits();
		}
		
	}
};

sb.events.observer.init();