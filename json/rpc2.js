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
  var request = new sb.json.rpc2.request({
	method : 'add',
	params : [1,2]
 });
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

var client = new sb.json.rpc2.client({
	debug : 1,
	url : '/directory/json_service',
	onResponse : function(json){
	
		if(json.error){
			alert(json.error.message);
		} else {
			//do something with the result
			alert(json.result);
		}
		
	}
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

	this.id = sb.json.rpc2.client.instances.length;
	sb.json.rpc2.client.instances.push(this);
	
	return this;
};

sb.json.rpc2.client.instances = [];

sb.json.rpc2.client.prototype = {
	/**
	@Name: sb.json.rpc2.dispatchViaScript
	@Description:  Dispatches a json.rpc2.request via script tag for cross site json service usage
	@Param: sb.json.rpc2.request
	@Return: calls client's onResponse method and passed a json rpc2 response object in json
	@Example:
	//dispatch the request via script tag
	client.dispatchViaScript(new sb.json.rpc2.request({
			method : 'current_user',
			params : ['reid']
	}));
	 */
	dispatchViaScript : function(request){
		sb.include('String.prototype.base64Encode');
		
		if(request instanceof sb.json.rpc2.request){
			this.request = request;
		}
		
		if(!this.request instanceof sb.json.rpc2.request){
			throw('request must be an instance of sb.json.rpc2.request');
		}

		var src = [this.url+'?'];
		src.push('callback=sb.json.rpc2.client.instances['+this.id+'].onResponse');
		src.push('method='+this.request.method);
		src.push('params='+sb.json.encode(this.request.params).base64Encode());
		src.push('id='+this.id);

		var transport = new sb.element({
			tag : 'script',
			type : 'text/javascript',
			src : src.join('&')
		});

		if(this.debug == 1){
			sb.consol.log("Adding script tag to body with src: "+transport.src);
		}
	
		transport.appendTo(document.body);

	},
	
	/**
	@Name: sb.json.rpc2.dispatchV
	@Description:  Dispatches a json.rpc2.request via ajax, only works locally
	@Param: sb.json.rpc2.request
	@Return: calls client's onResponse method and passed a json rpc2 response object in json
	@Example:
	//OR dispatch the request via ajax, local only
	client.dispatch(new sb.json.rpc2.request({
			method : 'current_user',
			params : ['reid']
	}));
	 */
	dispatch : function(request){
		
		if(request instanceof sb.json.rpc2.request){
			this.request = request;
		}
		
		if(!this.request instanceof sb.json.rpc2.request){
			throw('request must be an instance of sb.json.rpc2.request');
		}

		var transport = new sb.ajax({
			method : 'post',
			debug : this.debug,
			url : this.url+'?callback=sb.json.rpc2.client.instances['+this.id+'].onResponse',
			data : sb.json.encode(this.request),
			onResponse : function(r){
				eval(r);
			}
		});	
		
		transport.fetch();
	}
};