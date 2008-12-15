/**
@Author: Paul Visco of http://paul.estrip.org
@Version: 4.62 04/24/04 - 12/15/08
@Package: surebert
*/

/**
@Name: $
@Description: One of the most important parts of the surebert library. Can reference DOM elements in many way using CSS selectors.  The simplest use of it is to reference DOM elements by their id property.
@Param: String Use CSS selectors to return the elements desired
@Example:
e.g.'#myForm' An element id.  When passed an element ID it returns a reference to the element with that id'

e.g.'body' An tag name.  When passed a tag name it returns an array of all the tags that match that tag name.  If the tag is found in sb.singleTags e.g. body, head, title then only one element is returned instead of an array

e.g. '#myDiv' returns node with the id 'myDiv'

e.g. '.myClass' returns all nodes with the class 'myClass', see also [class="myClass"] below

e.g. '*' returns all nodes

e.g. '#myDiv p' returns all the p tags that are decendents of #myDiv

e.g. '#myDiv > p' returns all the p tags that are direct decendents of #myDiv

e.g. 'p + b' returns all the b tags that are direct adjacent siblings of p tags

e.g. 'div ~ p' a p element preceded by an div element

e.g 'p:first-child' returns all the p tags that are the first child of their parent, be careful its not the first child of each p tag

e.g. 'p:last-child' returns all the p tags that are the last child of their parent

e.g. 'p:empty' returns all the p tags that are empty

e.g  '#myDiv *:not(p)' returns all nodes that are not p tags within #myDiv

e.g  'input[name"choosen"]' returns all the input nodes with the name 'choosen'

e.g. 'a[href="http://www.surebert.com"] return all the a tags that have the href http://www.surebert.com

e.g. 'a[href$="google.com"] return all the a tags that end in google.com

e.g. 'a[href^="http"] return all the a tags that start with http

e.g. 'a[href*="surebert"] return all the a tags that have the substring "surebert" in them

e.g. a[hreflang|="en"]	returns all a tags whose "hreflang" attribute has a hyphen-separated list of values beginning (from the left) with "en"

e.g. 'p[class~="bob"] returns an array of all p tags whose "class" attribute value is a list of space-separated values, one of which is exactly equal to "bob"

e.g. 'p, b, #wrapper' Commas allow you to make multiple selections at once.This example returns all b nodes, all p nodes and node with the id 'wrapper'

e.g  '*:not(p)' LIMITED SUPPORT - returns all nodes that are not p tags
*/

$ = function(selector, root) {
	
	root = root || document;
	
	//return items that are already objects
	if(typeof selector != 'string'){
		
		if(typeof selector == 'object' && selector !== null){
			if(Element.emulated === true && selector.nodeType && selector.nodeType == 1){
				
				var ep = Element.prototype;
				for(var prop in ep){
					selector[prop] = ep[prop];
				}
			} else if (typeof selector.typeOf == 'function' && selector.typeOf() == 'sb.nodeList'){
				selector.getElementPrototypes();
			}
		}
		
		return selector;
	}
	
	var nodeList = new sb.nodeList();
	
	nodeList.setSelector(selector);
	
	if(document.querySelectorAll){
		
		nodeList.add(root.querySelectorAll(selector));
		
	} else {
		$.parseSelectors(nodeList, root);
	}
	
	if(nodeList.length() === 0 && nodeList.selector.match(/^\#\w+$/) ){
		return null;
	} else if(nodeList.length() == 1 && (nodeList.selector.match(/^\#\w+$/) || sb.nodeList.singleTags.some(function(v){return v === nodeList.selector;}))){
	
		return nodeList.nodes[0];
	} else {
		return nodeList;
	}
	
};

/**
@Name: sb.nodeList.parseInheritors
@Param: inheritor
@Param: within
@Description: Used Internally
*/
$.parseSelectors = function(nodes, within){
	
	within = within || document;
	var root = [within];

	var found = [],s=0;
	
	//split at comma
	var selectors = nodes.selector.split(",");
	
	var len = selectors.length;
	var inheriters = [];
	
	for(s=0;s<len;s++){
		
		inheriters = selectors[s].split(" ");
		root = [within];
		
		selectors[s].split(" ").forEach(function(selector,k,a){
	
			if(selector.indexOf(">")+1){
				
				root = $.getElementsByParent(selector);
				
				if(k+1 == a.length){
					nodes.add(root);
					
				}
				
				return true;
				
			} else if(selector.indexOf('[')+1){
				
				///look for attribute's by searching for sqaure brackets //
				root = $.getElementsByAttributes(root, selector);
			
				if(k+1 == a.length){
					nodes.add(root);
				}
				
				return true;
			} else if(selector.indexOf("~")+1){
				
				root = $.getElementsBySiblingCombinator(root, selector);
				
				if(k+1 == a.length){
					nodes.add(root);
					
				}
				
				return true;
		
			} else if(selector.indexOf("+")+1){
				
				root = $.getElementsByAdjacentSibling(root, selector);
				
				if(k+1 == a.length){
					nodes.add(root);
					
				}
				
				return true;
		
			} else if(selector.indexOf(":")+1){
				//look for pseudo selectors
				root = $.parsePseudoSelectors(root, selector);
				
				if(k+1 == a.length){
					nodes.add(root);
				}
				
				return true;
				
			}  else if((selector.indexOf("#") === 0 && selector.match(/^\#\w+$/)) || selector.match(/\w+\#\w+/)) {
				
				var element = $.getElementById(selector);
			
				if(element){
					root = (element instanceof Array) ? element : [element];
					
					if(k+1 == a.length){
						nodes.add(root);
						
					}
				}
				
				return true;
				
			}  else if (selector.indexOf(".") !== false){
				
				var period_pos = selector.indexOf(".");
				
				var left_bracket_pos = selector.indexOf("[");
				var right_bracket_pos = selector.indexOf("]");
				
				if(period_pos+1 && !(period_pos > left_bracket_pos && period_pos < right_bracket_pos)) {
					
					root = $.getElementsByClassName(selector, root[0]);
					
					if(k+1 == a.length){
						nodes.add(root);
					}
					
					return true;
				}
			}
		
			//Tag selectors - no class or id specified.
			root = $.getElementsByTagName(root, selector);
			
			if(k+1 == a.length){
				nodes.add(root);
			}
			
			return true;
		});
	
	}
	
	return nodes;
};

/**
@Name: $.getElementById
@Description: Used Internally
*/
$.getElementById = function(selector){
	
	var parts = selector.split("#");
	var element = document.getElementById(parts[1]);
	return element;
};

/**
@Name: $.getElementsByClassName
@Param: string Selector The selector e.g. .myclass or div.myclass
@Param: element The root to search within e.g. document, div
@Description: Used Internally
*/
$.getElementsByClassName = function(selector, root){

	var parts = selector.split('.');
	
	var nodes = root.getElementsByTagName(parts[0] || '*');
	var elements = [],className = parts[1], node, cur_class_name,len = nodes.length,x=0;
	var rg = RegExp("\\b"+className+"\\b");
	
	do{
		node = nodes[x];
		cur_class_name = node.className;
		if (cur_class_name.length && (cur_class_name == className || rg.test(cur_class_name))){
			
			elements.push(node);
        }
		x++;
	} while(x<len);
	
	return elements;
};

/**
@Name: $.getElementsByTagName
@Description: Used Internally
*/
$.getElementsByTagName = function(root, tag) {
	root = (root instanceof Array) ? root : [root];

	var matches = [],len1 = root.length,len2,x=0,i=0,nodes,elements;

	for(x=0;x<len1;x++){
	
		nodes = root[x].getElementsByTagName(tag || '*');
		elements = [];
		len2 = nodes.length;
		
		for(i=0;i<len2;i++){
			elements.push(nodes[i]);
		}
		matches = matches.concat(elements);
	}
	
	return matches;
};

/**
@Name: $.getElementsByAttributes
@Description: Used Internally
*/
$.getElementsByAttributes = function(within, selector){
	var f = 1;
	var tag,attr,operator,value;
	
	if (selector.match(/^(?:(\w*|\*))\[(\w+)([=~\|\^\$\*]?)=?['"]?([^\]'"]*)['"]?\]$/)) {
		tag = RegExp.$1;
		attr = (typeof sb.nodeList.attrConvert == 'function') ? sb.nodeList.attrConvert(RegExp.$2) : RegExp.$2;
		
		operator = RegExp.$3;
		value = RegExp.$4 ||'';
	}
	
	var elements = $.getElementsByTagName(within, tag);
	
	within = elements.filter(function(el,k,a){
	
		el.attrVal = el.getAttribute(attr, 2);
		
		//if attribute is null
		if(!el.attrVal){
			return false;
		}
		
		switch(operator){
			case '=':
				if(el.attrVal != value){
					return false;
				}
				break;
				
			case '~':
			
				if(!el.attrVal.match(new RegExp('(^|\\s)'+value+'(\\s|$)'))){
					return false;
				}
				break;
				
			case '|':
			
				if(!el.attrVal.match(new RegExp(value+'-'))) {
					return false;
				}
				break;
				
			case '^':
				if(el.attrVal.indexOf(value) !== 0){
					return false;
				}
				break;
				
			case '$':
				if(el.attrVal.lastIndexOf(value)!=(el.attrVal.length-value.length)){
					return false;
				}
				break;
				
			case '*':
				if(!(el.attrVal.indexOf(value)+1)){
					return false;
				}
				break;
				
			default:
				if(!el.getAttribute(attr)){
					return false;
				}
		}
		
		return true;
		
	});
	
	return within;
	
};

/**
@Name: $.getNextSibling
@Description: Used Internally
*/
$.getNextSibling = function(node){
	while((node = node.nextSibling) && node.nodeType != 1){}
	return node;
};

/**
@Name: $.getPreviousSibling
@Description: Used Internally
*/
$.getPreviousSibling = function(node){
	while((node = node.previousSibling) && node.nodeType != 1){}
	return node;
};

/**
@Name: $.getFirstChild 
@Description: Used Internally
*/
$.getFirstChild = function(node){
	node = node.firstChild;
	while (node && node.nodeType && node.nodeType == 3) {
		node = $.getNextSibling(node);
	}
	return node;	
};

/**
@Name: $.getLastChild 
@Description: Used Internally
*/
$.getLastChild = function(node){
	
	node = node.lastChild;
	while (node && node.nodeType && node.nodeType == 3) {
		node = $.getPreviousSibling(node);
	}
	return node;
};

/**
@Name: $.getElementsByParent
@Description: Used Internally
*/
$.getElementsByParent = function(selector){
	var tags = selector.split(">");

	var elements = $.getElementsByTagName([document.body], tags[1]);
	
	var rg = new RegExp(tags[0], 'i');
	var nodes = [];
	var len = elements.length;
	
	for(var n=0;n<len;n++){
		if(rg.test(elements[n].parentNode.nodeName)){
			elements[n].sbid = sb.uniqueID();
			nodes.push(elements[n]);
		}
	}
	
	return nodes;	
	
};

/**
@Name: $.getElementsBySiblingCombinator
@Description: Used Internally
*/
$.getElementsBySiblingCombinator = function(within, selector){
	var parts = selector.split("~");
			
	var nodeName = parts[0],siblingNodeName = parts[1],elements = [],x=0,nn;

	var siblings = $.getElementsByTagName(within, nodeName);
	var len = siblings.length;
	
	for(x=0;x<len;x++){
		var node = siblings[x];
		while(node = node.nextSibling){
			nn = node.nodeName.toLowerCase();
			if(nn == nodeName){break;}
			if(node.nodeType == 1 && nn == siblingNodeName){
				node.sbid = sb.uniqueID();
				elements.push(node);
			}
			
		}
	}
	return elements;
	
};

/**
@Name: $.getElementsByAdjacentSibling
@Description: Used Internally
*/
$.getElementsByAdjacentSibling = function(within, selector){
	var parts = selector.split("+");
			
	var nodeName =parts[0];
	var adjacentNodeName = parts[1].toUpperCase();
	var elements = $.getElementsByTagName([document.body], nodeName);
	elements = (!elements.length) ? [elements] : elements;
	//put in the proper adajcent siblings
	var nodes = [], x=0,node,len = elements.length;
	for(x=0;x<len;x++){
		node = $.getNextSibling(elements[x]);
		if(node && node.nodeName == adjacentNodeName){
			nodes.push(node);
		}
	}
	
	return nodes;
			
};

/**
@Name: $.parsePseudoSelectors
@Description: Used Internally
*/
$.parsePseudoSelectors = function(within, selector){

	var nth,notSelector,elements = [],parts = selector.split(":");
	
	selector =parts[0];
	var pseudo = parts[1];
	
	var nodes = $.getElementsByTagName(within, selector);
	var parentNode;
	
	nodes.forEach(function(node,k,a){
		
		switch(pseudo){
			
			case 'before':
		
				var bf = new sb.element({
					nodeName : 'span',
					innerHTML : 'ddd'
				}).appendToTop(node);
				elements.push(bf);
			
				break;
				
			case 'first-child':
				
				if(!$.getPreviousSibling(node)){
					elements.push(node);
				}
				break;
				
			case 'last-child':
				if(!$.getNextSibling(node)){
					elements.push(node);
				}
				break;
			
			case 'empty':
				if(node.innerHTML ===''){
					elements.push(node);
				}
				break;
				
			case 'only-child':
			
				if(!$.getPreviousSibling(node) && !$.getNextSibling(node)){
					elements.push(node);
				}
					
				break;
				
			default: 
				
			if(pseudo.indexOf('not')+1){
				notSelector = pseudo.match(/not\((.*?)\)/);
				
				if(node.nodeName.toLowerCase() != notSelector[1]){
					elements.push(node);
				}
			}
		}
		
		 
	});

	return elements;
};

var sb = {
	$ : $,
	
	/**
	@Name: sb.base
	@Description: Used Internally to find required files
	*/
	base : (typeof window.sbBase !='undefined') ? window.sbBase : '/surebert',
	
	/**
	@Name: sb.colors
	@Description: Methods used to calculate and manipulate color values, see also /colors direcory
	*/
	colors : {},
	
	/**
	@Name: sb.consol
	@Description: Used Internally.  Used as placeholder for sb.developer functions
	*/
	consol : {
		log : function(){},
		write : function(){},
		error : function(){}
	},

	/**
	@Name: sb.createIfNotExists
	@Description:  Sets up global alias for a variable if one does not already exist.
	@Example:
	//checks to see if global jump function exists and creates it if it does not
	sb.createIfNotExists('jump', function(){alert('jump');});
	
	*/
	createIfNotExists : function(i, o){
		if(!window[i] && o!==null){
			window[i] = o;
		}
	},
	
	/**
	@Name: sb.css
	@Description: Used Internally.  Used as placeholder for sb.css functions
	*/
	css : {},
	
	/**
	@Name: sb.included
	@Description:  An array of all modules that are included, updated live and can be used for debugging and making compressed libraries before putting into production
	@Example:
	alert(sb.included);
	*/
	included : [],
	/**
	@Name: sb.include
	@Description:  Includes another surebert module.  Make sure you surebert files are in /surebert or that you have set sb.base before using this.
	@Example:
	sb.include('String.prototype.nl2br');
	*/
	include : function(module){
		
		var mods = module.split('.');
		var path ='', file, unit=sb,m;
		if(mods[0] == 'String' || mods[0] == 'Element' || mods[0] == 'Array'){
			unit = window;
		}
		
		for(m=0;m<mods.length;m++){
			
			if(m !==0 && m < mods.length && mods.length >1){
				path +='.';
			}
			path +=mods[m];
			
			try{
				
				unit = unit[mods[m]];
				
			} catch(e){}
		
			if(typeof unit == 'undefined'){
					
				this.included.push(path);
				file = path.replace(/\./g, "/");
				sb.load(sb.base+'/'+file+'.js');
			
			}
		}
	},
	
	/**
	@Name: sb.load
	@Description: Used to load external javascript from the same server synchronously and on demand.
	@Return: Returns 0 upon eval success or 1 if not
	@Example: 
	sb.load('/surebert/surebert.effects.js');
	
	if(sb.load('../js/myJavascript.js')){
	
		//run function from myJavascript.js

	}
	*/
	load : function(url){
		var evaled = 0;

		(function(){
			var load = new sb.ajax({
				url : url,
				async : 0,
				method : 'get',
				format : 'js',
				debug : sb.loadDebug ? 1 : 0,
				onResponse: function(r){
				//#######look into this 
					
					try{
						evaled=1;
					}catch(e){
							
						evaled=0;
						delete e.stack;
						
						sb.consol.error(sb.messages[13]+"\nURL: "+url+"\n"+sb.objects.dump(e));
						
					}
					load=null;
				}
			}).fetch();}());
			
		return evaled;
	},
	
	/**
	@Name: sb.math
	@Description: Used Internally. A placeholder for sb.math
	*/
	math : {},
	
	/**
	@Name: sb.messages
	@Description: a placeholder used internally for holding error messages which are defined in sb.developer.  This array just keeps errors from occuring when referencing messages if sb.developer is not included.
	*/

	messages : [],
	
	/**
	@Name: sb.onbodyload
	@Description: an array of functions that run once the DOM loads, they are fired in order, funcitons can be function references or inline anonymous functions
	@Example:
	sb.onbodyload.push({myFunction});
	*/
	onbodyload : [],
	
	/**
	@Name: sb.onleavepage
	@Description: an array of functions that run once when leaving the page, they are fired in order
	@Example:
	sb.onleavepage.push({myFunction});
	*/
	onleavepage : [],

	/**
	@Name: sb.toArray
	@Description: converts other types of iterable objects into an array e.g. an arguments list or an element sb.nodeList returned from getElementsByTagName.
	@Param: Object Iterable non-array
	@Return: Array A normal iteratable array with all the properties of an array and the values of the iterable object it was passed.
	@Example: 
	var images = document.getElementsByTagName('img');
	images = sb.toArray(images);
	images.forEach(function(image,key,arr){
		alert(image.src);
	});
	*/
	toArray : function(o){
		var a=[];
		var len=o.length;
		for(var x=0;x<len;x++){
			a.push(o[x]);
		}
		return a;
	},
	
	/**
	@Name: sb.typeOf
	@Description: returns the type of the object it is passed
	@Param: object o Any type of javascript object, string, array, function, number, etc
	@Return: String 'function', 'array', 'string', 'object', 'textnode', 'element', 'boolean', 'float', 'number', or returns value of object's custom typeOf() if it exists, 'null'
	@Example:
		var obj = {name : 'joe'}
		sb.typeOf(obj); //return 'object'
	*/
	typeOf : function(o){
		var type='';
		
		if(o === null){
			return 'null';
		} else if (o instanceof Function) { 
			type = 'function'; 
		} else if (o instanceof Array) {
			type = 'array';
		} else if(typeof o == 'number'){
			type = 'number';
			if(String(o).match(/\./)){
				type = 'float';
			}
		} else if(typeof o == 'string'){
			type = 'string';
		} else if(o === true || o === false){
			type='boolean';
		} else {
			type = (typeof o).toLowerCase();
		}
		
		if(typeof o =='object' ){
		
			if(typeof o.typeOf == 'function'){
				type = o.typeOf();
			} else if (o.nodeType){
				if (o.nodeType == 3) {
					type = 'textnode';
					
				} else if (o.nodeType == 1) {
					type = 'element';
				}
			} else if(typeof o.length !='undefined' && type !='array'){
				type = 'sb.nodeList';
			} 
		}
		
		return type;
	},
	
	/**
	@Name: sb.uid
	@Description: a placeholder used internally when creating unqiue IDs for DOM elements
	*/
	uid : 0,
	
	/**
	@Name: sb.uniqueID
	@Description: produces a unique id, ideal for DOM element which are created on the fly but require unique ids
	@Return: String a unique id string for a dom elements id string e.g. 'uid_5'
	@Example:
	var myUniqueId = sb.uniqueID();
	//myUniqueId = 'uid_5' //<--just an example return would be unique each time it is called on a page
	*/
	uniqueID : function(){
		return 'uid_'+(sb.uid +=1);
	},
	
	/**
	@Name: sb.unixTime
	@Description: calculates the current time as a unix timestamp
	@Return: Number A unix timestamp
	@Example:
	var unixtime = sb.unixTime();
	//unixtime = 1170091311//<- just a possible example - would return current time
	*/
	
	unixTime : function(){
		return parseInt(String(new Date().getTime()).substring(0,10), 10);
	},
	
	/**
	@Name: sb.functions
	@Description: Used Internally. A placeholder for sb.functions
	*/
	functions : {},
	
	/**
	@Name: sb.utils
	@Description: Used Internally. A placeholder for sb.utils
	*/
	utils : {},
	
	/**
	@Name: sb.widget
	@Description: Used Internally. A placeholder for sb.widgets
	*/
	widget : {},
	
	/**
	@Name: sb.forms
	@Description: Used Internally. A placeholder for sb.forms
	*/
	forms : {}
	
};

/**
@Name: sb.browser
@Description: Find out what browser we are using and gets the query string and screen data
*/
sb.browser ={
	
	/**
	@Name: sb.browser.ie6
	@Type: boolean
	@Description: Is teh page being displayed with ie 6. Normally you would access this information through sb.browser.agent and sb.browser.version but I added this for convenience with ie6
	@Example.
	if(sb.browser.ie6){
		//do something
	}
	*/
	ie6 : 0,
	
	/**
	@Name: sb.browser.getAgent
	@Description: Determines the agent, version, and os of the client. Used Internally.  If you specify sbOutDatedBrowser as a function it will fire if the browser is opera < 9, firefox < 1.5, iexplorer <6 or safari < 1.3
	*/
	getAgent : function(){
		
		var opera = new RegExp("opera/(\\d{1}.\\d{1})", "i");
		var safari = new RegExp("safari/(\\d{3})", "i");
		var firefox = new RegExp("firefox/(\\d{1}.\\d{1})", "i");
		var agent = window.navigator.userAgent;
		var str;
		
		if(window.opera && window.document.childNodes) {
			this.agent = 'op';
			str = agent.match(opera);
			this.version = str[1];
			
		} else if (document.all && !window.XMLHttpRequest && document.compatMode){
			this.agent = 'ie';
			this.version = 6;
			sb.browser.ie6 =1;
		}  else if (document.all && window.XMLHttpRequest && document.compatMode){
			this.agent = 'ie';
			this.version = 7;
	
		} else if(agent.match(firefox)){
			this.agent = 'ff';
			str = agent.match(firefox);
			this.version = str[1];
		} else if(agent.match(safari)){
			
			str = agent.match(safari);
			
			this.agent = 'sf';
			if(agent.match(/iphone/i)){
				this.agent += '_iphone';
			} else if(agent.match(/ipod/i)){
				this.agent += '_ipod';
			}
			
			if(str[1] < 400){
				this.version =1;
			} else if(str[1] < 500){
				this.version =2;
			} else if(str[1] < 600){
				this.version =3;
			}
			
		} else {
			this.agent='other';
		}
	
		return this.agent;
	},
	
	/**
	@Name: sb.browser.measure
	@Description: Measures the inside view area of the window
	@Return Array Returns the an array of width and height of the inside of the client's view area
	@Example:
	var pos = sb.browser.measure();
	//pos = [800, 642]
	*/
	measure : function(){
		sb.browser.w=0; sb.browser.h =0;
		if( typeof window.innerWidth == 'number' ) {
		    sb.browser.w = window.innerWidth;
		    sb.browser.h = window.innerHeight;
		} else if( window.document.documentElement && ( window.document.documentElement.clientWidth || window.document.documentElement.clientHeight ) ) {
		    sb.browser.w = document.documentElement.clientWidth;
		    sb.browser.h = document.documentElement.clientHeight;
		}
		
		return [sb.browser.w, sb.browser.h];
	},
	
	/**
	@Name: sb.browser.init
	@Description: Used Internally
	*/
	init : function(){
	
		this.getAgent();
		this.measure();
	}
};

sb.browser.init();

sb.objects = {
		
	/**
	@Name: sb.objects.serialize
	@Description: Serializes all the properties of an object into a post data style key value string
	@Param: Object o An object with properties
	@Return: String e.g. key=value&key=value

	*/
	serialize : function(o){
		var str, arr, a=[];
	
		sb.objects.forEach.call(o, function(value, prop, object){
			
			if(sb.typeOf(value) == 'array'){
				
				value.forEach(function(v, k){
					a.push(prop+'[]='+encodeURIComponent(v));
				});
				
			} else if(typeof value =='object'){
				
				sb.objects.forEach.call(value, function(v2, k2, o2){
			
					if(typeof v2 == 'object' || sb.typeOf(v2) == 'array'){
						
						str = sb.objects.serialize(v2);
						arr = str.split("&");
						str ='';
						arr.forEach(function(v3, k3, a3){
							arr[k3]= v3.replace(/(.*?)=(.*?)/g, prop+"['"+k2+"']['$1']=$2");
							
						});
				
						a.push(arr.join("&"));
						
					} else {
						a.push(prop+"['"+k2+"']="+encodeURIComponent(v2));
					}
				});
			} else {
				a.push(prop+'='+encodeURIComponent(value));
			}
		});
			
		return a.join("&");
	},
	
	/**
	@Name: sb.objects.infuse
	@Description: Used to add properties from one object to another.  If you have globals enabled you can just call infuse on any object or constructor and pass teh object to copy the properties from.
	@Example:
	var boy = {
		name : 'paul'
	};
	
	var otherBoy : {
		eats : function(){
			alert('yum');
		}
	}
	//copies eat function to boy object
	sb.objects.infuse(otherBoy, boy);
	//or with globals enabled
	boy.infuse(otherBoy);
	*/
	infuse : function(from, to){
		
		to = to || this;
		from = from || {};
		sb.objects.forEach.call(from, function(val,prop,o){
		
			try{ to[prop] = val;} catch(e){}
		});
		from = null;
		return to;
	},
	
	/**
	@Name: sb.objects.copy
	@Description: Makes a copy of an object and it's properties
	@Param: Object o the object to copy
	@Return: Object a copy of the object
	@Example:
		var o = {name : 'paul, language : 'javascript'};
		var f = sb.objects.copy(o);
	*/
	
	copy : function(o){
		var copy = {};
		
		sb.objects.forEach.call(o, function(val,prop,obj){
			copy[prop] = val;
		});
		
		return copy;
	},
	
	/**
	@Name: sb.objects.dump
	@Description: Returns the properties of the object and their values for an object
	@Param: Object o the object to return the properties of
	@Param: Number pre If this parameter is set to 1 than, the data is returned in a pre tag to maitain formatting
	@Return: String The properties of the object
	@Example:
		var o = {name : 'paul, language : 'javascript'};
		sb.objects.dump({o});
	*/
	dump : function(o, pre){
			var prop,str ='';
			sb.objects.forEach.call(o, function(v,p,o){
				try{
					str+="\n\n"+p+' = '+v;
				} catch(e){
					str += "\n"+p+' = CANNOT PROCESS VALUE!';
				}
			});
			
			if(!pre){ return str;} else { return '<pre style="margin:5px;border:1px;padding:5px;">'+str+'</pre>';}
	
	},
	
	forEach : function(func){
		for(var prop in this){
			if(this.hasOwnProperty(prop) && !sb.objects[prop] || prop =='infuse'){
				func(this[prop], prop, this);
			}
		}
	}
};

/**
@Name: sb.nodeList
@Description: Used to create sb.nodeLists which are groups of sb.elements that have many of the same methods as sb.element but which act on all sb.elements in the sb.nodeList. It also has all the properties of an sb.array. These are returned by sb.s$
*/
//sb.nodeList 
sb.nodeList = function(params){
	
	for(var prop in params){
		this[prop] = params[prop];
	}
	
	//initialize internal arrays
	this.nodes = [];
	this.sb_ids = {};
	
	var nls= this;
	['forEach', 'map', 'filter', 'every', 'some', 'indexOf', 'lastIndexOf', 'inArray'].forEach(function(v,k,a){
		nls[v] = function(func){
			return nls.nodes[v](func);
		};
	});
	
};

sb.nodeList.prototype = {
	
	/**
	@Name: sb.nodeList.prototype.selector
	@Description: The CSS selector used to find the nodes
	*/
	selector : '',
	
	/**
	@Name: sb.nodeList.prototype.assign_element_prototypes
	@Description: Re-assigns Element.prototypes of the nodes in the .nodes array to make sure that it picks up any Element.prototypes that have been added after the $ selection was made.  This is only required in IE since the other browsers all respect actual Element.protoype
	*/
	getElementPrototypes : function(){
	
		if(Element.emulated){
			var x,prop,ep = Element.prototype,len = this.nodes.length;
		
			for(x=0;x<len;x++){
				for(prop in ep){
					this.nodes[x][prop] = ep[prop];
				}
			}
			
		}
	},
	
	/**
	@Name: sb.nodeList.prototype.empty
	@Description: Empties the nodes array
	*/
	empty : function(){
		this.nodes = [];
		
	},
	
	/**
	@Name: sb.nodeList.prototype.setSelector
	@Param: string selector e.g. h1#wrapper 
	@Description: Used Internally. the CSS selector used to find populate the initial nodes array
	*/
	setSelector : function(selector){
		this.selector = sb.nodeList.cleanSelector(selector);
		
	},
	
	/**
	@Name: sb.nodeList.prototype.add
	@Param: An array of other nodes to add
	@Description: Adds more nodes to the nodeList nodes array and assigns sb_ids or adds super element properties if required
	@Example: 
	var nodes = $('ol li');
	//adds element with id 'wrapper' to the node list
	nodes.add($('#wrapper'));
	
	*/
	add : function(nodes){
		nodes = (nodes instanceof Array || (typeof NodeList !='undefined' && nodes instanceof NodeList)) ? nodes : [nodes];
		
		var len = nodes.length;
		
		var prop,x=0,node;
		
		var emulated = Element.emulated;
		
		var ep = Element.prototype;
		
		for(x=0;x<len;x++){
			node=nodes[x];
			
			if(!node.sb_id ){
				node.sb_id = sb.nodeList.sb_id++;
			}
			
			if(!this.sb_ids[node.sb_id]){
				
				if(emulated){
					
					for(prop in ep){
						node[prop] = ep[prop];
					}
				}
				this.nodes.push(node);
				this.sb_ids[node.sb_id] = true;
			}
		}
	},
	/**
	@Name: sb.nodeList.prototype.drop
	@Description: drop dom nodes, either array o single node from a sb.nodeList
	@Example: 
	var nodes = $('ol li');
	//adds element with id 'wrapper' to the node list
	nodes.drop('#wrapper');
	//add all the links to the sb.nodeList
	nodes.drop('a');
	*/
	drop : function(el){
		
		var t = this;
		el = $(el);
		
		this.nodes = t.nodes.filter(function(v){
			if(sb.typeOf(el) == 'sb.element'){
				return v != el;
			} else {
				return !el.nodes.some(function(v1){return v===v1;});
			}
			
		});
		this.length = this.nodes.length;
		
		return this;
	},
	
	/**
	@Name: sb.nodeList.prototype.firePerNode
	@Description: fires a function once for each node
	@Param: func function The function to fire on a per node basis.  The node is the this of the function.
	@Param: * any additional paramaters get passed to the func as arguments
	@Example: 
	var nodes = $('ol li');
	//hide all the nodes from view
	nodes.firePerNode(Element.prototype.hide);
	
	//inline function - alert the innerHTML of each
	nodes.firePerNode(function(){alert(this.innerHTML);});
	
	//assuming you has a function that changed background color, this would pass the argument 'blue' as the first argument to that function
	nodes.firePerNode(changeBackgroundColor, 'blue');
	*/
	firePerNode : function(func){
		
		if(typeof func == 'function'){
			var args = [];
			var len = arguments.length;
			for(var x=1;x<len;x++){
				args.push(arguments[x]);
			}
			this.nodes.forEach(function(node){
				func.apply(node, args);
			});
		}
		
		return this;
	},
	
	/**
	@Name: sb.nodeList.prototype.length()
	@Description: Return the length of the this.nodes array which represents how many nodes the nodeList instance is holding
	*/
	length : function(){
		return this.nodes.length;
	},
	
	/**
	@Name: sb.nodeList.prototype.styles(o)
	@Description: Runs the style method of each node in the nodeList and pass the o style object
	@Example:
	
	var nodeList = $('li,p');
	nodeList.styles({
		backgroundColor : 'red',
		color: 'yellow'
	});
	*/
	styles : function(styles){
		this.firePerNode(Element.prototype.styles, styles);
	},
	
	/**
	@Name: sb.nodeList.prototype.typeOf
	@Description: Used Internally for sb.typeOf
	*/
	typeOf : function(){
		
		return 'sb.nodeList';
	}
	
};

sb.nodeList.cleanSelector = function(selector){
	
	selector = selector.replace(/^\s+/, '');
	selector = selector.replace(/\s+$/, '');
		
	//remove excess space after commas
	selector = selector.replace(/, /g, ',');
	selector = selector.replace(/\s*([>~\+])\s*/g, "$1");
	return selector;
};

/**
@Name: sb.nodeList.sb_id
@Description: Used internally, to assign unique ID
*/
sb.nodeList.sb_id = 0;

/**
@Name: sb.nodeList.singleTags
@Description: Used internally
*/
sb.nodeList.singleTags = ['html', 'body', 'base', 'head', 'title'];


/**
@Name: sb.ajax
@Description: sb.ajax is a constructor that can be used to instanitate objects which communicate in real time from the client to the server without refreshing the page, all properties can be passed as the only object argument
@Return: Object A new ajax communication object
@Example:
var myAjax = new sb.ajax({
	//optional 'get' is the default
	method : 'post',
	
	//optional 'text' is the default
	format : 'text',
	
	//optional no data needs to be sent to the server side script
	data : 'name=paul&friend=tim&day=monday',
	
	// the server side script to call
	url : 'process.php',
	
	//the handler function receives all data returned from the server side script, depending on the format specified, result has different properties, by default it is a text string
	onResponse : function(result){ 
		//alerts the text returned from the server side script
		alert(result); 
	}
});
*/
sb.ajax = function(params){ 
	
	try{this.o=new window.XMLHttpRequest();}catch(e){
		try{this.o=new window.ActiveXObject("Microsoft.XMLHTTP");}catch(e3){
			throw('This browser does not support surebert');
		}
	}
	
	sb.objects.infuse(params, this);
	
	if(sb.typeOf(params.data) == 'object'){
		this.data = sb.objects.serialize(params.data);
	}
};

/**
@Name: sb.ajax.log
@Description: Used internally as a placeholder for sb.ajax.log found in sb.developer which is used to debug ajax transations
*/
sb.ajax.log = function(){};

/**
@Name: sb.ajax.defaultMethod
@Description: The default transport method used for communicating with server side scripts.  If this is changed, all insatnces with non specified transport methods will use this one.  It is 'get' by default.  Another option is 'post'.
*/
sb.ajax.defaultMethod = 'post';

/**
@Name: sb.ajax.defaultFormat
@Description: The default way the ajax instances handles the data retreived from the scripts. This sets the default format for all sb.ajax instances that do not already specify a format.  It is text by default but you can override this in your script.  The options are;
1. text - returns the data from the server side script as text and passes it to the instances onResponse method
2. json - returns the data from the server side script as a JSON object whose properties can easily be accessed with javascript
3. xml - returns the data from the server side script as an XML node which can be parsed with traditional XML parsing methods in javascript
4. js - evaluated the data returned from the server side script as javascript
5. send - only sends data and does not receive any data
6. head - only reads the header data from the HTML transaction and passes that to the instances onResponse method.  If a header property is specified on the sb.ajax instance, then only that header is passed
@Example:
sb.ajax.defaultFormat = 'json';
*/
sb.ajax.defaultFormat = 'text';

/**
@Name: sb.ajax.defaultURL
@Description: The default url the ajax instances semd data to. This sets the url for all sb.ajax instances that do not already specify a url.
@Example:
sb.ajax.defaultURL = 'process.php';
*/
sb.ajax.defaultURL = '';

sb.ajax.prototype = {
	
	/**
	@Name: sb.ajax.prototype.completed
	@Description: Is set to 0 when if the ajax call is not complete or set to 1 if it is compelete.  Used to check for complete state when using synchronous calls in safari.  Each instances completed status is reset on each fetch() call so that asynchronous calls can still be fetched more than once.
	@Type: Boolean
	*/
	completed : 0,
	
	/**
	@Name: sb.ajax.prototype.debug
	@Description: Determines if the data sent and received is debugged to the to surebert debug consol which.  This  only works if you include sb.developer.js  This makes debuggin much easier.
	@Type: Boolean
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		debug : 1
	});
	
	//or added afterwards with
	myAjax.debug =1;
	*/
	debug : this.debug || 0,
	
	/**
	@Name: sb.ajax.prototype.data
	@Description: The data sent to the server side script specified in the url property.  The values are passed as key value pairs e.g. x=1&y=2&name=joe.  You should always escape or URIencode data that includes anything other than alphanumeric data.
	@Type: String
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		data : 'name=paul&day=monday&value='+escape($('myInput').value)
	});
	
	//or added afterwards with
	myAjax.data = 'name=paul&day=monday&value='+escape($('myInput').value);
	*/
	data : this.data || '',
	
	/**
	@Name: sb.ajax.prototype.format
	@Description: The format the data is retreived in.   Can be json, text, xml, head, js, send - s.  This value overides any sb.ajax.defaultFormat value set or if the Content-type from server matches a specific format.
	1. text - returns the data from the server side script as text and passes it to the instance's handler method
	2. json - returns the data from the server side script as a JSON object whose properties can easily be accessed with javascript.  This type is defaulted if the page is served with the term 'json' in the content type e.g. application/json 
	3. xml - returns the data from the server side script as an XML node which can be parsed with traditional XML parsing methods in javascript  This type is defaulted if the page is served with the term 'xml' in the content type e.g. application/xml 
	4. js - evaluated the data returned from the server side script as javascript.  This type is defaulted if the page is served with the term 'javascript' in the content type e.g. application/javascript 
	5. send - only sends data and does not receive any data
	6. head - only reads the header data from the HTML transaction and passes that to the instance's onResponse method.  If a header property is specified on the sb.ajax instance, then only that header is passed
	@Type: Boolean
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		format : 'json'
	});
	
	//or added afterwards with
	myAjax.format = 'json';
	*/
	format : this.format || '',
	
	/**
	@Name: sb.ajax.prototype.async
	@Description: Determines if the script is paused while the data is loaded.
	@Type: Boolean false performs synchronous and pauses, true performs asynchronously which is the default allowing other processes to continue
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		async : 1
	});
	
	//or added afterwards with
	myAjax.async = 1;
	*/
	async : this.async,
	
	/**
	@Name: sb.ajax.prototype.local
	@Description: Allows ajax object instances to fetch data from a local file instead of from a server.  Normally, the instance checks for the HTTP server response - e.g. 200, 404, 500, etc and if you grab a klocal file this does not exist.  If you are serving your pages from a web server you should never need to use this.
	@Type: Boolean
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		local : 1
	});
	*/
	local : this.local || 0,
	
	/**
	@Name: sb.ajax.prototype.onreadystatechange
	@Description: Used Internally
	*/
	onreadystatechange : function() {
		var message = '';
		var js='';
		
		if (this.o.readyState != 4 || this.completed == 1) {return true; }
		
		//for backwards compatibility, remove soon
		if(typeof this.handler == 'function'){
			this.onResponse = this.handler;
		}
		
		this.completed =1;

		this.contentType = this.o.getResponseHeader("Content-Type");
		this.contentLength = this.o.getResponseHeader("Content-Length");
		
		if(this.contentLength > this.maxContentLength){
			
			//this.addToLog(7);
			if(typeof this.onContentLengthExceeded == 'function'){
				this.onContentLengthExceeded();
			}
			this.o.abort();
			return;
		}
		
		if(this.format === ''){
			if(this.contentType){
				if(this.contentType.match('application/json')){
					this.format = 'json';
				} else if (this.contentType.match('text/javascript')){
					this.format = 'javascript';
				} else if (this.contentType.match('text/xml')){
					this.format = 'xml';
				} else if(this.contentType.match('boolean/value')){
					this.format = 'boolean';
				}
			} else {
				this.format = sb.ajax.defaultFormat;
			}
		} 
		
		this.log(2, "\nHEADERS\nStatus: "+this.o.status+"\nStatus Text: "+this.o.statusText+"\n"+this.o.getAllResponseHeaders()+"\nRESPONSE: \n"+(this.o.responseText ||'PAGE WAS BLANK ;(')+"\n");
		
		var cont = true;
		
		if(typeof this.timer !='undefined'){
			window.clearInterval(this.timer);
		}
		
		cont = this.onHeaders.call(this.o, this.o.status, this.o.statusText);	
		
		if(cont === false || (this.o.status != 200 && this.local !==1)){
			return false;
		}
	
		switch(this.format){
			
			case 'head':
				if(typeof this.header ==='undefined'){
					this.response = this.o.getAllResponseHeaders();
				} else {
					this.response = this.o.getResponseHeader(this.header);
				
				}
				break;
			case 'xml':
			
				if(this.o.responseXML !== null){ 
					this.response = this.o.responseXML.documentElement;
				} else { 
					this.log(3);
				}
				break;
			
			case 'js':
				js =  this.o.responseText;
				break;
				
			case 'json':
				js = 'this.response='+this.o.responseText;
				break;
				
			case 'boolean':
				this.response = (this.o.responseText === 0) ? 0 : 1;
				break;
			
			default:
				this.response = this.o.responseText;
		}
	
		if(js !==''){
			try{
				 eval(js);
			}catch(e2){
				this.log(4);
			}
		}
		
		this.onResponse(this.response);
		
		if(typeof this.node !='undefined'){
			
			if(sb.$(this.node)){
				this.node = sb.$(this.node);
				if(typeof this.node.value !='undefined'){
					this.node.value = this.o.responseText;
				} else {
					this.node.innerHTML = this.o.responseText;
				}
			} else {
				this.addToLog(5);
			}
		}
		
		this.o.abort();
		return this; 
	},

	log : function(logId, message){
		if(this.debug ==1){
			
			var info = (message || '')+"\nSENT\nURL: ";
			if(this.method == 'get'){
				info += '<a href="'+this.url+'?'+this.data+'">'+this.url+'?'+this.data+'</a>';
			} else {
				info += this.url;
			}
			
			info += "\nMETHOD: "+this.method+"\nFORMAT: "+this.format+"\nASYNC: "+this.async+"\nDATA: "+this.data;
			
			sb.ajax.log(logId, info);
			if(typeof this.onLog == 'function'){
				
				this.onLog(logId, info);
			}
		}
	},
	
	/**
	@Name: sb.ajax.prototype.timeout
	@Description: The amount of time in milliseconds the ajax request will wait before it aborts.  This is optional
	@Example:
	var myAjax.timeout = 1000;
	
	//fetches the data from the url specified
	myAjax.fetch();
	*/
	timeout : 0,
	
	/**
	@Name: sb.ajax.prototype.fetch
	@Description: Sends any data specified to the external server side file specified in your instances .url property and returns the data recieved to the instance's onResponse method
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php'
	});
	
	//fetches the data from the url specified
	myAjax.fetch();
	*/
	fetch : function(url) {
		this.completed =0;
		
		this.method = (typeof this.method !='undefined') ? this.method : sb.ajax.defaultMethod; 

		var t=this;
		url = url || t.url || sb.ajax.defaultURL;
		t.url =url;
	
		if(!t.o){
			return false;
		}
		
		if(typeof t.async =='undefined'){
			t.async=true;
		}
		
		t.log(1);
		
		t.o.onreadystatechange = function(){t.onreadystatechange();};
	
		if(sb.typeOf(t.data) == 'object'){
			t.data = sb.objects.serialize(t.data);
		}
	
		if(t.method=='get' && t.data !== undefined){
			url = url+'?'+t.data;
		}
		
		if(t.timeout){
			
			t.count = 0;
			
			t.timer = window.setInterval(function(){
				if(t.count >= t.timeout){
					t.abort();
					t.count = 0;
					
					if(typeof t.onTimeout == 'function'){
						t.onTimeout();
					}
					
					window.clearInterval(t.timer);
				} else {
					t.count++;
				}
			}, 1);
		}
		
		if(!url){
			throw('A sb.ajax instance has no url set? But is trying to send the following data: '+t.data);
		}
		
		t.o.open(t.method, url, t.async);
	
		if(t.method=='post'){
			try{
				t.o.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			}catch(e){}
		}
		
		try{t.o.send(t.data); }catch(e1){}
		
		if (!t.async ){ t.onreadystatechange();}
		
	},
	
	/**
	@Name: sb.ajax.prototype.abort
	@Description: You can use this to abort an ajax function that is fetching.  In addition, if you have defined an onabort() method for your sb.ajax instance it will fire whenever the fetch is canceled.
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php'
	});
	myAjax.fetch();
	
	//aborts a fetch already in progress, you could attach this event to a cancel button
	myAjax.abort();
	*/
	abort : function(){
		this.o.abort();
		
		if(typeof this.onmillisec !='undefined'){
			this.timer.reset();
		}
		
		this.onAbort();
		
	},
	
	/**
	@Name: sb.ajax.prototype.onResponse
	@Description: Fires when the ajax request gets its response back from the server.
	@Param: response String, json, or XML depending on ajax instance .format property
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		onResponse : function(response){
			alert(response);
		}
	});
	*/
	onResponse : function(){},
	
	/**
	@Name: sb.ajax.prototype.onHeaders
	@Description: Fires when the ajax request gets it headers back
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		onHeaders : function(status, statusText){
			//alert 400 if file not found
			alert(status);
			//you also have access to other headers
			alert(this.getResponseHeader('Content-type');
		}
	});
	
	*/
	onTimeout : function(){},
	
	/**
	@Name: sb.ajax.prototype.onHeaders
	@Description: Fires when the ajax request gets it headers back
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		onHeaders : function(status, statusText){
			//alert 400 if file not found
			alert(status);
			//you also have access to other headers
			alert(this.getResponseHeader('Content-type');
		}
	});

	*/
	onHeaders : function(status, statusText){},
	
	/**
	@Name: sb.ajax.prototype.abort
	@Description: You can use this to abort an ajax function that is fetching.  In addition, if you have defined an onabort() method for your sb.ajax instance it will fire whenever the fetch is canceled.
	@Example:
	var myAjax = new sb.ajax({
		url : 'process.php',
		onAbort : function(){
			alert('ajax call aborted');
		}
	});
	
	//aborts a fetch already in progress, you could attach this event to a cancel button, also used by timeout
	myAjax.abort();
	*/
	onAbort : function(){}
};

sb.dom = {

	/**
	@Name: sb.dom.onReady
	@Description: Used to run a function when a DOM element becomes available
	@Param: object o An object of parameters
	o.id - A reference to the id of the DOM node you are questioning the availability of, e.g. #navigation is the the ID of the DOM node I am polling for.
	
	@Example:

	//In this example the onloaded function fires when the node with the id #navigation is available  the onloaded function, receives a this which is essentialy the element passed through sb.$
	sb.dom.onReady({
		id : '#navigation',
		onReady : function(){
			alert(this.innerHTML);
		},
		interval : 100,
		tries : 10,
		ontimeout function(el){
			alert(el+' not found');
		},
		args : ['one', 'two']
	});
	
	*/
	
	onReady : function(o){
		var found =0, timer, count=0;
		o.args = o.args || [];
		o.interval = o.interval || 10;
		
		o.tries = o.tries || 600;
		if(o.tries == -1){o.tries =99999999;}
		
		if(typeof o.onReady=='function'){
			
			timer = window.setInterval(function(){
				
				count +=1;
				
				if(count >= o.tries){
					window.clearTimeout(timer);
					
					if(typeof o.onTimeout=='function'){
						o.onTimeout(o.id);
					}
					return;
				}
				
				if(o.id == 'body' && document.body){
					window.clearTimeout(timer);
					found=1;
					o.id = document.body;
				} else if(o.id !='body' && sb.$(o.id)){
					
					window.clearTimeout(timer);
					found=1;
				}
				
				if(found == 1){
					o.onReady.apply(sb.$(o.id), o.args);
					
				}
				
			}, o.interval);
			
		} else {
			throw('sb.dom.onReady: You object argument must have a onReady property that runs when the dom element "'+o.id+'" is available');
		}
	}
	
};

/**
@Name: Array.prototype.inArray
@Description: Checks to see if a value is contained in the array
@Param: Object/String/Number val Method checks to see if val is in the array
@Return: Boolean True or False
@Example:
var myArray = [1,2,3];
var answer = myArray.inArray(2);
//answer is true
*/
Array.prototype.inArray = function(val){
	return this.some(function(v){return v===val;});
};

/**
@Name: Array.prototype.remove
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: Removes a value or a set of values from an array.
@Param: values Array If passed an array of values, all the values in the argument array are removed from the array being manipulated
@Param: value Object/String/Number If a single object, string, number, etc is passed to the function than only that value is removed.
@Return: Array Returns the array minus the values that were specified for removal.
@Example:
var myArray = [5, 10, 15];
var answer = myArray.remove([10,5]);
//answer =[15];

var answer = myArray.remove(5);
//answer =[10, 15];
*/
Array.prototype.remove = function(values){
	
	return this.filter(function(v){
		if(sb.typeOf(values) !='array'){
			return v != values;
		} else {
			return !values.inArray(v);
		}
	});
};

/**
@Name: String.prototype.hex2rgb
@Description: Used internally, converts hex to rgb
@Example:
var str = '#FF0000';
var newString = str.hex2rgb();
//newString = 'rgb(255,0,0)'
*/
String.prototype.hex2rgb = function(asArray){
	var hex = this.replace(/(^\s+|\s+$)/).replace("#", "");
	var rgb = parseInt(hex, 16); 
	var r   = (rgb >> 16) & 0xFF;
	var g = (rgb >> 8) & 0xFF; 
	var b  = rgb & 0xFF;
	
	if(asArray){
		return [r,g,b];
	} else {
		return 'rgb('+r+', '+g+', '+b+')';
	}
};
	
/**
@Name: String.prototype.toCamel
@Description: Converts all dashes to camelStyle
@Return: String The original string with dashes converted to camel - useful when switching between CSS and javascript style properties
@Example:
var str = 'background-color';

var newString = str.toCamel();
//newString = 'backgroundColor'
*/
String.prototype.toCamel = function(){
	return String(this).replace(/-\D/gi, function(m){
		return m.charAt(m.length - 1).toUpperCase();
	});
};
/**
@Name: sb.styles
@Description: Methods used to manipulate CSS and javascript styles
*/
sb.styles = {
	
	/**
	@Name: sb.styles.numRules
	@Description: used internally
	*/
	numRules : 1,
	
	/**
	@Name: sb.styles.newSheets
	@Description: Used Internally
	*/
	sheets : [],
	
	/**
	@Name: sb.styles.pxProps
	@Description: Used Internally. These properties get 'px' added to their value if no measurement is specified
	*/
	
	pxProps : ['fontSize', 'width', 'height', 'padding', 'border', 'margin', 'left', 'top']
	
};

/**
@Name: sb.events
@Description: Cross browser event handling that references the proper "this" and passes the event to the handler function.  Using sb.events, multiple events can be added to a single DOM node for the same event.  e.g. multiple onclick handlers
*/
sb.events = {
	
	/**
	@Name: sb.events.add
	@Description: Add an event listener to a DOM element, re-write of surebert events based on tips from http://www.digital-web.com/articles/seven_javascript_techniques/
	@Param: Element/String el A reference to a DOM element or a string that can be passed through sb.$ to return a dom el e.g. '#myList'
	@Param: String event The event to listen for without the on e.g. 'click'
		click - fires when the use mouses down and then up on an element
		contextmenu - fires when the user right-clicks on a DOM element - Not in opera
		mouseover - fires when the user hovers over a DOM element
		mouseout - fires when the user moves the mouse out from over a DOM element
		mousedown - fires when the user press the mouse button down over a DOM element
		mouseup - fires when the user lets the mouse button return to the up position over a DOM element
		keydown - fires when the user presses a key when in a DOM element
		keyup - fires when the user lets the key return to the up position in a DOM element
		keypress - fires when the key is pressed and then returns to the upstate in a DOM element
		blur - fires when a DOM element loses focus
		focus - fires when a DOM element gains focus
		submit - fires when a form is submitted
		onload - when a element such as body or img loads
		onunload - when user naviagtes away from the page
	@Param: Function handler The function that is run when the event occurs.  The this reference of the function is the element itself and the funciton is also passed an event object which holds data about the event e.g. clientX, clientY, target, etc  The funciton can be either an anonymous inline function or a function reference
	@Return Object Returns a reference to the event handler so that the event listener can be removed
	@Example:
	var myEvent = sb.events.add('#myList', 'click', function(e){
		alert(this.innerHTML);
	});
	*/
	add : function() {
		
	    if(window.addEventListener){
	   
	        return function(el, type, fn) {
	        	el = sb.$(el);
	        	var f = function(e) {
	        		
	        		var sb_target = e.target;
	        		var sb_related_target = e.relatedTarget;
	        		delete e.target;
	        		delete e.relatedTarget;
	        		e.__defineGetter__("target", function() { return sb.events.distillTarget(sb_target); });
	        		e.__defineGetter__("relatedTarget", function() { return sb.events.distillTarget(sb_related_target); });
	                fn.call(el, e);
	                delete sb_target;
	        		delete sb_related_target;
	            };
	        	var evt = {el:el, type:type, fn:f, remove : sb.events.removeThis};
	            el.addEventListener(type, f, true);
	            return sb.events.record(evt);
	        };
	    } else if ( window.attachEvent){
	        return function(el, type, fn) {
	        	el = sb.$(el);
	        	var tar = false;
				
	            var f = function() {
	            	var e = window.event;
	            	var tar = null;
		            switch(e.type){
						case 'mouseout':
							tar = e.relatedTarget || e.toElement;
							break;
						
						case 'mouseover':
							tar = e.relatedTarget || e.fromElement;
							break;
					}
					
					if(tar){
	            		e.relatedTarget = sb.events.distillTarget(tar);
	            	} 
	            	
	            	if(e.srcElement){
	            		e.target = sb.events.distillTarget(e.srcElement);;
	            	}
	            	
	            	e.preventDefault = function(){
	            		e.returnValue = false;
	            	};
	            	
	            	e.stopPropagation = function(){
	            		e.cancelBubble = true;
	            	};
	            	
	                fn.call(el, e);
	            };
	            var evt = {el:el, type:type, fn:f, remove : sb.events.removeThis};
	            el.attachEvent('on'+type, f);
	            return sb.events.record(evt);
	        };
	    }
	}(),
	
	/**
	@Name: sb.events.removeThis
	@Description: used internally
	*/
	removeThis : function(){
		sb.events.remove(this);
	},
	
	/**
	@Name: sb.events.log
	@Description: used internally to keep track of all events registered on the page
	*/
	log : [],
	
	
	record : function(evt){
		sb.events.log.push(evt);
		return evt;
	},

		
	/**
	@Name: sb.events.remove
	@Description: Removes an event listener
	@Param: Object event An event listener reference as returned from sb.events.add
	@Example:
	var myEvent = sb.events.add('#myList', 'click', function(e){
		alert(this.innerHTML);
	});
	
	sb.events.remove(myEvent);
	*/
	remove : function(evt){
	
		if (evt.el.removeEventListener){
			evt.el.removeEventListener( evt.type, evt.fn, false );
		} else if (evt.el.detachEvent){
			evt.el.detachEvent( "on"+evt.type, evt.fn );
		}
		
	},
	
	/**
	@Name: sb.events.removeAll
	@Description: Removes all event listeners added with sb.events.add or sb.elements or $'s event method

	@Example:
	sb.events.removeAll();
	*/
	removeAll: function(){
		sb.events.log.forEach(function(evt){
			sb.events.remove(evt);
		});
		sb.events.log=[];
	},
	
	/**
	@Name: sb.events.distillTarget
	@Description: Used internally
	
	 */
	distillTarget : function(tar){
		if (tar && tar.nodeType && (tar.nodeType== 3 || tar.nodeName == 'EMBED')){
			tar = tar.parentNode;
		}
	
	   return $(tar);
	}
	
};

/**
@Name: sb.element
@Description: Used to create DOM nodes.  If a string is passed to the fuction it simply return document.createElement(str);
@Param: Object o An object of properties which are used to contruct the DOM object,  all properites are appending as properties to the dom object.  sb.elements have many methods whcih are all listed in the Element.prototype object below
@Param: String o If passed a nodeName as a string it simply returns document.createElement(nodeName);
@Param: Object sb.element If passed an sb.element it uses that element as a template and clones it
@Return: Element A DOM element hat can be inserted into the DOM or further manipulated
@Example: 
var myDiv = new sb.element({
	tag : 'div',
	className : 'redStripe',
	innerHTML : 'I am a redstriped div',
	events : {
		click : function(){
			alert(this.innerHTML);
		},
		mouseover : function(){
			this.style.backgroundColor='red';
		}
	},
	styles : {
		backgroundColor : 'blue',
		fontSize : '18px'
	},
	addAttributes : function{
		friend : 'xxx'
	}
});

myDiv.appendTo('body');

//OR just pass the nodeType
var myDiv = new sb.element('div');

myDiv.appendChild(myOtherDiv);
*/
sb.element = function(o){
	var el,c;

	if(sb.typeOf(o) == 'sb.element'){
		return o;
	}
	
	if(typeof o == 'object' ){
		
		if(o.tag == 'input' && sb.dom.createNamedElement){
			
			el = new sb.dom.createNamedElement(o.type, o.name, o.checked);
			
		} else {
			el = document.createElement(o.tag);
		}
	}
	
	//copy properties from the sb.element prototype
	if(Element.emulated){
		sb.objects.infuse(Element.prototype, el);
		o = sb.objects.copy(o);
	}
	
	if(typeof o.addAttributes !='undefined'){
		el.styles(o.addAttributes);
		delete o.addAttributes;	
	}
	
	if(typeof o.styles !='undefined'){
		el.styles(o.styles);
		delete o.styles;	
	}
	
	if(typeof o.children !='undefined'){
		var len = o.children.length;
		for(c=0;c<len;c++){
			el.appendChild(new sb.element(o.children[c]));
		}
		delete o.children;
	}
	
	if(typeof o.events !='undefined'){
		sb.objects.forEach.call(o.events, function(func,event,obj){
			el.event(event, func);
		});
		
		delete o.events;
	}
	
	//copy additional props from o
	sb.objects.infuse(o, el);
	
	if(sb.browser.ie6){
		//remove attributes for ie's sake
		el.removeAttribute('tag');
	}
	
	return el;
};

/**
 * Create Element for IE and browsers that don't have it, notify that we are emulating so that we can copy properties as required
 */
if(typeof Element == 'undefined'){
		Element = function(){};
		Element.emulated = true;
		Element.prototype = {};
}

/**
@Name: sb.element.protoype
@Description: Methods of sb.element instances. Assume that myElement is an sb.element instance in all examples of Element.prototype
*/
Element.prototype.$ = function(selector){
	return $(selector, this);
};
	
/**
@Name: Element.prototype.addClassName
@Description: Adds a className to the sb.element, using this methods sb.element instances can have multiple classNames
@Param: String c The classname to add
@Return: returns itself
@Example:
myElement.addClassName('redStripe');
*/
Element.prototype.addClassName = function(className){
	this.className += ' '+className;
	
	return this;
};

/**
@Name: Element.prototype.append
@Description: Appends another DOM element to the element as a child
@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
@Example:
myElement.append(myOtherElement);
*/
Element.prototype.append = function(el){return this.appendChild(sb.$(el));};

/**
@Name: Element.prototype.appendTo
@Description: Appends the element to another DOM element as a child
@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
@Return: Element A refernce to the appended node
@Example:
//appends myElement to the page body
myElement.appendTo('body');

//appends myElement to a div with the ID "myDiv"
myElement.appendTo('#myDiv');

*/
Element.prototype.appendTo = function(el){
	return sb.$(el).appendChild(this);
};

	/**
@Name: Element.prototype.appendToTop
@Description: Appends the element to the top DOM element as a child
@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
@Return: Element A refernce to the appended node
@Example:
//appends myElement to the page body
myElement.appendToTop('body');

//appends myElement to a div with the ID "myDiv"
myElement.appendToTop('#myDiv');

*/
Element.prototype.appendToTop = function(el){
	el = sb.$(el);

	if(el.childNodes.length ===0){
		return this.appendTo(el);
	} else {
		return this.appendBefore(el.firstChild);
	}
};

/**
@Name: Element.prototype.appendAfter
@Description: Appends the element after another DOM element as a sibling
@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
@Example:
//appends myElement to the parent of "#myDiv" as a sibling of "#myDiv" directly after "#myDiv"
myElement.appendAfter('#myDiv');

*/
Element.prototype.appendAfter = function(after){
	var after = $(after);
	
	if(after.nextSibling){
		while((after = after.nextSibling) && after.nodeType != 1){}
		var nxtSib = after;
	}

	if(nxtSib){
		return nxtSib.parentNode.insertBefore(this, nxtSib);
	} else {
		return this.appendTo(after.parentNode);
	}
	
};

/**
@Name: Element.prototype.appendBefore
@Description: Appends the element before another DOM element as a sibling
@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
@Example:
//appends myElement to the parent of "#myDiv" as a sibling of "#myDiv" directly before "#myDiv"
myElement.appendBefore('#myDiv');

*/
Element.prototype.appendBefore = function(before){
	before = sb.$(before);
	return before.parentNode.insertBefore(this, before);
};

/**
@Name: Element.prototype.getX
@Description: Calculates the absolute x position of an element
@Return: Integer the x position of an element
@Example:
myElement.getX();
*/
Element.prototype.getX = function(){
	var x = 0, el=this;
	while(el !== null){
		x += el.offsetLeft;
		el = el.offsetParent;
	}
	return x;
};

/**
@Name: Element.prototype.getY
@Description: Calculates the absolute x position of an element
@Return: Integer the y position of an element

@Example:
myElement.getY();
*/
Element.prototype.getY = function(){
	var y = 0, el=this;
	while(el !== null){
		y += el.offsetTop;
		el = el.offsetParent;
	}
	return y;
};

/**
@Name: Element.prototype.hasClassName
@Description: Checks to see if the element has the className specified.  Elements can have more than one className.
@Return: Boolean True if the element contains the className and False if it doesn't
@Param: String c The className to check for
@Example:
myElement.hasClassName('redStripe');
*/
Element.prototype.hasClassName = function(classname){
	
	return this.className.match("\\b"+classname+"\\b");
};

/**
@Name: Element.prototype.remove
@Description: Removes an element from the DOM
@Return: returns itself
@Example:
myElement.remove();
*/
Element.prototype.remove = function(){
	try{
	if(typeof this.parentNode !='undefined'){
		this.parentNode.removeChild(this);
	}
	}catch(e){
		alert(this.innerHTML);
	}
	return this;
};

/**
@Name: Element.prototype.removeClassName
@Description: Removes a className from the elements className array.  Elements can have more than one className
@Param: String c Specified the className to remove from the element
@Return: returns itself
@Example:
myElement.removeClassName('redStripe');
*/
Element.prototype.removeClassName = function(className){
	this.className = this.className.replace(new RegExp("\b*"+className+"\b*"), "");
	return this;
};

/**
@Name: Element.prototype.replace
@Description: Replaces an element with another element in the DOM
@Param: Object/String A reference to another DOM node, either as a string which is passed to the sb.$ function or as an element reference
@Return: returns itself
@Example:
myElement.replace('#myOtherElement');
*/
Element.prototype.replace = function(node){
	node = sb.$(node);
	if(typeof node.parentNode !='undefined'){
		node.parentNode.replaceChild(this, node);
	}
	node = null;
	return this;
};

/**
@Name: Element.prototype.event
@Description: Used to set event cross-browser event handlers.  For more information see sb.events.
@Param: String evt The event to handle e.g. mouseover, mouseout, mousedown, mouseup, click, dblclick, focus, blurr, scroll, contextmenu, keydown, keyup, keypress
@Param: Function func The function to use as an event handler.  It is passed the e from the event in every brower as the first argument.  It also references "this" as the object the event is listening on.
@Return: The event that is added is returned so that you can use the reference to remove it with sb.events.remove or the sb.element instances sb.eventRemove
@Example:

//sets the backgroundColor peroperty to red
myElement.event('click', function(e){
	//alerts the x value of the click 
	alert(e.clientX);
	//alerts the innerHTML of myElement
	alert(this.innerHTML);
});

*/
Element.prototype.event = function (evt, func){
	
	var event = sb.events.add(this, evt, func);
	
	//this.eventsAdded.push(event);
	return event;
	
};

/**
@Name: Element.prototype.eventsAdded
@Description: Used keep track of events added to a sb.element.  All events added with this.event are pushed into this array where they are stored for removal

*/
Element.prototype.eventsAdded = [];

/**
@Name: Element.prototype.events
@Description: Used to assign multiple events at once
@Param: object events
@Example:
var myDiv = $('#myDiv');
myDiv.events({
	click : function(){
		do something
	},
	mouseover : function(){
		//do somthing
	}
});
*/
Element.prototype.events = function(events){
	for(var event in events){
		if(typeof events[event] =='function'){
			this.event(event, events[event]);
		}
	}
	
	return this;
};

/**
@Name: Element.prototype.eventRemove
@Description: Removes an event created with Element.prototype.event
@Param: String evt An event reference returned from the sb.element instances event method above.
@Example:
//sets the backgroundColor property to red
var myEvt = myElement.event('click', function(e){
	alert(this.innerHTML);
});

myElement.eventRemove(myEvt);
*/
Element.prototype.eventRemove = function (evt){
	sb.events.remove(evt);
	return this;
};

/**
@Name: Element.prototype.eventsRemoveAll
@Description: Removes all event observers for the sb.element that were added using this.event() or this.events()
@Example:
myElement.eventsRemoveAll();
*/
Element.prototype.eventsRemoveAll = function(){
	this.eventsAdded.forEach(function(evt){
		sb.events.remove(evt);
	});
	this.eventsAdded = [];
	return this;
};

/**
@Name: Element.prototype.styles
@Description: Sets multiple style properties for an sb.element
@Param: Object params An object with css style/value pairs that are applied to the object
@Return: returns itself
@Example:
myElement.styles({
	backgroundColor : '#000000',
	fontSize : '18px',
	border : '1px solid #FF0000'
});
*/
Element.prototype.styles = function(params){
	
	for(var prop in params){
		if(params.hasOwnProperty(prop)){
			try{
			this.setStyle(prop, params[prop]);
			}catch(e){}
		}
	}
	
	return this;
};

/**
@Name: Element.prototype.getStyle
@Description: calculates the style of an sb.element based on the current style read from css
@Param: String prop The property to look up
@Return: returns property value
@Example:
myElement.getStyle('background-color');
//or
myElement.getStyle('padding').
*/
Element.prototype.getStyle = function(prop){
	var val;
	
	if(prop.match(/^border$/)){
		prop = 'border-left-width';				
	} 
	
	if(prop.match(/^padding$/)){
		prop = 'padding-left';				
	}
	
	if(prop.match(/^margin$/)){
		prop = 'margin-left';		
	}
	
	if(prop.match(/^border-color$/)){
		prop = 'border-left-color';				
	}
	
	if (this.style[prop]) {
		val = this.style[prop];
		
	} else if (this.currentStyle) {
		
		prop = prop.toCamel();
		val = this.currentStyle[prop];
		
	} else if (document.defaultView && document.defaultView.getComputedStyle) {
			
		prop = prop.replace(/([A-Z])/g, "-$1");
		prop = prop.toLowerCase();
		
		val = document.defaultView.getComputedStyle(this,"").getPropertyValue(prop);
		
	} else {
		val=null;
	}
	
	if(prop == 'opacity' && val === undefined){
		val = 1;
	}
	
	if(val){
		if(typeof val == 'String'){
			
			val = val.toLowerCase();
			if(val == 'rgba(0, 0, 0, 0)'){val = 'transparent';}
		
			if(typeof sb.colors.html !='undefined'){
				if(sb.colors.html[val]){
					val = sb.colors.html[val].hex2rgb();
				}
			}
			
			if(val.match("^#")){
				val = val.hex2rgb();
			}
		
		}
		
		return val;
	} else {
		return null;
	}

};

/**
@Name: Element.prototype.prototype.setStyle
@Description: Sets the style of an sb.element
@Param: String prop The property to assign a value to
@Param: String val The value to assign to the property specified
@Return: returns property value
@Example:
myElement.setStyle('backgroundColor', blue);
//or
myElement.setStyle('opacity', 0.5);
*/
Element.prototype.setStyle = function(prop, val){
	
		if(sb.styles.pxProps.inArray(prop) && val !=='' && !val.match(/em|cm|pt|px|%/)){
			val +='px';
		}
		
		if(prop == 'opacity' && typeof this.style.filter == 'string' && typeof this.style.zoom == 'string'){
			this.style.opacity = val;
			this.style.zoom = 1;
			this.style.filter = "alpha(opacity:"+val*100+")";
			
			
		} else {
		
			if(prop == 'cssFloat' && typeof this.style.styleFloat == 'string'){
				prop = 'styleFloat';
			}
			
			if(typeof this.style[prop] == 'string'){
				this.style[prop] = val;
			} else {
				throw("style["+prop+"] does not exist in this browser's style implemenation");
			}
		}
};

Element.prototype.typeOf = function(){
	return 'sb.element';
};

if(!Array.prototype.forEach){
	sb.include('js1_5');
}

if(sb.browser.ie6){
	sb.include('ie6');
} else {
	/**
	@Name: sb.ie6
	@Description: Used Internally
	*/
	sb.ie6 = {
		pngFix: function(){},
		pngFixBg: function(el){}
	};
}

sb.dom.onReady({
	id : 'body',
	onReady : function(){
		sb.onbodyload.forEach(function(v){
			if(typeof v == 'function'){
				v();
			}
		});
	},
	tries : 600,
	ontimeout : function(){
		if(typeof sb.onbodynotready =='function'){
			sb.onbodynotready();
		}
	}
});

sb.events.add(window, 'resize', sb.browser.measure);
sb.events.add(window, 'unload', function(e){
	sb.onleavepage.forEach(function(v){
		if(typeof(v) =='function'){v(e);}
	});
	sb.events.removeAll();
});