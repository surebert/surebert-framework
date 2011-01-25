sb.include('String.prototype.stripHTML');
/**
@Name: sb.browser.pulsingTitle
@Description: pulses document title between default and custom to grab user attention
@Param string title The custom title to pulse
@Param integer interval The frequency to pulse at in ms
@Example:
var p = new sb.browser.pulsingTitle('dancing');
//stop pulsing
p.stop();
*/
sb.browser.pulsingTitle = function(title, interval){
	this.title = title || 'pulsing';
	this.interval = interval || 2000;

	if(sb.browser.pulsingTitle.instances > 0){
		return sb.browser.pulseTitle.instance;
	}

	this.origTitle = document.title;
	
	sb.browser.pulsingTitle.instance = this;
	
	if(this.title){
		this.pulse(this.title);
	}

};

sb.browser.pulsingTitle.prototype = {
	/**
	@Name: sb.browser.pulsingTitle.prototype.stop()
	@Description: stops pulsing
	@Example:
	var p = new sb.browser.pulsingTitle('dancing');
	//stop pulsing
	p.stop();
	*/
	stop : function(){
		document.title = this.origTitle;
		if(this.isPulsing){
			window.clearInterval(this.isPulsing);
		}
		return this;
	},

	/**
	@Name: sb.browser.pulsingTitle.prototype.stop()
	@Description: starts pulsing, does this by default when constructor is used, can be used to restart
	@Example:
	var p = new sb.browser.pulsingTitle('dancing');
	//stop pulsing
	p.stop();
	//start pulsing again
	p.pulse();
	*/
	pulse : function(title){
		var self = this;
		this.stop();
		if(title == false){
			intra.resetTitle();
			return;
		}
		var x =0;

		this.isPulsing = window.setInterval(function(){
			if(x % 2 == 0){
				document.title = title;
			} else {
				document.title = self.origTitle;
			}
			x++;

		}, this.interval);
		return this;
	}
};