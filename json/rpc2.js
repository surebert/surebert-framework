sb.include('json.encode');
sb.json.rpc2 = {};

/**
 @Name: sb.json.rpc2.request
 @Author: Paul Visco v1.02 02/09/09
 @Description:  Models a JSON rpc2 request
 @Param: Object o
 o.method String The name of the remote procedure (method) to call
 o.params Array/Object Either an array or object with the values to send
 @Return: JSON object with preoprties id, result or id and error.  The result holds the result from the remote procedure.  If there is an error, the response has an error object property, that in turn has a code and message property.
 @Example:
 var f = {
 	name : 'fred'
 };
 sb.json.encode(f);
*/
sb.json.rpc2.request = function(o){
	this.jsonRPC = 'jsonrpc2';
	this.method = o.method || '';
	this.params = o.params || [];
	this.id = o.id || sb.uniqueID();
};

/**
 @Name: sb.json.rpc2.client
 @Author: Paul Visco v1.02 02/09/09
 @Description:  Makes a request to a JSON rpc2 server 
 @Param: Object o
 o.debug boolean Debugs message to sb.consol
 o.url String the url to send the request to
 o.request sb.json.rpc2.request An instance of sb.json.rpc2.request that makes up the request
 o.onResponse The function that fires when the data is returned
 @Return: String in JSON format
 @Example:
//create the client
var client = new sb.json.rpc2.client({
	debug : 1,
	url : '/json/server',
	onResponse : function(r){
		alert(r.result);
	}
});

//dispatch the request
client.dispatch(new sb.json.rpc2.request({
		method : 'add',
		params : [1,2]
});
*/

sb.json.rpc2.client = function(o){
	
	if(o.request instanceof sb.json.rpc2.request){
		this.request = o.request;
	}

	this.debug = o.debug;
	this.url = o.url;
	this.onResponse = o.onResponse;
	delete o;
	
	return this;
};

sb.json.rpc2.client.prototype = {

	setRequest : function(request){
		this.request = request;
	},
	
	dispatch : function(request){
		
		if(request instanceof sb.json.rpc2.request){
			this.request = request;
		}
		
		if(!this.request instanceof sb.json.rpc2.request){
			throw('request must be an instance of sb.json.rpc2.request');
		}
	 
		var transport = new sb.ajax({
			method : 'post',
			format : 'json',
			debug : this.debug,
			url : this.url,
			data : sb.json.encode(this.request)
		});	
		
		if(typeof this.onResponse == 'function'){
			transport.onResponse = this.onResponse;
		}
	
		transport.fetch();
	}
};