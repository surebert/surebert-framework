sb.storage = function(name){
	this.name = name || 'sb_storage';
	if(sb.browser.agent == 'ie'){
		this.storage = new sb.storage.userData(this.name);
	} else if(window.localStorage){
		this.storage = new sb.storage.local(window.location.host);
	} else {
		this.storage = sb.cookies;
	}
};
sb.storage.prototype = {
	set : function(key, val){
		return this.storage.set(key, val);
	},
	get : function(key){
		return this.storage.get(key);
	}
};

sb.storage.userData = function(name){
	this.name = name || 'sb_storage';
	if(sb.browser.agent != 'ie'){
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
	}
};

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
	clear : function(key){
		return window.localStorage.clear();
	}
};