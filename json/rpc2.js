sb.include('json.encode');
sb.json.rpc2 = {};

sb.json.rpc2.request = function(o){
	this.jsonRPC = 'jsonrpc2';
	this.method = o.method || '';
	this.params = o.params || [];
	this.id = o.id || sb.uniqueID();
};

sb.json.rpc2.client = function(o){

	this.transport = new sb.ajax({
		method : 'post',
		format : 'json',
		debug : o.debug || false
	});	
	
	if(o.url == ''){
		throw('You must specify a url');
	} else {
		this.url = o.url || '';
	}
	
	if(o.request instanceof sb.json.rpc2.request){
		this.request = o.request;
	} else {
		throw('request must be an instance of sb.json.rpc2.request');
	}
	
	if(typeof o.onResponse == 'function'){
		this.transport.onResponse = o.onResponse;
	}

	return this;
};

sb.json.rpc2.client.prototype = {

	setRequest : function(request){
		this.request = request;
	},
	
	dispatch : function(){
	
		this.transport.url = this.url;
		this.transport.data = sb.json.encode(this.request);
		this.transport.fetch();
	}
};