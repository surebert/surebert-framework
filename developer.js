/**
@Author: Paul Visco of http://elmwoodstrip.org?u=paul
@Version: 4.06 12/26/07
@Description:These functions are used when developing.  This file does not need to be included in production version of your site.  It basically allows for debugging during development. Used Internally. 
*/

sb.include('date');
sb.include('css.rules');

sb.include('math.rand');
sb.include('colors.rand');
sb.include('strings.nl2br');
sb.include('element.prototype.disableSelection');

sb.debug =1;
sb.developer = {};

//sb.devel =1;

/**
@Name: sb.messages
@Description: Used Internally. A lookup table for surebert error messages
*/
sb.messages = {
	
	10 :"FlashPlayer not installed or not detected.  You will need at least Flash play 8 to use sb's flash functionality\n GET FLASH PLAYER: http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash",
	11 :"You have disabled flash functionality. i.e. sound and flash shared object storage space access by setting the sbNoFlash=1 before calling surebert.",
	12 :"Surebert has tried to play this track 5 times with no sucess, perhaps you have flash functionality disabled or the sound does not exist.",
	13: " is not a well formatted javascript file and cannot be loaded by sb.javascript",

	15 : 'The surebert.swf has not loaded yet and cannot receive calls to its internal functions, e.g. upload, sound, setDebug.',
	16 : "sb.sound has tried to play the following file 10 times but surebert.flashGate has not loaded.  Try pushing the play function into the sb.onFlashGateLoad array \nSOUND: ",
	17 : "The following image is not reporting height or width properly, perhaps because it has never been in view and so will not work with sb.ie6.pngFix, try running pngFix after it is in view\n",
	18 : "Cannot read style: "
};

/**
@Name: sb.objects.alert
@Description: Alerts the properties and their values for an object
@Param: Object o The object to alert the properties of
@Example:
	var o = {name : 'paul, language : 'javascript'};
	sb.objects.alert({o});
*/
sb.objects.alert =  function(o){
	window.alert(sb.objects.dump(o));
};
	
/**
@Name: sb.ajax.log
@Description: Used Internally. Another lookup table for surebert error messages
*/
sb.ajax.log = function(logId, data, color){
	
	var message = '<h2>'+sb.ajax.log.messages[logId]+"</h2>"+(data || '');
	
	sb.consol.ajaxLog(message);
};

/**
@Name: sb.ajax.log.messages
@Description: Used Internally. Another lookup table for surebert error messages
*/
sb.ajax.log.messages = {
	0 : 'Could not create ajax request, must use firefox, ie 6+ for win, safari, netscape or opera.',
	1 : 'Ajax - Data sent --&gt;',
	2 : '&lt;-- Ajax - Response returned',
	3 : 'invalid XML returned',
	4 : 'Error evaling javascript from server',
	5 : 'Dom node referenced by ajax object does not exist'
};

/**
@Name: sb.debugMe
@Package: sb.developer.js
@Description: Sends surebert debugging info to the surebert consol.  It shows window size, agent, version, flashplayer and cookies.  This only fires after the document is finsihed loading. If you call it before, it will wait until the document has loaded before firing.
@Example:
sb.debugMe();
*/
sb.debugMe = function(){
	/*
	if(sb.initialized !=1){
		sb.ondomload.push(sb.debugMe);
		return;
	}*/
	
	//list all cookies that are stored
	sb.include('cookies');
	sb.include('swf');
	
	sb.consol.log([
		'agent='+window.navigator.userAgent,
		'browser detection:'+"\nsb.browser.agent="+sb.browser.agent+"\nsb.browser.version="+sb.browser.version,
		'window.width='+sb.browser.w+"\n"+'window.height='+sb.browser.h,
		'flashPlayer='+sb.swf.version,
		'cookies found: '+sb.cookies.listAll()
		].join("\n\n")
	);

};

/**
@Name: sb.debugStyle
@Package: sb.developer.js
@Description: Creates a new debugging color scheme for use with the sb.consol debugging system.
@Param: String color The text color of the debug message produced with the resulting function
@Param: String backgroundColor The background color of the debug message produced with the resulting function
@Example:
var paulDebugStyle = new sb.debugStyle('red', 'yellow');

paulDebug('hello world');
//this debugs the message 'hello world' to the surebert debug consol in red text on a yellow background.
*/
sb.debugStyle = function(color, backgroundColor, allowHTML){
	return function(message){
		sb.consol.write(message, color, backgroundColor, allowHTML);
	};
};

/**
@Name: sb.consol
@Found In: sb.developer.js
@Description: The consol is a cross browser debugging area that allows the programmer and surebert to debug messages to the screen in the handy dandy surebert consol.  The consol is a element node that gets inserted at the top of the page before all other content.  Messages can be written with difference background colors to speficiy what type of message sthey are.  By default red is an error, orange is a warning, green is a log message and pink is a debug message.  You can make you own combos very easily.  ATTENTION: If you specify a function called sb.consol.onlog - the strings that would normally be written to the consol, during debugging are redirected to the function with the string passed as the only argument.
@Example:
//this would override writing to the consol with your own function whenever sb.consol functions were called.
sb.consol.onlog = function(str){
	alert(str);
}
*/

sb.consol = {
	/**
	@Name: sb.consol.write
	@Description:  Used internally.  This should never been accessed directly.  If you wish to write to the consol use sb.consol.error, sb.consol.warning, sb.consol.log, or sb.consol.debug all of which use this method to write formatted strings to the sb.consol.  If you wish to create you own debug message style, refer to sb.debugStyle.
	*/
	write : function(str, color, bgColor, allowHTML){
			
		
		var note,preWrap;
		str = String(str);
		
		if(sb.debug ==1){
			color = color || 'green';
			bgColor = bgColor || 'black';
			
			if(typeof this.onlog == "function"){
				this.onlog(str);
			} else {
				
				if(typeof this.box == 'undefined'){
					this.open();
				}
				
				if(typeof allowHTML == "undefined"){
					str = sb.strings.escapeHTML.call(String(str));
				}
				
				//str = sb.strings.linkify.call(str);
				
			 	sb.css.rules.write("sbConsolNote", 'display:block;font-size:0.8em;margin-bottom:20px;padding:10px;font-size:12px;font-family:tahoma,verdana;text-align:left;');
			 	sb.css.rules.write("sbConsolNote a", 'color:orange;font-size:1.2em;');
			 	sb.css.rules.write("sbConsolNote h1", 'margin-bottom:0;font-size:1.2em;');	
			 	sb.css.rules.write("sbConsolNote hr", 'height:2px;margin:3px 0 3px 0;color:orange;background-color:orange;');

			 	str = str.replace(/\n/g, "<br />");
			 	str = str.replace(/\t/g, "&nbsp;&nbsp;&nbsp;&nbsp; ");
			
			 	note = new sb.element({
			 		tag : 'sbConsolNote',
			 		innerHTML : this.num+'. '+new sb.date().format('m/d - g:i:s a')+"<hr />"+str
			 	});
			 	
			 	
			 	note.styles({
			 		backgroundColor : bgColor,
			 		color : color
			 	});
			 	
			 	note.appendToTop(this.box.firstChild);
				note = null;
				str =null;
				this.num++;
			}
		}
		
	},
	
	/**
	@Name: sb.consol.error
	@Description:  Write to the sb.consol in white color text on a red background. You should reserve this for debugging error messages.  Only works if sb.debug =1 which it is by default otherwise debugging is turned off.
	@Param: String message The message to write to the consol in error style
	@Example:
	sb.consol.error('OMG there was a horrible error in when trying to make the top div');
	*/
	error : new sb.debugStyle('white', 'red'),
	
	/**
	@Name: sb.consol.warning
	@Description:  Write to the sb.consol in red color text on a yellow background. You should reserve this for debugging warning messages. Only works if sb.debug =1 which it is by default otherwise debugging is turned off.
	@Param: String message The message to write to the consol in warning style
	@Example:
	sb.consol.warning('You should specify the height of the widget in pixels');
	*/
	warning : new sb.debugStyle('red', 'yellow'),
	
	/**
	@Name: sb.consol.debug
	@Description:  Write to the sb.consol in white color text on a light blue background. You should reserve this for debugging non-error messages. Only works if sb.debug =1 which it is by default otherwise debugging is turned off.
	@Param: String message The message to write to the consol in debug style
	@Example:
	sb.consol.debug('You should specify the height of the widget in pixels');
	*/
	debug : new sb.debugStyle('white', '#19a29a'),
	
	/**
	@Name: sb.consol.ajaxLog
	@Description:  Used internally to debug ajax messages
	*/
	ajaxLog : new sb.debugStyle('#8fb0d2', '#1866b7', 1),
	
	/**
	@Name: sb.consol.dump
	@Description:  Dumps an objects properties to the sb.consol
	@Param: Object/String str Object reference of a string that references an object by ID e.g. '#mYDiv'
	@Example:
	var myObj = {
		name: 'paul,
		day : 'monday'
	};
	
	sb.consol.dump(myObj);
	*/
	dump : function(str){
		var obj;
		if(typeof str =='string'){
			obj = eval(str);
		} else {
			obj = str;
		}
		
		this.debug('OBJECT: '+str+"\n"+sb.objects.dump(obj), 'white', 'red', 1);
	},
	
	/**
	@Name: sb.consol.log
	@Description:  Used Internally.
	*/
	log : new sb.debugStyle('yellow', 'green'),
	
	/**
	@Name: sb.consol.num
	@Description: Used internally
	*/
	num : 1,
	
	/**
	@Name: sb.consol.open
	@Description: Used internally
	*/
	open : function(){
		var self = this;
		this.box = new sb.element({
			tag : 'sbConsol',
			styles : {
				width:'100%',
				height:'200px',
				marginBottom: '20px',
				borderTop : '5px solid black',
				backgroundColor: '#84b439',
				display : 'block'
				
			},
			resize : function(h){
				self.box.style.height = h+'px';
				self.box.firstChild.style.height = h+'px';
			},
			children : [
				{
					tag : 'div',
					styles: {
						height : '100%',
						overflow : 'auto'
					}
				}
			]
		});
		
		this.box.appendToTop(((sb.browser.ie6) ? 'body' : 'html'));
		
		this.resizer = new sb.element({
			resizing : 0,
			tag : 'div',
				children : [
			{
				tag : 'span',
				innerHTML : 'clear',
				styles : {
					marginLeft : '10px',
					cursor : 'pointer'
				},
				events : {
					mousedown : function(){
						self.box.firstChild.innerHTML ='';
					}
				}
			},{
				tag : 'span',
				innerHTML : 'hide',
				styles : {
					marginLeft : '10px',
					cursor : 'pointer'
				},
				events : {
					mousedown : function(){
						self.box.resize(self.box.getY());
						self.resizer.mv(self.box.getX(), 0, 999);
					}
				}
				
			},
			{
				tag : 'span',
				innerHTML : 'capture',
				styles : {
					marginLeft : '10px',
					cursor : 'pointer'
				},
				events : {
					click : function(){
						var randColor = sb.colors.rand(1);
						
						var pwin = window.open('', sb.uniqueID(), 'width=500,height=600,resizeable=yes,scrollbars=yes');
						pwin.document.write('<style type="text/css">body{background-color:black;color:green;}</style><h2>Captured At'+new Date()+' from '+window.location+'</h2>'+self.box.firstChild.innerHTML);
						pwin.document.close();
						pwin.focus();
					}
				}
				
			}
			],
			title : 'Drag to resize consol, double click to hide',
			styles: {
				height : '15px',
				width : '100%',
				backgroundColor :'black',
				color: 'white',
				cursor : 'n-resize',
				position : 'relative',
				left : '0px',
				padding: '2px 0 2px 0',
				textAlign : 'left'
			},
			events : {
				mousedown : function(e){
					this.origY = this.getY();
					this.style.width = self.box.offsetWidth+'px';
					this.style.position = 'absolute';
					this.resizing =1;
				},
				dblclick : function(e){
					if(sb.events.target(e).nodeName == 'DIV'){
						self.box.resize(0);
						self.resizer.mv(self.box.getX(), self.box.getY(), 999);
					}
				}
			}
		});
		this.resizer.disableSelection();
		
		sb.events.add(document, 'mouseup', function(){
			self.resizer.resizing =0;
		});
		
		sb.events.add(document, 'mousemove', function(e){
			var rszr = self.resizer;
			
			if(rszr.resizing ==1 && e.clientY > self.box.getY()){
				rszr.mv(self.box.getX(), e.clientY, 999);
				self.box.resize(e.clientY);
				
			}
			
		});
		this.resizer.appendTo(this.box);
        return;
		
	}
};

/**
@Name: sb.performance
@Description: Determines the time it takes to run a function in milliseconds
@Param: Function func The function to time
@Return: Number The number of milliseconds required to run the function.
@Example:
function getImages(){
	var images = sb.$('img');
}
var timeItTakes = sb.performace(getImages);

*/
sb.performance = function(func){
    var t0 = new Date().getTime();
    func();
    return new Date().getTime() - t0;
};