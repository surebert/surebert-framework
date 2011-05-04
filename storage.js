/**
@Name: sb.storage
@Author: Paul Visco
@Description: used to store data clientside.  Uses localStorage if it exists 
Safari, Chrome, Firefox, IE 8+ or UserData IE 6,7 or cookies if neither is available
*/
sb.storage = function(params){
	sb.objects.infuse(params, this);
	this.name = this.name || 'sb_storage';

	if('localStorage' in window && window['localStorage'] !== null){
		this.storage = new sb.storage.local(window.location.host);
	} else if(typeof document.body.style.behavior == 'string'){
		this.storage = new sb.storage.userData(this.name);
	} else {
		sb.include('cookies');
		this.storage = sb.cookies;
	}
};

/**
@Name: sb.storage.prototype
@Author: Paul Visco
*/
sb.storage.prototype = {
	/**
	@Name: sb.storage.prototype.onBeforeGet
	@Description: fires before set is complete so that you can do things like check the engine used
	@Param string key The key that is being set
	@Param string val The value of the key being set
	@Return If it returns false, the set will be canceled
	@Example:
	var s = new sb.storage({
		onBeforeSet : function(key, val){
			if(this.typeOf() == 'sb.cookies'){
				//do something
			}
		}
	});
	*/
	onBeforeGet : function(key){},
	
	/**
	@Name: sb.storage.prototype.onBeforeSet
	@Description: fires before get is complete so that you can do things like check the engine used
	@Param string key The key that is being gotten
	@Return If it returns false, the set will be canceled
	@Example:
	var s = new sb.storage({
		onBeforeGet : function(key){
			if(this.typeOf() == 'sb.cookies'){
				//do something
			}
		}
	});
	*/
	onBeforeSet : function(key, val){},
	
	/**
	@Name: sb.storage.prototype.onBeforeClear
	@Description: fires before the datastore is cleared
	@Return If it returns false, the clearing will be canceled
	@Example:
	var s = new sb.storage({
		onBeforeClear : function(key){
			if(this.typeOf() == 'sb.cookies'){
				//do something
			}
		}
	});
	*/
	onBeforeClear : function(){},
	
	/**
	@Name: sb.storage.prototype.set
	@Description: sets a key, value pair
	@Return: Boolean Returns true if the key was set
	@Example:
	var s = new sb.storage();
	s.set('name', 'paul');
	*/
	set : function(key, val){
		if(this.onBeforeSet(key, val) !== false){
			return this.storage.set(key, val);
		}
		
		return false;
	},
	
	/**
	@Name: sb.storage.prototype.get
	@Description: get a value by key
	@Return: String Return value of the key
	@Example:
	var s = new sb.storage();
	s.get('name');
	*/
	get : function(key){
		if(this.onBeforeGet(key) !== false){
			return this.storage.get(key);
		}
		return false;
	},
	
	/**
	@Name: sb.storage.prototype.clearAll
	@Description: clears all values in storage
	@Example:
	var s = new sb.storage();
	s.clearAll();
	*/
	clearAll : function(){
		if(this.onBeforeClear() !== false){
			return this.storage.clearAll();
		}
	},
	
	/**
	@Name: sb.storage.prototype.typeOf
	@Description: Gets the type of storage engine being used
	@Return: String The type of storage engine being used e.g. sb.storage.local, sb.cookies, sb.userData
	@Example:
	var s = new sb.storage();
	s.clearAll();
	*/
	typeOf : function(){
		return this.storage.typeOf();
	}
};

/**
@Name: sb.storage.prototype.userData
@Description: used internally Interface to store data in IE 6 and 7 where localStorage does not exist
*/
sb.storage.userData = function(name){
	this.name = name || 'sb_storage';
	if(typeof document.body.style.behavior != 'string'){
		throw('sb.storage.userData only works in IE');
	} else {
		this.storage = new sb.element({
			tag : 'div',
			id : 'sb_storage',
			value : '',
			styles : {
				'behavior' : 'url(#default#userdata)',
				'display' : 'none'
			}
		});
		this.storage.appendToTop('body')
	}
};

sb.storage.userData.prototype = {
	set : function(key, val){
		this.storage.setAttribute(key, val);
		try{
			this.storage.save(this.name);
			return true;
		}catch(e){
			return false;
		}
	},
	get : function(key){
		this.storage.load(this.name);
		return this.storage.getAttribute(key);
	},
	typeOf : function(){
		return 'sb.storage.userData';
	}
};

/**
@Name: sb.storage.prototype.local
@Description: used internally Interface to store data in browsers that support localStorage
*/
sb.storage.local = function(){
	if(!window.localStorage){
		throw('requires local strorage');
	}
};

sb.storage.local.prototype = {
	set : function(key, val){
		window.localStorage.setItem(key, val);
	},
	get : function(key){
		return window.localStorage.getItem(key);
	},
	clearAll : function(key){
		return window.localStorage.clear();
	},
	typeOf : function(){
		return 'sb.storage.local';
	}
};