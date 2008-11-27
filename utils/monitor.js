/**
@Name: sb.utils.monitor
@Description: Monitors a url for a sb.utils.monitor header to verify that the connection is avaiable
@Params: object o
o.url String The url of the resource to monitor
o.onConnected Function The call back function that fires when the monitor senses a connection and is provided the header "sb.utils.monitor:1" fromt he url specifed
o.onDisconnected Function The callback function that fires when the monitor senses a disconnection by not being able to reach the file, or when the file sends a header of "sb.utils.monitor:0" 
o.interval Integer The number of milliseconds to wait between checks
o.data String the data value pairs passed ot the url being monitored
o.method String get/post The method used when requesting the resource
*/
sb.utils.monitor = function(o){
	o = o || {};
	this.onConnected = o.onConnected || this.onConnected;
	this.onDisconnected = o.onDisconnected || this.onDisconnected;
	this.interval = o.interval || this.interval;
	this.data = o.data || this.data;
	this.method = o.method || this.method;
	if(o.url){
		this.url = o.url;
	} else {
		throw("You must specify a url for you sb.utils.monitor");
	}
	
};

sb.utils.monitor.prototype = {
	
	url : '',
	
	interval : 1000,
	
	onConnected : function(){},
	
	onDisconnected : function(){},
	
	ping : 0,
	
	data : '',
	method : 'get',

	stop : function(){
		if(typeof this.pinger !='undefined'){
			window.clearInterval(this.pinger);
		}
	},
	
	start : function(){
		var self =this;
		
		if(this.ping == 0){
			this.ping = new sb.ajax({
				url : this.url,
				data : this.data,
				debug : 0,
				method : this.method,
				format : 'head',
				header : 'sb.utils.monitor',
				onResponse : function(r){
					
					window.clearTimeout(self.timeout);
					if(r==1){
						self.onConnected();
					} else {
						self.onDisconnected();
					}
				}
				
			});
		}
		
		var self =this;
		this.pinger = window.setInterval(function(){
		
				self.timeout = window.setTimeout(function(){
					self.onDisconnected();
				}, this.interval+5);
				self.ping.fetch();
			
		}, this.interval);
	
	}
};