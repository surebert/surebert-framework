(function(){
	if(window.sb){return;}
	var document = window.document;
	
	/**
	@Author: Paul Visco of http://paul.estrip.org
	@Version: 4.2 04/24/04 07/06/08
	@Package: surebert
	*/
	var sb = {
		
		/**
		@Name: sb.$
		@Description: One of the most important parts of the surebert library. Can reference DOM elements in many way using CSS selectors.  The simplest use of it is to reference DOM elements by their id property.
		@Param: String Use CSS selectors to return the elements desired
		@Example:
		e.g.'#myForm' An element id.  When passed an element ID it returns a reference to the element with that id'
		
		e.g.'body' An tag name.  When passed a tag name it returns an array of all the tags that match that tag name.  If ther tag is found in sb.singleTags e.g. body, head, title then only one element is returned instead of an array
		
		e.g. '#myDiv' returns node with the id 'myDiv'
		
		e.g. '.myClass' returns all nodes with the class 'myClass', see also [class="myClass"] below
		
		e.g. 'p' returns all the p nodes
		
		e.g. '*' returns all nodes
		
		e.g. '#myDiv p' returns all the p tags that are decendents of #myDiv
		
		e.g. '#myDiv > p' returns all the p tags that are direct decendents of #myDiv
		
		e.g. 'p + b' returns all the b tags that are direct adjacent siblings of p tags
		
		e.g 'p:first-child' returns all the p tags that are the first child of their parent, be careful its not the first child of each p tag
		
		e.g. 'p:last-child' returns all the p tags that are the last child of their parent
		
		e.g. 'p:empty' returns all the p tags that are empty
		
		e.g  'p:nth-child(4)' returns all the p tags that are the 4th child of their parent
			
		e.g  'p:nth-child(odd)' returns all the p nodes that are theare odd numbered child of their parent
		
		e.g  'p:nth-child(even)' returns all the p nodes that are theare even numbered child of their parent
		
		e.g  '*:not(p)' returns all nodes that are not p tags
		
		e.g  '#myDiv *:not(p)' returns all nodes that are not p tags within #myDiv
		
		e.g  'input[name"choosen"]' returns all the input nodes with the name 'choosen'
		
		e.g. 'a[href="http://www.surebert.com"] return all the a tags that have the href http://www.surebert.com
		
		e.g. 'a[href$="google.com"] return all the a tags that end in google.com
		
		e.g. 'a[href^="http"] return all the a tags that start with http
		
		e.g. 'a[href*="surebert"] return all the a tags that have the substring "surebert" in them
		
		e.g. a[hreflang|="en"]	returns all a tags whose "hreflang" attribute has a hyphen-separated list of values beginning (from the left) with "en"
		
		e.g. 'p[class~="bob"] returns an array of all p tags whose "class" attribute value is a list of space-separated values, one of which is exactly equal to "bob"
		
		e.g. 'p, b, #wrapper' Commas allow you to make multiple selections at once.This example returns all b nodes, all p nodes and node with the id 'wrapper'
		
		*/
		
		$ : function() {
			//return items that are already objects
			if((typeof arguments[0] == 'object' || typeof arguments[0] == 'function') && arguments.length ==1){return arguments[0];}
			
			//handle legacy dollarsign stuff
			if(arguments.length ==2 && typeof arguments[1] == 'string'){
				return sb.$.legacy.apply(this, arguments);
			}
			
			var selectors = sb.strings.trim.call(arguments[0]);
			var selector = selectors.split(",");
			var inheriters, nodes = [];
			var within = arguments[1];
			
			for(var s=0;s<selector.length;s++){
				selector[s] = selector[s].replace(/\s?([>\+\~])\s?/, "$1");
				inheriters = selector[s].split(" ");
				nodes = nodes.concat(sb.$.parseInheritors(inheriters, within));
			}
			
			//if there is only one match and there was only one requested or the request and match were for a single style HTML tag (head, body) then return only that match
			
			if(nodes.length ==1  && (!selectors.match(/,/) && selectors.match(/^\#\w+$/)) || sb.arrays.inArray.call(sb.dom.singleTags, selectors)){
				
				return nodes[0];
			} else if(selectors.match(/^\#\w+$/) && nodes.length === 0){
				return null;
			} else {
				return nodes;
			}
			
		},
	
		/**
		@Name: sb.addGlobals
		@Description: Used Internally.  Used as create a few useful globals if sbNoGlobals is not true.
		*/
		addGlobals : function(){
			var prop;
			
			for(prop in sb.strings){
				if(typeof String.prototype[prop] =='undefined'){
					String.prototype[prop] = sb.strings[prop];
				}
			}
		
			for(prop in sb.arrays){
				if(typeof Array.prototype[prop] =='undefined'){
					Array.prototype[prop] = sb.arrays[prop];
				}	
			}
			
			//Object.prototype.infuse = sb.objects.infuse;
			//Object.prototype.forEach = sb.objects.forEach;
			
			
			//add global links to internal sb functions
			sb.globals = {
				$ : sb.$,
				s$ : sb.s$
			};
			
			sb.objects.forEach.call(sb.globals, function(v,k,o){
				sb.createIfNotExists(k, v);
			});
	
		},
		
		/**
		@Name: sb.base
		@Description: Used Internally to find required files
		*/
		base : (typeof window.sbBase !='undefined') ? window.sbBase : '/surebert',
		
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
		sb.include('strings.nl2br');
		*/
		include : function(module){
			
			var mods = module.split('.');
			var path ='', file, unit=sb,m;
			
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
					handler: function(r){
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
		@Name: sb.s$
		@Description: Takes a normal DOM node or DOM node list selection from the sb.$ and applies the prototypes of sb.element to it.  If it returns an array, as base don the results from sb.$, then the resulting elements in the array all have the properties of an sb.element
		
		If it returns an array of elements the array, the arary has all the properties of an sb.array e.g. prev(), next(), current(), end(), rewind(), forEach(), map(), filter(), etc - see sb.array docs for details and all the properties of an sb.elementArray, css(), show(), hide(),  addClassName(), removeClassName() etc - see sb.elementArray docs for details.  
		@Param: el Anything you can pass to sb.$ - see $ docs for more details
		
		@Example:
		s$('ol > li'); //returns an array of all the list items in an ordered list, each of which has the properties of a sb.element
		
		s$('#wrapper'); //returns the element with the id "wrapper" and gives it all the properties of a sb.element
		
		//when an array is returned, all of the elementArray methods avaiable return the array back so you can chain methods together
		e.g. s$('ol li').getStyle('background-color', 'blue').addClassName('selected');
		
		*/
		
		s$ : function(el, fil){
			
			var retVal = sb.$.apply(this, sb.toArray(arguments));
			
			if(sb.typeOf(retVal) == 'array'){
			
				retVal = retVal.map(function(node){
					return sb.s$helper(node, el);
				});
			
				//REMOVEME
				
				retVal = new sb.nodeList(retVal);
				
				
			}  else {
				retVal = sb.s$helper(retVal, el); 
			}
			
			return retVal;
	
		},
		
		/**
		@Name: sb.toArray
		@Description: Used Internally. grabs $ value for s$
		*/
		s$helper : function(node, el){
			if(sb.typeOf(node) == 'element'){
				sb.objects.infuse(sb.element.prototype, node);
				
			} else if (sb.typeOf(node) != 'sb.element'){
				sb.consol.error('"'+el+'" of object type ' +sb.typeOf(node)+sb.messages[14]);
			}
			
			return node;
					
		},
		
		/**
		@Name: sb.toArray
		@Description: converts other types of iterable objects into an array e.g. an arguments list or an element Nodelist returned from getElementsByTagName.
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
			for(var x=0;x<o.length;x++){
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
					type = 'nodelist';
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
		@Name: sb.browser.getScrollPosition
		@Description: Gets the window scroll data It is automatically populated on window.onscroll.
		@Return Array Returns the an array of x and y scroll pos.
		@Example:
		var pos = sb.browser.getScrollPosition();
		//pos = [400, 300]
		*/
		getScrollPosition : function(){
			var x=0,y=0;
			if(window.pageYOffset){
				y = window.pageYOffset;
			} else if (document.documentElement && document.documentElement.scrollTop){
				y= document.documentElement.scrollTop;
			} 
			sb.browser.scrollY = y;
		
			if(window.pageXSOffset){
				x = window.pageXOffset;
			} else if (document.documentElement && document.documentElement.scrollLeft){
				x = document.documentElement.scrollLeft;
			} 
			
			sb.browser.scrollX = x;
			return [sb.browser.scrollX, sb.browser.scrollY];
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
		
	/**
	@Name: sb.$.getElementById
	@Description: Used Internally
	*/
	sb.$.getElementById = function(selector){
		
		var parts = selector.split("#");
		var tag = parts[0];
		var id = parts[1];
		var el = document.getElementById(id);
		
		return el;
		
	};
	
	/**
	@Name: sb.$.getElementByClassName
	@Description: Used Internally
	*/
	sb.$.getElementByClassName = function(within, selector){
	
		var parts = selector.split('.');
		var tag = parts[0];
		var class_name = parts[1];
		
		var elements = sb.$.getElementsByTagName(within, tag);
		
		within = elements.filter(function(node,k,a){
			
			//var classMatch = node.className && node.className.match(new RegExp("\s?("+class_name+")\s?"));
			var classMatch = node.className && node.className.match(new RegExp("\\b("+class_name+")\\b"));
			if(classMatch && classMatch[0] == class_name){
				
				return true;
			}
			
		});
		
		return within;
		
	};
	
	/**
	@Name: sb.$.getElementsByTagName
	@Description: Used Internally
	*/
	sb.$.getElementsByTagName = function(within, tag) {
		
		tag = tag || '*';
		
		var matches = [];
		
		within.forEach(function(node,k,a){
			
			var elements = sb.toArray(node.getElementsByTagName(tag));
			matches = matches.concat(elements);
		});
	
		
		return matches;
	};
	
	/**
	@Name: sb.$.getElementsByAttributes
	@Description: Used Internally
	*/
	sb.$.getElementsByAttributes = function(within, selector){
		
		var tag,attr,operator,value;
		if (selector.match(/^(?:(\w*|\*))\[(\w+)([=~\|\^\$\*]?)=?['"]?([^\]'"]*)['"]?\]$/)) {
			tag = RegExp.$1;
			attr = (typeof sb.$.attrConvert =='function') ? sb.$.attrConvert(RegExp.$2) : RegExp.$2;
			
			operator = RegExp.$3;
			value = RegExp.$4 ||'';
		}
		
		var elements = sb.$.getElementsByTagName(within, tag);
		
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
				if(!el.attrVal.match(new RegExp('^'+value+'-?'))) {
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
	@Name: sb.$.getNextSibling
	@Description: Used Internally
	*/
	sb.$.getNextSibling = function(node){
		while((node = node.nextSibling) && node.nodeType != 1){}
		return node;
	};
	
	/**
	@Name: sb.$.getPreviousSibling
	@Description: Used Internally
	*/
	sb.$.getPreviousSibling = function(node){
		while((node = node.previousSibling) && node.nodeType != 1){}
		return node;
	};
	
	/**
	@Name: sb.$.getFirstChild 
	@Description: Used Internally
	*/
	sb.$.getFirstChild = function(node){
		node = node.firstChild;
		while (sb.typeOf(node) == 'textnode') {
			node = sb.$.getNextSibling(node);
		}
		return node;	
	};
	
	/**
	@Name: sb.$.getLastChild 
	@Description: Used Internally
	*/
	sb.$.getLastChild = function(node){
		
		node = node.lastChild;
		while (sb.typeOf(node) == 'textnode') {
			node = sb.$.getPreviousSibling(node);
		}
		return node;
	};
	
	/**
	@Name: sb.$.getElementsByParent
	@Description: Used Internally
	*/
	sb.$.getElementsByParent = function(within, selector){
		var parts = selector.split(">");
				
		var par =parts[0];
		var chld = parts[1];
		
		var elements = sb.$(chld);
		elements = (!elements.length) ? [elements] : elements;
		
		elements = elements.filter(function(el,k,a){
		
			if(!par.indexOf('#')+1 && el.parentNode.nodeName.toLowerCase() == par){
					
				return true;
			} else if(par.indexOf('#')+1 && par.replace(/\#/, '') == el.parentNode.id){
				return true;
			}
			
			return false;
		});
		
		return elements;
	};
	
	/**
	@Name: sb.$.getElementsByAdjacentSibling
	@Description: Used Internally
	*/
	sb.$.getElementsByAdjacentSibling = function(within, selector){
		var parts = selector.split("+");
				
		var nodeName =parts[0];
		var adjacentNodeName = parts[1];
		
		var elements = sb.$(nodeName);
		elements = (!elements.length) ? [elements] : elements;
		//put in the proper adajcent siblings
		elements = elements.map(function(el,k,a){
			
			var node = sb.$.getNextSibling(el);
			if(node && node.nodeName.toLowerCase() == adjacentNodeName){
				return node;
			} 
			
			return false;
			
		});
		
		//remove any false eones
		elements = elements.filter(function(v){
			if(!v){
				return false;
			} else {
				return true;
			}
		});
		return elements;
				
	};
	
	/**
	@Name: sb.$.parsePseudoSelectors
	@Description: Used Internally
	*/
	sb.$.parsePseudoSelectors = function(within, selector){
	
		var nth,notSelector,elements = [],parts = selector.split(":");
		
		selector =parts[0];
		var pseudo = parts[1];
		
		var nodes = sb.$(selector, within);
		
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
					
					if(node.parentNode && node == sb.$.getFirstChild(node.parentNode)){
						elements.push(node);
					}
					break;
					
				case 'last-child':
					if(node.parentNode && node == sb.$.getLastChild(node.parentNode)){
						elements.push(node);
					}
					break;
				
				case 'empty':
					if(node.innerHTML ===''){
						elements.push(node);
					}
					break;
					
				case 'only-child':
					
					if(node.parentNode.childNodes.length ==1){
						elements.push(node);
					}
					break;
					
				default: 
					
				if(pseudo.indexOf('not')+1){
					notSelector = pseudo.match(/not\((.*?)\)/);
					
					if(node.nodeName.toLowerCase() != notSelector[1]){
						elements.push(node);
					}
				} else if(pseudo.indexOf('nth-child')+1){
					nth = pseudo.match(/nth\-child\((.*?)\)/);
					if(sb.strings.isNumeric.call(nth[1])){
						nth = parseInt(nth[1],10)-1;
						
						if(nth == k){
							elements.push(node);
						}
					} else {
						switch(nth[1]){
							case 'odd':
								if(k %2 !==0){
									elements.push(node);
								}
								break;
								
							case 'even':
								if(k %2 ===0){
									elements.push(node);
								}
								break;
						}
					}
						
					
				}
				
			}
			
			 
		});
	
		return elements;
	};
	
	/**
	@Name: sb.$.parseInheritors
	@Description: Used Internally
	*/
	sb.$.parseInheritors = function(inheriters, within){
		
		var matches = [];
		
		//within = (typeof within !='undefined') ? within : [document];
		within = within || [document];
		
		inheriters.within = within;
		inheriters.forEach(function(selector,k,a){
		
			var element;
			
				///we have just an id///
				if((selector.indexOf("#") === 0 && selector.match(/^\#\w+$/)) || selector.match(/\w+\#\w+/)) {
					
					element = sb.$.getElementById(selector);
				
					if(element){
						inheriters.within = [element];
						if(k+1 == a.length){
							matches = matches.concat(inheriters.within);
						}
					}
					
					return true; 
				}
			
			if(selector.indexOf(">")+1){
				inheriters.within = sb.$.getElementsByParent(inheriters.within, selector);
				
				if(k+1 == a.length){
					matches = matches.concat(inheriters.within);
					
				}
				
				return true;
				
			}
			
			if(selector.indexOf("+")+1){
				
				inheriters.within = sb.$.getElementsByAdjacentSibling(inheriters.within, selector);
				
				if(k+1 == a.length){
					matches = matches.concat(inheriters.within);
					
				}
				
				return true;
		
			}
			
			///look for attribute's by searching for sqaure brackets //
			if(selector.indexOf('[')+1){
				
				inheriters.within = sb.$.getElementsByAttributes(inheriters.within, selector);
			
				if(k+1 == a.length){
					matches = matches.concat(inheriters.within);
				}
				
				return true;
			}
			
			//look for pseudo selectors
			if(selector.indexOf(":")+1){
				
				inheriters.within = sb.$.parsePseudoSelectors(inheriters.within, selector);
				
				if(k+1 == a.length){
					matches = matches.concat(inheriters.within);
				}
				
				return true;
				
			}
			
			
			///look for classNames///
			var period_pos = selector.indexOf(".");
			//check for position of css attribute selectors to make sure period isn't in them
			var left_bracket_pos = selector.indexOf("[");
			var right_bracket_pos = selector.indexOf("]");
			
			if(period_pos+1 && !(period_pos > left_bracket_pos && period_pos < right_bracket_pos)) {
				
				inheriters.within = sb.$.getElementByClassName(inheriters.within, selector);
				if(k+1 == a.length){
					matches = matches.concat(inheriters.within);
				}
				
				return true;
				
			}
			
			if(selector.match(/\w+\#\w+/)){
			
				inheriters.within = [sb.$.getElementById(selector)];
			
				if(k+1 == a.length){
					matches = matches.concat(inheriters.within);
				}
				
				return true;
			}
			
			//Tag selectors - no class or id specified.
			inheriters.within = sb.$.getElementsByTagName(inheriters.within, selector);
		
			if(k+1 == a.length){
				matches = matches.concat(inheriters.within);
			}
			return true;
				
		});
		
		return matches;
	};
	
	/**
	@Name: sb.$.legacy
	@Description: Used Internally Converts old sb.$ calls into new CSS selector compliant ones for backwards compatibility with old surebert code
	*/
	sb.$.legacy = function(){
		
		var hasAttr=0, obj = arguments[0], filt =  arguments[1];
			
		if(filt.indexOf('@')+1){
		
			filt = filt.replace(new RegExp("=(.*?)$","flags"), "=\"$1\"");
			
			filt = '['+filt.replace('@', '')+']';
			hasAttr=1;
		}
			
		//if first arg is an object
		if(obj.getElementsByTagName){
			
			//convert old sb @ to new attribute selector style - get rid of this soon
			
			return sb.$(filt, [obj]);
			
		//if both arguments are strings join and send to sb.$
		} else if (typeof obj =='string' && typeof filt =='string') {
			
			if(hasAttr){
			//	alert(obj+filt);
				return sb.$(obj+filt);
			} else { 
				return sb.$(sb.toArray(arguments).join(' '));
			}
		}
		
	};
	
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
	@Name: sb.infuse
	@Description:  Used to infuse an object into sb
	@Example:
	sb.infuse({friend : {name : 'ted'}}};
	//could then use sb.friend
	*/
	sb.infuse = sb.objects.infuse;
	
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
		handler : function(result){ 
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
	@Name: sb.ajax.infuse
	@Description: Used internally to compensate for globals not being on by default
	*/
	sb.ajax.infuse = sb.objects.infuse;
	
	sb.ajax.infuse({
		/**
		@Name: sb.ajax.log
		@Description: Used internally as a placeholder for sb.ajax.log found in sb.developer which is used to debug ajax transations
		*/
		log : function(){},
		
		/**
		@Name: sb.ajax.defaultMethod
		@Description: The default transport method used for communicating with server side scripts.  If this is changed, all insatnces with non specified transport methods will use this one.  It is 'get' by default.  Another option is 'post'.
		*/
		defaultMethod : 'post',
		
		/**
		@Name: sb.ajax.defaultFormat
		@Description: The default way the ajax instances handles the data retreived from the scripts. This sets the default format for all sb.ajax instances that do not already specify a format.  It is text by default but you can override this in your script.  The options are;
		1. text - returns the data from the server side script as text and passes it to the instances handler method
		2. json - returns the data from the server side script as a JSON object whose properties can easily be accessed with javascript
		3. xml - returns the data from the server side script as an XML node which can be parsed with traditional XML parsing methods in javascript
		4. js - evaluated the data returned from the server side script as javascript
		5. send - only sends data and does not receive any data
		6. head - only reads the header data from the HTML transaction and passes that to the instances handler method.  If a header property is specified on the sb.ajax instance, then only that header is passed
		@Example:
		sb.ajax.defaultFormat = 'json';
		*/
		defaultFormat : 'text',
		
		/**
		@Name: sb.ajax.defaultURL
		@Description: The default url the ajax instances semd data to. This sets the url for all sb.ajax instances that do not already specify a url.
		@Example:
		sb.ajax.defaultURL = 'process.php';
		*/
		defaultURL : ''
		
	});
	
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
		@Description: The format the data is retreived in.  Can be json, text, xml, head, js, send - s.  This value overides any sb.ajax.defaultFormat value set or if the content type of the server page matches a specific format.
		1. text - returns the data from the server side script as text and passes it to the instances handler method
		2. json - returns the data from the server side script as a JSON object whose properties can easily be accessed with javascript.  This type is defaulted if the page is served with the term 'json' in the content type e.g. application/json 
		3. xml - returns the data from the server side script as an XML node which can be parsed with traditional XML parsing methods in javascript  This type is defaulted if the page is served with the term 'xml' in the content type e.g. application/xml 
		4. js - evaluated the data returned from the server side script as javascript.  This type is defaulted if the page is served with the term 'javascript' in the content type e.g. application/javascript 
		5. send - only sends data and does not receive any data
		6. head - only reads the header data from the HTML transaction and passes that to the instances handler method.  If a header property is specified on the sb.ajax instance, then only that header is passed
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
					if(this.contentType.match('json')){
						this.format = 'json';
					} else if (this.contentType.match('javascript')){
						this.format = 'javascript';
					} else if (this.contentType.match('xml')){
						this.format = 'xml';
					} else if(this.contentType.match('boolean')){
						this.format = 'boolean';
					}
				} else {
					this.format = sb.ajax.defaultFormat;
				}
			} 
			
			this.log(2, "\nHEADERS\nStatus: "+this.o.status+"\nStatus Text: "+this.o.statusText+"\n"+this.o.getAllResponseHeaders()+"\nRESPONSE: \n"+(sb.strings.escapeHTML.call(this.o.responseText) ||'PAGE WAS BLANK ;(')+"\n");
			
			//page status other than 200
			if(this.o.status != 200 && this.local !==1){
				return false;
			}
		
			
			if(typeof this.timer !='undefined'){
				window.clearInterval(this.timer);
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
			
			if(typeof this.handler =='function'){this.handler(this.response);}
			
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
		@Description: Sends any data specified to the external server side file specified in your instances .url property and returns the data recieved to the instances handler method
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
							t.timeout();
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
			
			if(typeof this.onabort =='function'){
				this.onabort();
			}
			
		},
		
		/**
		@Name: sb.ajax.prototype.infuse
		@Description: You can easily infuse sb.ajax objects with multiple properties
		@Example:
		var myAjax = new sb.ajax({
			url : 'process.php'
		});
		
		myAjax.infuse({
			format :'text',
			handler : function(result){
				alert(result);
			}
		});
		*/
		infuse : sb.objects.infuse
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
					
					if(found ==1){
						o.onReady.apply(sb.$(o.id), o.args);
						
					}
					
				}, o.interval);
				
			} else {
				throw('sb.dom.onReady: You object argument must have a onReady property that runs when the dom element "'+o.id+'" is available');
			}
		},
		
		/**
		@Name: sb.dom.singleTags
		@Description: Used internally
		*/
		singleTags : ['html', 'body', 'base', 'head', 'title']
		
	};
	
	/**
	@Name: sb.arrays
	@Description: These are used as native array prototypes if globals are not turned off.  Even when globals are turned off methods every, filter, forEach, indexOf, lastIndexOf, map, reduce, and reduceRight all are global if not already defined by you browser as part of javascript 1.6-1.8.  This allows you to use these javascript array method in any browser.
	*/
		
	sb.arrays = {
		
		/**
		@Name: sb.arrays.inArray
		@Description: Checks to see if a value is contained in the array
		@Param: Object/String/Number val Method checks to see if val is in the array
		@Return: Boolean True or False
		@Example:
		//with globals on
		var myArray = [1,2,3];
		var answer = myArray.inArray(2);
		//answer is true
		
		//without globals on
		sb.arrays.inArray.call(myArray, 2);
		*/
		inArray : function(val){
			return this.some(function(v){return v===val;});
		},
		/**
		@Name: sb.arrays.remove
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
		
		//without globals on
		sb.arrays.remove.call(myArray, [10, 15]);
		*/
		remove : function(values){
			
			return this.filter(function(v){
				if(sb.typeOf(values) !='array'){
					return v != values;
				} else {
					return !sb.arrays.inArray.call(values, v);
				}
			});
		}
	};
	
	/**
	@Name: sb.colors
	@Description: Methods used to calculate and manipulate color values, see also /colors direcory
	*/
	sb.colors =  {
		
		/**
		@Name: sb.colors.dec2hex
		@Description: coverts decimal values to hex
		@Example: 
		var hex = sb.colors.dec2hex(255);
		*/
		dec2hex : function(dec){
			return(this.hexDigit[dec>>4]+this.hexDigit[dec&15]);
		},
		
		hexDigit : ["0","1","2","3","4","5","6","7","8","9","A","B","C","D","E","F"],
		
		/**
		@Name: sb.colors.hex2dec
		@Description: coverts hex values to decimal
		@Example: 
		var hex = sb.colors.hex2dec('AC');
		*/
		hex2dec : function(hex){
			return parseInt(hex,16);
		}
	};
	
	/**
	@Name: sb.strings
	@Description: String manipulation methods - these are prototyped to the native Strings when globals are not disabled
	*/
	sb.strings = {
		
		/**
		@Name: sb.strings.escapeHTML
		@Author: Paul Visco
		@Version: 1.0 11/19/07
		@Description: Checks to see if a string is empty or not
		@Return: Boolean Returns true if the string is empty, false otherwise
		@Example:
		var str = '<p>hello</p>';
		var newString = str.escapeHTML();
		//newString = '&lt;p&gt;hello&lt;/p&gt;'
		
		//without globals
		sb.strings.escapeHTML.call(str);
		*/
		escapeHTML : function(){
			var str = this.replace(/</g, '&lt;');
			return str.replace(/>/g, '&gt;');
		},
		/**
		@Name: sb.strings.isNumeric
		@Author: Paul Visco
		@Version: 1.1 11/27/07
		@Description: Checks to see if a string is numeric (a float or number)
		@Return: Boolean True if the the string represnts numeric data, false otherwise
		@Example:
		var str = '12';
		
		var answer = str.isNumeric();
		//answer = true
		
		//without globals
		sb.strings.isNumeric.call(str);
		*/
		isNumeric : function(){
			return /^\d+?(\.\d+)?$/.test(this);
		},
		
		/**
		@Name: sb.strings.hex2rgb
		@Description: Used internally, converts hex to rgb
		*/
		hex2rgb : function(asArray){
			var hex = sb.strings.trim.call(this).replace("#", "");
			var rgb = parseInt(hex, 16); 
			var r   = (rgb >> 16) & 0xFF;
			var g = (rgb >> 8) & 0xFF; 
			var b  = rgb & 0xFF;
			
			if(asArray){
				return [r,g,b];
			} else {
				return 'rgb('+r+', '+g+', '+b+')';
			}
		},
		
		/**
		@Name: sb.strings.toCamel
		@Description: Converts all dashes to camelStyle
		@Return: String The original string with dashes converted to camel - useful when switching between CSS and javascript style properties
		@Example:
		var str = 'background-color';
		
		var newString = str.toCamel();
		//newString = 'backgroundColor'
	
		//without globals
		sb.strings.toCamel.call(str);
		*/
		toCamel : function(){
			return String(this).replace(/-\D/gi, function(m){
				return m.charAt(m.length - 1).toUpperCase();
			});
		},
		
		/**
		@Name: sb.strings.toElement
		@Description: Converts a string of HTML code to a sb.element for dom manipulation
		@Param: String parentNodeType The nodetype of the parent element returned if there is not already a single parent element for all elements contained in the html string - see example two - defaults to span if it is not given
		@Example:
		//would return div as the element with all its children
		sb.dom.HTMLToElement('<div id="joe"><p class="test">hey there</p></div>');
		//would return all elements grouped under a span because they have no comment parent
		sb.dom.HTMLToElement('<p class="test">hey there</p><p class="test2">hey there2</p>');
		*/
		toElement : function(parentNodeType){
			parentNodeType = parentNodeType || 'span';
			
			var temp = new sb.element({
				nodeName : parentNodeType,
				innerHTML : this
			});
			
			if(temp.childNodes.length > 1){
				return sb.s$(temp);
			} else {
				return sb.s$(temp.firstChild);	
			}
			
		},
	
		/**
		@Name: sb.strings.trim
		@Description: trims whitespace from both left and right side of a string
		@Return: String The original string with whitespace removed from left and right side
		@Example:
		var str = '    hello world       ';
		
		var newString = str.trim();
		//newString = 'hello world'
	
		//without globals
		sb.strings.trim.call(str);
		*/
		trim : function() {
			var str = this.replace(/^\s+/, '');
			return str.replace(/\s+$/, '');
		}
		
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
		        	var evt = {el:el, type:type, fn:fn};
		            el.addEventListener(type, fn, false);
		            return sb.events.record(evt);
		        };
		    } else if ( window.attachEvent){
		        return function(el, type, fn) {
		        	el = sb.$(el);
		        	
		            var f = function() {
		                fn.call(el, window.event);
		            };
		            var evt = {el:el, type:type, fn:f};
		            el.attachEvent('on'+type, f);
		             return sb.events.record(evt);
		        };
		    }
		}(),
		
		/**
		@Name: sb.events.log
		@Description: used internally to keep track of all events registered on the page
		*/
		log : [],
		
		/**
		@Name: sb.events.preventDefault
		@Description: Used to prevent default actions from occurring on an event e.g. links that are clicked would do whatever is in the event handler but not the ordinary default event of goign to the page specified by the href attribute.
		@Param: Object event An event reference as passed to a handler function as e
		@Example:
		var myEvent = sb.events.add('#myList', 'click', function(e){
			sb.events.preventDefault(e);
		});
		
		*/
		preventDefault : function(e){
			 
			if(typeof e.stopPropagation == 'function'){
				e.preventDefault();
			} else {
				e.returnValue = false;
			} 
		},
		
		record : function(evt){
			sb.events.log.push(evt);
			return evt;
		},
		
		/**
		@Name: sb.events.relatedTarget
		@Description: Related targets are specified for events that have related targets. e.g. mouseover and mouseout.  when the event is mouseout the relatedTarget refers to the element the mouse is moving to.  When the event is mouseover, the relatedTarget refers to the element that the mouse is moving from. 
		@Param: Object event An event reference as passed to a handler function as e
		@Return: Element The related target DOM node as explained in the description
		@Example:
		var myEvent = sb.events.add('#myList', 'click', function(e){
			var target = sb.events.relatedTarget(e);
			alert(target.nodeName);
		});
		*/
		
		relatedTarget : function(e){
			var tar = false;
			switch(e.type){
				case 'mouseout':
					tar = e.relatedTarget || e.toElement;
					break;
				
				case 'mouseover':
					tar = e.relatedTarget || e.fromElement;
					break;
			}
			
			try{
				
				if (tar.nodeType && (tar.nodeType== 3 || tar.nodeName == 'EMBED')){
				  tar = tar.parentNode;
				}
				
			} catch(error){
				
				tar = sb.events.target(e);
				
			}
			return sb.s$(tar);
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
		@Description: Removes all event listeners added with sb.events.add or sb.elements or s$'s event method
	
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
		@Name: sb.events.stopAndPrevent
		@Description: used to stop event bubbling and prevent default actions for an event, see sb.events.stopPropagation and sb.events.preventDefault for more info
		@Param: Object event An event reference as passed to a handler function as e
		@Example:
		var myEvent = sb.events.add('#myList', 'click', function(e){
			sb.events.stopAndPrevent(e);
			
		});
		
		*/
		stopAndPrevent : function(e){
			sb.events.stopPropagation(e);
			sb.events.preventDefault(e);
		},
		
		/**
		@Name: sb.events.stopAndPrevent
		@Description: Used to stop event bubbling e.g. when a ordered list is clicked the event bubbles to its children.
		@Param: Object event An event reference as passed to a handler function as e
		@Example:
		var myEvent = sb.events.add('#myList', 'click', function(e){
			sb.events.stopPropagation(e);
		});
		
		*/
		stopPropagation : function(e){
			 
			if(typeof e.stopPropagation == 'function'){
				e.stopPropagation();
			} else {
				e.cancelBubble = true;
			} 
		},
		
		/**
		@Name: sb.events.target
		@Description: Determines the target of the event, becaus eof event bubbling, this is not necessarily the this of the event. e.g when an orderlist is clicked, the target might have been one of the child list items, however, it's click event fires because teh chidlren are within it.  By referencing the target you can see which child was clicked.
		@Param: Object event An event reference as passed to a handler function as e
		@Return: Element The target DOM node as explained in the description
		@Example:
		var myEvent = sb.events.add('#myList', 'click', function(e){
			var target = sb.events.target(e);
			alert(target.innerHTML);
		});
		
		*/
		target : function(e){
			var tar = (e.target !==undefined) ? e.target : e.srcElement;
		   
		   if (tar.nodeType && (tar.nodeType== 3 || tar.nodeName == 'EMBED')){
		      tar = tar.parentNode;
		   }
		
		   return sb.s$(tar);
		}
		
	};
	
	/**
	@Name: sb.element
	@Description: Used to create DOM nodes.  If a string is passed to the fuction it simply return document.createElement(str);
	@Param: Object o An object of properties which are used to contruct the DOM object,  all properites are appending as properties to the dom object.  sb.elements have many methods whcih are all listed in the sb.element.prototype object below
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
		if(sb.typeOf(o)=='object' ){
			if(o.tag == 'input' && sb.dom.createNamedElement){
				el = new sb.dom.createNamedElement(o.type, o.name);
			} else {
				el = document.createElement(o.tag);
			}
		}
		
		//copy properties from the sb.element prototype
		sb.objects.infuse(sb.element.prototype, el);
		o = sb.objects.copy(o);
		 
		if(typeof o.addAttributes !='undefined'){
			this.setAttributes.call(el, o.addAttributes);
			delete o.addAttributes;	
		}
		
		if(typeof o.styles !='undefined'){
			this.styles.call(el, o.styles);
			delete o.styles;	
		}
		
		if(typeof o.children !='undefined'){
			for(c=0;c<o.children.length;c++){
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
		
		try{
			//remove attributes for ie's sake
			el.removeAttribute('tag');
		}catch(e){
			sb.consol.log("Error building new sb.element: "+sb.objects.dump(o));
		}
		return el;
	};
	
	/**
	@Name: sb.element.protoype
	@Description: Methods of sb.element instances. Assume that myElement is an sb.element instance in all examples of sb.element.prototype
	*/
	
	sb.element.prototype = {
		/**
		@Name: sb.element.prototype.s$
		@Description: Return the results of an s$ lookup within that node.  See sb.$ arguments for more information
		@Example:
		var myDiv = s$('#myDiv');
		//returns an array of all li elements in myDiv
		myDiv.s$('li');
		
		//returns an array of all elements with className blue inside myDiv.
		myDiv.s$('.blue');
		*/
		s$ : function(pattern){
			return sb.s$(this, pattern);	
		},
		/**
		@Name: sb.element.prototype.addClassName
		@Description: Adds a className to the sb.element, using this methods sb.element instances can have multiple classNames
		@Param: String c The classname to add
		@Return: returns itself
		@Example:
		myElement.addClassName('redStripe');
		*/
		addClassName : function(className){
			this.className += ' '+className;
			
			return this;
		},
	  
		/**
		@Name: sb.element.prototype.addClassName
		@Description: Adds a className to the sb.element, using this methods sb.element instances can have multiple classNames
		@Param: String c The classname to add
		@Return: returns itself
		@Example:
		myElement.setAttributes({friend : 'tim', name : 'joe'});
		<myElement friend="tim" name="joe">
		*/
		setAttributes : function(o){
			var t=this;
			sb.objects.forEach.call(o, function(val,prop,o){
				t.setAttribute(prop, val);
			});
			return this;
		},
		
		/**
		@Name: sb.element.prototype.append
		@Description: Appends another DOM element to the element as a child
		@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
		@Example:
		myElement.append(myOtherElement);
		*/
		append : function(el){return this.appendChild(sb.$(el));},
		
		/**
		@Name: sb.element.prototype.appendTo
		@Description: Appends the element to another DOM element as a child
		@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
		@Return: Element A refernce to the appended node
		@Example:
		//appends myElement to the page body
		myElement.appendTo('body');
		
		//appends myElement to a div with the ID "myDiv"
		myElement.appendTo('#myDiv');
		
		*/
		appendTo : function(el){
			return sb.$(el).appendChild(this);
		},
		
			/**
		@Name: sb.element.prototype.appendToTop
		@Description: Appends the element to the top DOM element as a child
		@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
		@Return: Element A refernce to the appended node
		@Example:
		//appends myElement to the page body
		myElement.appendToTop('body');
		
		//appends myElement to a div with the ID "myDiv"
		myElement.appendToTop('#myDiv');
		
		*/
		appendToTop : function(el){
			el = sb.$(el);
		
			if(el.childNodes.length ===0){
				return this.appendTo(el);
			} else {
				return this.appendBefore(el.firstChild);
			}
		},
	
		/**
		@Name: sb.element.prototype.appendAfter
		@Description: Appends the element after another DOM element as a sibling
		@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
		@Example:
		//appends myElement to the parent of "#myDiv" as a sibling of "#myDiv" directly after "#myDiv"
		myElement.appendAfter('#myDiv');
		
		*/
		appendAfter : function(after){
			
			var el = sb.s$(after);
			
			var nxtSib = el.getNextSibling();
			
			if(nxtSib){
				return nxtSib.parentNode.insertBefore(this, nxtSib);
			} else {
				return this.appendTo(el.parentNode);
			}
			
		},
		
		/**
		@Name: sb.element.prototype.appendBefore
		@Description: Appends the element before another DOM element as a sibling
		@Param: Element, String el Another DOM element reference or a string that can be passed through sb.$ to return a DOM node.
		@Example:
		//appends myElement to the parent of "#myDiv" as a sibling of "#myDiv" directly before "#myDiv"
		myElement.appendBefore('#myDiv');
		
		*/
		appendBefore : function(before){
			before = sb.$(before);
			return before.parentNode.insertBefore(this, before);
		},
		
		/**
		@Name: sb.element.prototype.event
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
		event : function (evt, func){
			
			var event = sb.events.add(this, evt, func);
			this.eventsAdded.push(event);
			this.lastEventAdded = event;
			return this;
			
		},
		
		/**
		@Name: sb.element.prototype.lastEventAdded
		@Description: Used keep track of the last event added to a sb.element.  
		*/
		lastEventAdded : [],
		
		/**
		@Name: sb.element.prototype.eventsAdded
		@Description: Used keep track of events added to a sb.element.  All events added with this.event are pushed into this array where they are stored for removal
		
		*/
		eventsAdded : [],
		
		/**
		@Name: sb.element.prototype.events
		@Description: Used to add events to an s$
		@Param: object events
		@Example:
		var myDiv = s$('#myDiv');
		myDiv.events({
			click : function(){
				do something
			},
			mouseover : function(){
				//do somthing
			}
		});
		*/
		events : function(events){
			for(var event in events){
				if(typeof events[event] =='function'){
					this.event(event, events[event]);
				}
			}
			
			return this;
		},
		
		/**
		@Name: sb.element.prototype.eventRemove
		@Description: Removes an event created with sb.element.prototype.event
		@Param: String evt An event reference returned from the sb.element instances event method above.
		
		@Example:
		
		//sets the backgroundColor peroperty to red
		var myEvt = myElement.event('click', function(e){
			alert(this.innerHTML);
		});
		
		myElement.eventRemove(myEvt);
		*/
		eventRemove : function (evt){
			sb.events.remove(evt);
			return this;
		},
		
		/**
		@Name: sb.element.prototype.eventRemove
		@Description: Removes all event observers for the sb.element that were added using this.event() or this.events()
	
		@Example:
		
		myElement.eventsRemoveAll();
		*/
		eventsRemoveAll : function(){
			this.eventsAdded.forEach(function(evt){
				sb.events.remove(evt);
			});
			this.eventsAdded = [];
			return this;
		},
		
		/**
		@Name: sb.element.prototype.infuse
		@Description: Used to infuse an sb.element instance
		@Example:
		
		//adds a property called name which is set to tim, and add a  property called type with a value of text
		myElement.infuse({name : 'tim', type : 'text'});
		*/
		infuse : function(o){
			sb.objects.infuse(o, this);
			
			return this;
		},
		
		/**
		@Name: sb.element.prototype.getPosition
		@Description: Used to calculate the bounds top, right, bottom, left, h, and w of a DOM node.  h and w are height and width.  Attaches those properties to the element itself
		@Return: Object An object with top, right, bottom, left, h, and w properties
		@Return: returns itself
		@Example:
		var pos = myElement.getPosition();
		alert(pos.left);
		myElement.getPosition({pos : 'rel'});
		myElement.getPosition({accountForScroll : 1});
		*/
		
		getPosition : function(params){
			params = params || {};
			var orig = this;
		
			var el=this;
			
			orig.top =0;
			orig.left =0;
			
			do{
				orig.top += el.offsetTop;
				orig.left += el.offsetLeft;
				
				if(params.pos =='rel'){
					el = false;
				} else{
					try{el = el.offsetParent;}catch(e){el = false;}
					
				}
			} while(el);
			
			if(params.accountForScrollBar){
				sb.browser.getScrollPosition();
				if(sb.browser.scrollY){
					orig.top -=sb.browser.scrollY;
				}
				
				if(sb.browser.scrollX){
					orig.left -=sb.browser.scrollX;
				}
			}
			
			orig.getDimensions();
			
			//alias for w and h
			orig.w = orig.width;
			orig.h = orig.height;
			orig.bottom = orig.top+orig.width;
			orig.right = orig.left+orig.height;
			
			return orig;	
		},
	
		/**
		@Name: sb.element.prototype.getDimensions
		@Description: calculates and assigns width and height properties to an to an element
		@Example:
		myElement.getDimensions();
		alert(myElement.width);
		*/
		getDimensions : function() {
			
		    var display = this.getStyle('display');
		    // Safari bug
		    if (display != 'none' && display !== null) {
		    	this.width = this.offsetWidth;
		    	this.height = this.offsetHeight;
		     
		    } else {
		    	
			    // All *Width and *Height properties give 0 on els with display none,so enable the el temporarily
			
			    var origStyles = {
			    	visibility : this.style.visibility,
			    	position : this.style.position,
			    	display : this.style.display
			    };
			    
			    this.styles({
			    	visibility : 'hidden',
			    	position : 'absolute',
			    	display : 'block'
			    });
				
			  	this.width = this.clientWidth;
			  	this.height = this.clientHeight;
			  	this.styles(origStyles);
		    }
		    return this;
		},
		/**
		@Name: sb.element.prototype.getFirstChild
		@Description: returns the first element type node (nodeType ==1) of a parentNode
		@Return: element sb.element
		
		@Example:
		//get the nodes firstChild
		myParentSbElement.getFirstChild();
		
		*/
		getFirstChild : function(){
			return sb.$.getFirstChild(this);
		},
		
		/**
		@Name: sb.element.prototype.getLastChild
		@Description: returns the last element type node (nodeType ==1) of a parentNode
		@Return: element sb.element
		
		@Example:
		////get the nodes lastChild
		myParentSbElement.getLastChild();
		
		*/
		getLastChild : function(){
			return sb.$.getLastChild(this);
		},
		
		/**
		@Name: sb.element.prototype.getWidth();
		@Description: Gets the width of an element
		@Return: returns the elements width as a number in pixels with no unit
		@Example:
			var width = myElement.getWidth();
			
		*/
		getWidth : function(){
			return this.getDimensions(this).width;
		},
			  
		/**
		@Name: sb.element.prototype.getHeight();
		@Description: Gets the height of an element
		@Return: returns the elements width as a number in pixels with no unit
		@Example:
			var width = myElement.getHeight();
			
		*/
		getHeight : function(){
			return this.getDimensions(this).height;
		},
	
		/**
		@Name: sb.element.prototype.getNextSibling
		@Description: Finds the next sibling element of the element on which this is called
		@Return: Element A DOM element reference to the next sibling
		
		@Example:
		myElement.getNextSibling();
		*/
		getNextSibling : function(){
			return sb.s$(sb.$.getNextSibling(this));
		},
		
		/**
		@Name: sb.element.prototype.getPreviousSibling
		@Description: Finds the previous sibling element of the element on which this is called
		@Return: Element A DOM element reference to the previous sibling
		
		@Example:
		myElement.getPreviousSibling();
		*/
		getPreviousSibling : function(){
			return sb.s$(sb.$.getPreviousSibling(this));
		},
		
		/**
		@Name: sb.element.prototype.getX
		@Description: Calculates the absolute x position of an element
		@Return: Integer the x position of an element
		
		@Example:
		myElement.getX();
		*/
		getX : function(){
			var x = 0, el=this;
			while(el !== null){
				x += el.offsetLeft;
				el = el.offsetParent;
			}
			return x;
		},
		
		/**
		@Name: sb.element.prototype.getY
		@Description: Calculates the absolute x position of an element
		@Return: Integer the y position of an element
		
		@Example:
		myElement.getY();
		*/
		getY : function(){
			var y = 0, el=this;
			while(el !== null){
				y += el.offsetTop;
				el = el.offsetParent;
			}
			return y;
		},
		
		/**
		@Name: sb.element.prototype.hasClassName
		@Description: Checks to see if the element has the className specified.  Elements can have more than one className.
		@Return: Boolean True if the element contains the className and False if it doesn't
		@Param: String c The className to check for
		@Example:
		myElement.return('redStripe');
		*/
		hasClassName: function(c){
			return sb.arrays.inArray.call(this.className.split(' '), c);
		},
		
		/**
		@Name: sb.element.prototype.hide
		@Description: Sets the display of the element to none, removing its from being displayed on the page
		@Return: returns itself
		@Example:
		myElement.hide();
		*/
		hide : function(){
			this.style.display = 'none';
			return this;
		},
		
		/**
		@Name: sb.element.prototype.mv
		@Description: Moves an element to a specific x, y and z position either absolutly or relatively is specified
		@Param: Number x The x position to move the element to
		@Param: Number y The y position to move the element to
		@Param: Number z The zIndex to move the element to
		@Return: returns itself
		@Example:
		myElement.mv(200,200,999);
		//move sthe node to absolute position 200,200 and a zIndex of 999
		*/
		mv : function(x,y,z){
			
			this.style.left = x+'px';
			this.style.top = y+'px';
			if(z){
				this.style.zIndex = z;
			}
			
			if(this.getStyle('position') == 'static'){this.style.position = 'absolute';}
			
			this.getPosition();
			return this;
		},
		
		/**
		@Name: sb.element.prototype.remove
		@Description: Removes an element from the DOM
		@Return: returns itself
		@Example:
		myElement.remove();
		*/
		remove : function(){
			if(typeof this.parentNode !='undefined'){
				this.parentNode.removeChild(this);
			}
			return this;
		},
		
		/**
		@Name: sb.element.prototype.removeClassName
		@Description: Removes a className from the elements className array.  Elements can have more than one className
		@Param: String c Specified the className to remove from the element
		@Return: returns itself
		@Example:
		myElement.removeClassName('redStripe');
		*/
		removeClassName : function(className){
			var a = this.className.split(' ');
			this.className = sb.arrays.remove.call(a, className).join(' ');
			return this;
		},
		
		/**
		@Name: sb.element.prototype.replace
		@Description: Replaces an element with another element in the DOM
		@Param: Object/String A reference to another DOM node, either as a string which is passed to the sb.$ function or as an element reference
		@Return: returns itself
		@Example:
		myElement.replace('#myOtherElement');
		*/
		replace : function(node){
			node = sb.$(node);
			if(typeof node.parentNode !='undefined'){
				node.parentNode.replaceChild(this, node);
			}
			node = null;
			return this;
		},
		
		/**
		@Name: sb.element.prototype.show
		@Description: Switches an elements display back to whatever its default was.  Tis is the reciprocal method for myElement.hide();
		@Return: returns itself
		@Example:
		myElement.show();
		*/
		show : function(){
			try{
			this.style.display = (this.getStyle('display')=='none') ? 'block' : this.getStyle('display'); 
			} catch(e){
				this.style.display='block';
			}
			return this;
		},
		
		/**
		@Name: sb.element.prototype.styles
		@Description: 
		@Param: Object params An object with css style/value pairs that are applied to the object
		@Return: returns itself
		@Example:
		myElement.styles({
			backgroundColor : '#000000',
			fontSize : '18px',
			border : '1px solid #FF0000'
		});
		*/
		styles : function(params){
			
			for(var prop in params){
				if(params.hasOwnProperty(prop)){
					this.setStyle(prop, params[prop]);
				}
			}
			
			return this;
		},
		
		/**
		@Name: sb.element.prototype.getStyle
		@Description: 
		@Param: calculates the style of an sb.element
		@Return: returns property value
		@Example:
		myElement.styles({
			backgroundColor : '#000000',
			fontSize : '18px',
			border : '1px solid #FF0000'
		});
		*/
		getStyle : function(prop){
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
					
			try{
				if (this.style[prop]) {
					val = this.style[prop];
					
				} else if (this.currentStyle) {
					
					prop = sb.strings.toCamel.call(prop);
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
					val = val.toLowerCase();
					if(val == 'rgba(0, 0, 0, 0)'){val = 'transparent';}
					
					if(typeof sb.colors.html !='undefined'){
						if(sb.colors.html[val]){
							val = sb.strings.hex2rgb.call(sb.colors.html[val]);
						}
					}
					
					if(val.match("^#")){
						val = sb.strings.hex2rgb.call(val);
					}
				
					return val;
				} else {
					return null;
				}
				
			} catch(e){
				sb.consol.log(sb.messages[18]+prop+"\nID: #"+this.id+"\nError: "+e);
			}
		},
		
		setStyle : function(prop, val){
			
				if(sb.arrays.inArray.call(sb.styles.pxProps, prop) && val !=='' && !val.match(/em|cm|pt|px|%/)){
					val +='px';
				}
				
				prop = sb.strings.toCamel.call(prop);
			
				if(prop == 'opacity'){
					if(val <= 0){ val =0;}
					if(val >= 1){ val =1;}
					this.style.opacity = val;
					
					if(typeof this.style.filter == 'string' && sb.browser.ie6===1){
						this.style.filter = "alpha(opacity:"+val*100+")";
					}
					
				} else {
					
					try{
						this.style[prop] = val;
					}catch(e){}
				}
		},
		
		/**
		@Name: sb.element.prototype.toggle
		@Description: Switches an object's display between hidden and default
		@Return: returns itself
		@Example:
		myElement.toggle();
		*/
		toggle : function(){
			if(this.style){
				this.style.display = (this.getStyle('display') ==='none') ? '' : 'none';
			}
			return this;
		},
		
		typeOf : function(){
			return 'sb.element';
		},
		
		/**
		@Name: sb.element.prototype.unsetAttributes
		@Description: Unsets the attributes of the element that are in the argument array
		@Param: Array a A list of strings which represent the values to unset
		@Example:
		myElement.unsetAttributes(['friend', 'nextKin']);
		*/
		unsetAttributes : function(a){
			var t=this;
			a.forEach(function(v){
				t.setAttribute(v, '');
			});
			return this;
		},
		
		/**
		@Name: sb.element.prototype.wh
		@Description: Sets the width and height of the element
		@Param: String/Number w The element width desired, can be specified as a number e.g. 100 or as a percent '100%'
		@Param: String/Number h The element height desired, can be specified as a number e.g. 100 or as a percent '20%'
		@Example: 
		myElement.wh(100, 200);
		*/
		wh : function(w,h){
			this.style.width = w+'px';
			this.style.height = h+'px';
			return this;
		}
	};
	
	
	/**
	@Name: sb.nodeList
	@Description: Used to create nodeLists which are groups of sb.elements that have many of the same methods as sb.element but which act on all sb.elements in the nodeList. It also has all the properties of an sb.array. These are returned by sb.s$
	*/
	sb.nodeList = function(nodes){
			
		this.nodes = nodes;
		
		for(var prop in sb.element.prototype){
			if (sb.typeOf(sb.element.prototype[prop]) == 'function') {
				this[prop] = this.addElementPrototypes(prop);
			}
		}
		
		var nl= this;
		['forEach', 'map', 'filter', 'every', 'some', 'indexOf', 'lastIndexOf', 'inArray'].forEach(function(v,k,a){
			nl[v] = function(func){
				return nl.nodes[v](func);
			};
		});
	};
	
	sb.nodeList.prototype  = {
		
		/**
		@Name: sb.nodeList.prototype.add
		@Description: add more dom nodes, either array o single node to a sb.nodeList
		@Example: 
		var nodes = s$('ol li');
		//adds element with id 'wrapper' to the node list
		nodes.add('#wrapper');
		//add all the links to the nodeList
		nodes.add('a');
		*/
		add : function(el){
			el = sb.s$(el);
			if(sb.typeOf(el) == 'array'){
				for(var i=0;i<el.length;i++){
					this.nodes.push(el[i]);
				}
			} else {
				this.nodes.push(el);
			}	
			
			return this;
		},
		
		/**
		@Name: sb.nodeList.prototype.drop
		@Description: drop dom nodes, either array o single node from a sb.nodeList
		@Example: 
		var nodes = s$('ol li');
		//adds element with id 'wrapper' to the node list
		nodes.drop('#wrapper');
		//add all the links to the nodeList
		nodes.drop('a');
		*/
		drop : function(el){
			
			var t = this;
			el = sb.s$(el);
			
			this.nodes = t.nodes.filter(function(v){
				if(sb.typeOf(el) == 'sb.element'){
					return v != el;
				} else {
					return !el.nodes.some(function(v1){return v===v1;});
					
				}
				
			});
			return this;
		},
		
		/**
		@Name: sb.nodeList.prototype.nodes
		@Description: Used Internally. An array of sb.elements
		*/
		nodes : [],
		
		/**
		@Name: sb.nodeList.prototype.addElementPrototypes
		@Description: Used Internally. Adds the prototypes from sb.element to the group
		*/
		addElementPrototypes : function(func){
			var t = this;
			return function(){
				
				var args = arguments;
				
				t.nodes.forEach(function(node){
					if(sb.typeOf(node) == 'sb.element'){
						node[func].apply(node, args);
					}
			
				});
				return this;
	
			};
		},
		
		/**
		@Name: sb.nodeList.prototype.typeOf
		@Description: Used Internally for sb.typeOf
		*/
		typeOf : function(){
			
			return 'sb.nodeList';
		}
		
	};
	
	/**
	@Name: sb.math
	@Description: Used Internally. A placeholder for sb.math
	*/
	sb.math = {
		/**
		@Name: sb.math.flip
		@Description:  flips 0 to 1 or 1 to 0
		@Example:
		//equals 0
		sb.flip(1);
		*/
		flip : function(a){
			return (a===1) ? 0 : 1;	
		}
	};
		
	(function(){
		if(!Array.prototype.forEach){
			sb.include('arrays.js1_5');
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
		
		if(typeof window.sbNoGlobals === 'undefined'){
			//if globals are enabled add them
			sb.addGlobals();
			
		}
		
	})();
		
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
	sb.events.add(window, 'scroll', sb.browser.getScrollPosition);
	sb.events.add(window, 'unload', function(e){
		sb.onleavepage.forEach(function(v){
			if(typeof(v) =='function'){v(e);}
		});
		sb.events.removeAll();
	});
	window.sb = sb;
	window.Sb = sb;
})();