/**
@Name: sb.cookies
@Author: Paul Visco
@Version: 0.9 11-21-2008 11-22-2008
@Description: Expirimental - do not use for produciton code yet - Used to create extensible classes
@Example:
sb.include('Class');

var Person = sb.Class({
	init : function(){
		this.name = 'jimmy';
	},
	type : 'person',
	say : function(){
		sb.consol.log('I am an person');
	}
});

var Employee = Person.extend({
	init : function(name, id){
		this.name = name;
		this.id = id;
	},
	type : 'employee',
	say : function(){
		this.parent.say();
		sb.consol.log('I am an employee');
	}
});

*/
sb.Class = function(o){
	
	var c = function(){
		
		if(typeof o.init == 'function'){
			
			o.init.apply(this, arguments);
	
		}
		
	};
	
	c.prototype.typeOf = function(){
		return o.type;
	}

	for(var prop in o){
		c.prototype[prop] = o[prop];
	}
	//c.prototype.init = o.init;
	c.type = o.type || 'Class';
	
	c.extend = function(o2){
		
		for(prop in c.prototype){
			
			if(!o2[prop]){
				o2[prop] = c.prototype[prop];
			} 
		}
		 
		var n = sb.Class(o2);
		
		n.prototype.isa = function(t){
			var types = [this.type];
			var parent = this.parent;
			do{
				types.push(parent.type);
			} while (parent = parent.parent);
		
			return types.inArray(t);
			
		}
		
		for(var prop in o2){
			n.prototype[prop] = o2[prop];
		}
		n.prototype.parent = this.prototype;
	
		n.prototype.parent.init = function(){
			
			return o.init.apply(n.prototype, arguments);
		};
		
		return n;
	};
	
	return c;
};