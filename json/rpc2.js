sb.include('json.encode');
sb.json.rpc2 = {};

/**
 @Name: sb.json.rpc2.request
 @Author: Paul Visco v1.02 02/09/09
 @Description:  Models a JSON rpc2 request
 @Param: Object o
 o.method String The name of the remote procedure (method) to call
 o.params Array/Object Either an array or object with the values to send
 o.onResponse The callback handler to manage the data
 @Return: JSON object with preoprties id, result or id and error.  The result holds the result from the remote procedure.  If there is an error, the response has an error object property, that in turn has a code and message property.
 @Example:
  var request = new sb.json.rpc2.request({
	method : 'add',
	params : [1,2],
	onResponse : function(json){
		if(json.error){
			alert(json.error.message);
		} else {
			alert(json.result);
		}
	}
 });
*/
sb.json.rpc2.request = function(o){
	this.jsonRPC = 'jsonrpc2';
	this.method = o.method || '';
	this.params = o.params || [];
	this.onResponse = o.onResponse || function(){}
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

var client = new sb.json.rpc2.client({
	url : '/directory/json_service'
});
*/

sb.json.rpc2.client = function(o){
	
	if(o.request instanceof sb.json.rpc2.request){
		this.request = o.request;
	}

	this.debug = o.debug;
	this.url = o.url;
	this.requests = {};
	delete o;

	this.id = sb.json.rpc2.client.instances.length;
	sb.json.rpc2.client.instances.push(this);
	
	return this;
};

sb.json.rpc2.client.instances = [];

/**
@Name: sb.json.rpc2.callbacks
@Description:  Used internally to map callbacks
 */
sb.json.rpc2.callbacks = function(client_id, request_id){
	var func = sb.json.rpc2.client.instances[client_id].requests[request_id].onResponse;
	sb.json.rpc2.client.instances[client_id].requests[request_id] = null;
	return func;
};

sb.json.rpc2.client.prototype = {

	/**
	@Name: sb.json.rpc2.dispatch
	@Description:  Dispatches a json.rpc2.request via ajax for local, or script for http
	@Param: sb.json.rpc2.request, onResponse
	@Example:
	client.dispatch(new sb.json.rpc2.request({
			method : 'current_user',
			params : ['reid'],
			onResponse : function(json){
				if(json.error){
					alert(json.error.message);
				} else {
					alert(json.result);
				}
			}
	}));
	 */
	dispatch : function(request){
	
		this.requests[request.id] = request;

		
		if(request instanceof sb.json.rpc2.request){
			this.request = request;
		}

		if(!this.request instanceof sb.json.rpc2.request){
			throw('request must be an instance of sb.json.rpc2.request');
		}

		if(this.url.match(/^http/)){
			
			this.dispatchViaScript(request);
		} else {
			this.dispatchViaAjax(request);
		}

	},

	/**
	@Name: sb.json.rpc2.dispatchViaScript
	@Description:  Used internally Dispatches a json.rpc2.request via script tag for cross site json service usage
	 */
	dispatchViaScript : function(request){
		sb.include('String.prototype.base64Encode');

		var src = [this.url+'?'];
		src.push('callback=sb.json.rpc2.callbacks('+this.id+',"'+request.id+'")');
		src.push('method='+this.request.method);
		src.push('params='+sb.json.encode(this.request.params).base64Encode());
		src.push('id='+this.id);

		var s = new sb.script({
			src : src.join('&'),
			onload : function(){
				s.remove();
				s=null;
			}
		});

		s.load();
	},

	/**
	@Name: sb.json.rpc2.dispatchViaAjax
	@Description:  Used internally Dispatches a json.rpc2.request via script tag for cross site json service usage
	 */
	dispatchViaAjax : function(request){
		
		var transport = new sb.ajax({
			method : 'post',
			debug : this.debug,
			url : this.url+'?callback=sb.json.rpc2.callbacks('+this.id+',"'+request.id+'")',
			data : sb.json.encode(this.request),
			onResponse : function(r){
				eval(r);
			}
		});	
		
		transport.fetch();
	}
};