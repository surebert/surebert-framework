/**
@Name: sb.swf
@Description: A constructor used to embed macromedia flash swf, youtube videos, etc. Do not set the sb.swf object's id property to the same name as the object itself. e.g. if your swf object = var mySwf do not set it's id property to 'mySwf' as Internet Explorer will throw an error.
@Param: Object params An objectw ith all the properties of the swf should have once embedded
	src - the url of the flash swf to be loaded
	width - The width that the swf file is displayed at
	height - The height that the swf file is displayed at
	bgColor - The backgroudn color fo the swf file
	version - The minimum version of flash required to display the swf, else the instances alt property data is returned when running the instances toHTML() or embed() method
	alt - Alternative data to be displayed if the user does not have the minimum version of flash required.  This can be HTML or string data.
	id - the id of the new node returned
	flashvars - an object whose properties are converted to key/value pairs and passed to the swf objects FlashVars property
@Return: Object An sb.swf instance with the properties specifed in the param argument and by sb.swf.prototype
@Example:
var mySwf = new sb.swf({
	src : 'surebert.swf',
	bgColor : '#000000',
	wmode : 'transparent',
	width : 400,
	height : 300,
	version : 8,
	id : 'swify',
	flashvars : {
		name : 'paul',
		age : 30
	},
	alt : '<div>You need at least flashplayer 8 to play the swf</div>'
});
*/
sb.swf = function(params){
	if(typeof params == 'object'){
		sb.objects.infuse(params, this);
	} 
	this.width = this.width || '400px';
	this.height = this.height || '300px';
	this.bgColor = this.bgColor || '#FFFFFF';
	this.version = this.version || 5;
	this.allowFullScreen = this.allowFullScreen || 'true';
	this.alt = this.alt || '';
	this.src = this.src || '';
	this.wmode = this.wmode || '';
	if(typeof this.id =='undefined'){
		this.id = 'sb_swf_'+sb.swf.instanceId;
		sb.swf.instanceId++;
	}
};

/**
@Name: sb.swf.prototype
@Description: The properties of any sb.swf instance. All sb.swf.prototype examples below assume the following sb.swf example object was created
@Example: 
var mySwf = new sb.swf({
	src : 'surebert.swf',
	bgColor : '#000000',
	wmode : 'transparent',
	width : 400,
	height : 300,
	version : 8,
	id : 'swify',
	alt : '<div>You need at least flashplayer 8 to play the swf</div>'
});
*/
sb.swf.prototype = {
	
	/**
	@Name: sb.swf.prototype.toHTML
	@Description: Converts the sb.swf into the appropriate swf code for the browser being used by the client
	@Return: String The HTML code required to embed the flash swf into the DOM, can be used as the innerHTML of another element
	@Example:
	var string = mySwf.toHTML();
	//on embed style browsers returns
	<embed type="application/x-shockwave-flash" src="mySwf.swf" id="mySwf" wmode="transparent" allowScriptAccess="always" bgcolor="#000000" width="400" height="300" />
	
	//on explorer returns
	<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="400" height="300" id="mySwf"><param name="movie" value="mySwf.swf" /><param name="bgcolor" value="#000000" /><param name="wmode" value="transparent" /><param name="allowScriptAccess" value="always" /></object>
	*/
	toHTML : function(){
		var html='';
		
		if(this.version > sb.swf.version){
			return this.alt;
		}
		
		if(sb.swf.format=='embed'){
			
			html = '<embed type="application/x-shockwave-flash" src="'+this.src+'"  id="'+this.id+'" wmode="'+this.wmode+'" allowScriptAccess="always" allowFullScreen="'+this.allowFullScreen+'" bgcolor="'+this.bgColor+'" ';
				
			if(typeof this.flashvars =='object'){
				
				html +='FlashVars="'+sb.objects.serialize(this.flashvars)+'" ';
			
			}
			
			html +=' width="'+this.width+'" height="'+this.height+'"  />';
		
		} else if(sb.swf.format=='object'){
				html = '<object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" width="'+this.width+'" height="'+this.height+'" id="'+this.id+'"><param name="movie" value="'+this.src+'" /><param name="bgcolor" value="'+this.bgColor+'" /><param name="wmode" value="'+this.wmode+'" /><param name="allowFullScreen" value="'+this.allowFullScreen+'" /><param name="allowScriptAccess" value="always" />';
				if(typeof this.flashvars =='object'){
					html +='<param name="FlashVars" value="'+sb.objects.serialize(this.flashvars)+'">';
				}
				html +='</object>';
		}
		return html;
		
	},
	
	/**
	@Name: sb.swf.prototype.embed
	@Description: Converts the sb.swf into the appropriate swf code for the browser being used by the client and inserts that code into the element specified
	@Param: element el Either an object reference to a dom element or a string reference that can be passed through sb.$ e.g. use the elements id '#someElement'
	@Return: String The HTML code required to embed the flash swf into the DOM, can be used as the innerHTML of another element
	@Example:
	mySwf.embed('#someElement');
	*/
	embed : function(el){
		sb.$(el).innerHTML =this.toHTML();
		return sb.$('#'+this.id);
		
	}
};

sb.swf.infuse = sb.objects.infuse;

sb.swf.infuse({
	/**
	@Name: sb.swf.version
	@Description: Used Internally
	*/
	version : 4,
	
	/**
	@Name: sb.swf.swfs
	@Description: Used Internally
	*/
	swfs : [],

	/**
	@Name: sb.swf.instanceId
	@Description: Used Internally
	*/
	instanceId : 0,
	
	/**
	@Name: sb.swf.check
	@Description: Used Internally
	*/
	check : function(){
		var version, description;
		try{
			version = new RegExp("\\d{1}\.\\d{0,5}", "i");
			if(window.navigator.plugins["Shockwave Flash"]){
				description = window.navigator.plugins["Shockwave Flash"].description;
				if(description.match(version)){
					sb.swf.version = description.match(version);
				}
				
			}
		} catch(e){sb.swf.version=0;}
		return sb.swf.version;
	},
	
	/**
	@Name: sb.swf.testIe
	@Description: Used Internally
	*/
	testIe : function(){
		try{
			
			if(new window.ActiveXObject("ShockwaveFlash.ShockwaveFlash." + sb.swf.version)){
				return false;
			}
		} catch(e){return true;}
			
	},
	
	/**
	@Name: sb.swf.ieCheck
	@Description: Used Internally
	*/
	ieCheck : function(){	
		try{
			//THERE MUST BE A BETTER SOLUTION
			while(!sb.swf.testIe()){
				sb.swf.version++;
			}
			sb.swf.version--;
			return sb.swf.version;
		} catch(e){
			return true;
		}
	},
	
	/**
	@Name: sb.swf.cleanup
	@Description: Used Internally
	*/
	cleanup : function() {
		try{
		sb.$('object').forEach(function(obj){
			obj.style.display='none';
			for(var prop in obj){
				if(typeof obj == 'function'){obj[prop] = function(){};}
			}
		});
		}catch(e){}
	},
	
	/**
	@Name: sb.swf.unload
	@Description: Used Internally
	*/
	unload : function() {
		__flash_unloadHandler = function(){};
		__flash_savedUnloadHandler = function(){};
		window.attachEvent( "onunload", sb.swf.cleanup );
		
	},
	
	/**
	@Name: sb.swf.detect
	@Description: Used Internally
	*/
	detect : function(){
		for(var x=0;x<window.navigator.plugins.length;x++){
		//	sb.objects.alert(navigator.plugins[x]);
		}
		if (window.navigator.plugins && window.navigator.plugins.length){
			sb.swf.format = 'embed';
			return sb.swf.check();
		} else if(sb.browser.agent =='ie'){
			sb.swf.format = 'object';
			return sb.swf.ieCheck();
		} 
	}
	
});

sb.swf.detect();

if(sb.browser.ie6){
	
	//fix for bad adobe code in IE
	var __flash__removeCallback = function(instance, name){
		if(typeof instance != 'null'){
			instance[name] = null;
		}
	};

	//cleanup flash players for IE
	window.attachEvent( "onbeforeunload", sb.swf.unload);
}