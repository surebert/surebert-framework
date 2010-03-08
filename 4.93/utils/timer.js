/**
@Name: sb.utils.timer
@Description: A constructor to create a timer, which is an event firing timer that repeats on an increment
@Param: Object o
handler - the function to fire after the delay
seconds - the number of seconds to wait before firing
milliseconds - the number of milliseconds to wait before firing
@Return Object timer object with counter, begin, end, reset, restart, and changeInterval properties and onend, onbegin event handlers
@Example:

//create a new timer
var myTimer = new sb.utils.timer({
	//set the number of seconds between intervals - alternatively you can set the number of milliseconds
	seconds : 0.01,
	//set the function that will fire reapeatedly on interval
	handler : function(){
		//reference the this.count property of the time to make sure it has fired less than 255 times
		if(this.count <255){
			//set the document title to the count for debugging
			document.title = this.count;
			
			//set the background color of the body to incremement with the count
			document.body.style.backgroundColor = 'rgb('+this.count+',2,3)';
		} else {
			//it has fired 255 times, end the timer
			this.end();
		}
	}
});

//start the timer;
myTimer.begin();
*/
sb.utils.timer = function(o){
	this.handler = o.handler || function(){};
	this.interval = o.milliseconds || 1000;
	if(typeof o.seconds!='undefined'){
		this.interval = o.seconds*1000;
	}
	var t =this;
};

/**
@Name: sb.utils.timer.prototype
@Description: These properties are avaiable to any sb.utils.timer instance.  All sb.utils.timer.prototype examples below assume a timer name myTimer was instantiated previously in the code
@Example:
//creates a timer which changes the document's title to the current date every 2 seconds
var myTimer = new sb.utils.timer({
	handler: function(){
		document.title = new Date();
	},
	seconds : 2
});

*/
sb.utils.timer.prototype = {
	count : 0,
	/**
	@Name: sb.utils.timer.prototype.begin
	@Description: begins the repeat event firing based on the interval specified for a sb.utils.timer instance, if an onbegin property was set than it fires on first firing
	@Example:
	myTimer.begin();
	*/
	begin : function() {
	
		var t = this;
		this.end();
		this.repeater = window.setInterval(function () {
			if (typeof t.handler  == "function"){
				
				t.handler();
				t.count++;
			}
		}, this.interval);
		
		if (typeof(this.onbegin) == "function"){
			this.onbegin();
		}
		
	},
	
	/**
	@Name: sb.utils.timer.prototype.end
	@Description: ends the repeat event firing, if an onbegin property was set than it fires on first firing
	@Param: Boolean resetCount Optional argument. If set the timer instance's counter is reset.  I would suggest just calling myTimer.reset which ends the timer and resets automatically.
	@Example:
	myTimer.end();
	*/
	end : function (resetCount) {
		resetCount = resetCount || 0;
		if (this.repeater !== null){window.clearInterval(this.repeater);}
		
		if(resetCount===1){this.count=0;}
		
		if (typeof(this.onend) == "function"){
			this.onend();
		}
	},
	
	/**
	@Name: sb.utils.timer.prototype.reset
	@Description: ends the repeat event firing and resets the count property on a sb.utils.timer instance
	@Example:
	myTimer.reset();
	*/
	reset : function(){
		this.end(1);
	},
	
	/**
	@Name: sb.utils.timer.prototype.restart
	@Description: ends the repeat event firing and resets the count property and starts the timer again for an sb.utils.timer instance
	@Example:
	myTimer.restart();
	*/
	restart : function(){
		this.end(1);
		this.begin();
	},
	
	/**
	@Name: sb.utils.timer.prototype.restart
	@Description: ends the repeat event firing and resets the count property and starts it again for an sb.utils.timer instance.
	@Example:
	myTimer.changeInterval({seconds :10});
	*/
	changeInterval : function(o){
		this.interval = o.milliseconds || 1000;
		if(typeof o.seconds!='undefined'){
			this.interval = o.seconds*1000;
		}
		this.end();
		this.begin();
	}
};