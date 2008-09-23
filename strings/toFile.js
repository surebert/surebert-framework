/**
@Name: sb.strings.toFile
@Author: Paul Visco
@Version: 1.0 11/19/07
@Description: transfers the contents of a string to an external file.  Passes the string as POST data with a key name of data.  The data is escaped.  Make sure the external file referenced in the url property of the params object has permissions set to writeable.  There is an example file server side file log.php in the surebert extras folder.  It writes to log.txt in the same folder.
@Param: Object params Parameters defining the data transfer
url - The url of the file to send the string to. Must be a local file because of security in xmlHTTP object
onpass - Function The function that fires if the file the data is sent to returns the number 1. You should set you server side file to print the number 1 if the data arrives.
onfail - Function The optional function that fires anytime the server side file returns a 0.
debug - Boolean If set to 1 then the process is debugged to the sb.consol if sb.developer.js is included in your source.
@Example:
var myString = 'Here is a string';
myString.toFile({
	url : '../extras/log.php',
	onpass : function(){
		alert('you');
	},
	onfail : function(){
		alert('bad');
	},
	debug :1
	
});
*/
sb.strings.toFile = function(params){
	if(typeof params.url =='undefined'){return;}
	
	params.debug = params.debug || 0;
	params.onpass= params.onpass || function(){};
	params.onfail= params.onfail || function(){};
	
	params.handler = params.handler || function(){};
	var xfer = new sb.ajax({
		url:params.url,
		debug:params.debug,
		format :'text',
		method:'post',
		data:'data='+escape(this),
		handler:function(r){
			if(r==1){
				params.onpass();
			} else {
				params.onfail();
			}
		
		}
		
	});

	xfer.fetch();
};
	
String.prototype.toFile = sb.strings.toFile;