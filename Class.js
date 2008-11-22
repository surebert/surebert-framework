/**
@Name: sb.cookies
@Author: Paul Visco
@Version: 0.9 11-21-2008 11-22-2008
@Description: Expirimental - do not use for produciton code yet - Used to create extensible classes
@Example:
sb.include('Class');
var Person = sb.Class({
	init : function(name){
		this.name = name;
	},
	type : 'Person',
	say : function(){
		sb.consol.log('I am an person');
	}
});

var Employee = Person.extend({
	init : function(name, id){
	
		this.name = name;
		//this.Person.init.call(this, name, id);
		this.id = id;
	},
	type : 'Employee',
	say : function(){
		
		this.parent.say();
		sb.consol.log('I am an employee');
	}
});

var Manager = Employee.extend({
	init : function(name, id, super_id){
		this.Employee.init.call(this, name, id);
		//this.Employee.init.call(this, name, id);
		this.super_id = super_id;
	},
	type : 'Manager',
	say : function(){
		this.parent.say();
		sb.consol.log('I am a manager');
	}
});


var y = new Manager('paul', 14650, 3009);

//alert(y.getParents());
y.say();
sb.consol.dump(y);

*/
sb.Class = function(o){
	
	var c = function(p){
	alert(p);
		//this.parent.init = o.init;
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
		
		var n = new sb.Class(o2);
		
	
		n.prototype.getParentInit = function(){
			return o.init;
		};
		
		n.prototype.isa = function(t){
			
			
			return this.getParents().inArray(t);
			
		}

		n.prototype.getParents = function(x){
			parents = [this.type];
			var parent = this.parent;
			do{
				parents.push(parent.type);
			} while (parent = parent.parent);

			return parents;
		};
		
		n.prototype.parent = this.prototype;
		
		n.prototype[o.type] = {
			init  : this.prototype.init
		};
	
		/*n.prototype[o.type] = {
			init  : this.prototype.init
		};*/
		return n;
	};
	
	
	return c;
};

var Person = sb.Class({
	init : function(name){
		this.name = name;
	},
	type : 'Person',
	say : function(){
		sb.consol.log('I am an person');
	}
});

var Employee = Person.extend({
	init : function(name, id){
	
		this.name = name;
		//this.Person.init.call(this, name, id);
		this.id = id;
	},
	type : 'Employee',
	say : function(){
		
		this.parent.say();
		sb.consol.log('I am an employee');
	}
});

var Manager = Employee.extend({
	init : function(name, id, super_id){
		this.Employee.init.call(this, name, id);
		//this.Employee.init.call(this, name, id);
		this.super_id = super_id;
	},
	type : 'Manager',
	say : function(){
		this.parent.say();
		sb.consol.log('I am a manager');
	}
});
