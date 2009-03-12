/**
 * sb.events.observer.observe(myObj);
 */
 
sb.events.observer = {
	
	html : $('html'),

	/**
	@Name: app.delegateEvents
	@Description: Delgates all the events for the system to the various other modules which may be loaded.  This prevents having to add many duplicate handlers
	*/
	delegateEvents : function(e){

		sb.events.observer.eventHandlers.forEach(function(v){
		
			if(v.events[e.type]){
				v.events[e.type](e);
			}
		});
		
	},
	
	observeFormSubmits : function(){
		var self = this;
		
		$('form').nodes.forEach(function(v){
			if(!v._sb_on_submit){
				v._sb_on_submit = sb.events.add(v, 'submit', self.delegateEvents);
			}
		});
	},
	
	observe : function(eventHandler){
		
		if(!this.eventHandlers.inArray(eventHandler)){
			
			this.eventHandlers.push(eventHandler);
		}
	},
	
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

/*
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
 
sb.events.observer.observe(eventListener);*/
