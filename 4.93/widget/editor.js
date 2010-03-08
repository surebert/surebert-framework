sb.include('forms.textarea');
sb.include('cookies');
sb.include('flashGate');
/**
@Name: sb.widget.editor
@Author: Paul Visco v1.01 11/03/07 12/15/08
@Description: The surebert widget creates a live text editor.  It replaces all iframes passed to the constructor with real text editors.  if they cannot be built it replaces them with empty textareas.  The textareas have setContent and getContent methods just like real sb.widget.editor instances so that you can set and get the data in the same way.
@Param: string id The id of the iframe to replace with an editor
@Param: function onload A function which is run after the editor is created.  The 'this' in the function is the editor.
@Example:
<div id="info">
<iframe id="myEditor" ></iframe>
</div>
<script type="text/javascript">

sb.dom.onReady({
	id : '#myEditor',
	onReady : function(){
		myEditor1 = new sb.widget.editor('#myEditor', function(){
			this.dataIn('hello world');
		});
	}
});

</script>
*/

sb.widget.editor = function(id, onload){
	
	this.box = sb.$(id);
	this.box.className ='editor';
		
	if(!sb.browser.agent.match(/[ie|ff|sf]/i)){
		throw('You need Firefox, Safari or IE to use the editor');

		var textarea = this.createTextArea();
		textarea.replace(id);
		textarea.setContent = this.setContent;
		textarea.getContent = this.getContent;
		textarea.id = this.box.id;
		return textarea;
		
	}
	
	this.box.frameBorder=0; //ie bug not working
	this.box.style.backgroundColor='white'; //ff needs bgcolor
	
	this.document = this.getReference(this.box.id);
	
	var toolbar = this.makeToolBar();
	
	this.editor = {
		
		numRules : 1,
		keyup : [],
		document :this.document,
		id : this.box.id,
		box : this.box,
		iframe : document.getElementById(this.box.id).contentWindow,
		dataOut : this.dataOut,
		dataIn : this.dataIn,
		basicOnly : 0,
		textarea :  this.createTextArea(),
		//textarea :  this.createTextArea(),
		toolbar : toolbar,
		getSelection : function(){
			var selection ='';
			if(this.document.selection){
				var range = this.document.selection.createRange();
				 selection= range.text;
			} else if(this.document.getSelection){
				selection =  this.document.getSelection();
			} 
			return selection;
		},
		addTags : function(beginTag, endTag){
			var selection;
			if(this.document.selection){
				var range = this.document.selection.createRange();
				 selection= range.text;
				 range.pasteHTML(beginTag+selection+endTag);
			} else if(this.document.getSelection){
				selection = this.document.getSelection();
				this.document.execCommand('inserthtml', false, beginTag+selection+endTag);
			} 
		},
		replaceSelection : function(newText){
			var selection;
			if(this.document.selection){
				var range = this.document.selection.createRange();
				 selection= range.text;
				 range.pasteHTML(newText);
			} else if(this.document.getSelection){
				selection = this.document.getSelection();
				this.document.execCommand('inserthtml', false, newText);
			} 
		}
		
	};
	
	var editor = this.editor;
	this.makeEditable();

	//set up the stylesheets for the editor and import any global ones defined in sb.widget.editor.styleSheets array
	var styleSheets = '@import "/css/sb_editor.css";';
	sb.widget.editor.styleSheets.forEach(function(v){
		styleSheets +="\n"+'@import "'+v+'";';
	});
	
	this.editor.document.open();
	this.editor.document.write('<html><head><style type="text/css">'+styleSheets+'</style></head><body></body></html>');
	this.editor.document.close();
	
	sb.events.add(this.document, 'keyup', function(){
	
		editor.keyup.forEach(function(v){
			if(typeof v=='function'){
				v.call(editor);
			}
		});
		 sb.cookies.set(editor.id, this.body.innerHTML);
	});
	
	/*
	var t=this;
	sb.events.add(this.document, 'keydown', function(e){
	
		e = e || editor.iframe.event;
		
		if(typeof t.onkeydown == 'function'){
			t.onkeydown.call(editor, e);
		}

	});
	*/
	this.editor.textarea.appendBefore(this.box);
	
	if(typeof onload== 'function'){
		onload.call(this);
	}
};

sb.widget.editor.styleSheets = [];
sb.widget.editor.plugins =[];

/**
@Name: sb.widget.editor.buttons
@Description: These booleen values toggle the editor buttons on or off on a global scope e.g. all editors on the page
*/
sb.widget.editor.buttons = {
	bold : 1,
	italic : 1,
	underline : 1,
	superscript : 1,
	subscript : 1,
	strikethrough : 1,
	horizontalRule : 1,
	link : 1,
	unorderedList : 1,
	orderedList : 1,
	redo : 1,
	undo : 1,
	clearFormat : 1,
	viewSource : 1,
	restore :1
};

/**
@Name: sb.widget.editor.prototype
@Description: THese are the properties associated with every sb.widget.editor instance
*/
sb.widget.editor.prototype = {
	typeOf : function(){
		return 'sb.widget.editor';
	},
	
	basicOnly : function(){
		this.editor.basicOnly =1;	
	},
	
	dataIn : function(str){
		
		if(typeof this.value !='undefined'){
			this.value = str;
		} else if(typeof this.document.body !='undefined'){
			var t=this;
			//changed this part to make it pass validation may have caused problem
			var interval = window.setInterval(function(){
				try{
					t.document.body.innerHTML = str;
				} catch(e){
					return;
				}
				window.clearInterval(interval);
			}, 10);
		
			
		}
	},
	
	dataOut : function(){
		var str ='';
		if(typeof this.value !='undefined'){
			str= this.value;
		} else if(typeof this.document.body.innerHTML !='undefined'){
		
			 str= this.document.body.innerHTML;
		}
		
		return str.parseHTML(this.basicOnly);
	},
	
	getReference : function(id){
		if (document.getElementById(id).contentDocument){
			return document.getElementById(id).contentDocument;
		} else {
			return document.frames[id].document;
		}
	},
	
	exec : function(command, value, bool){
		bool = bool || false;
		value = value || null;
		this.execCommand(command, false, null);	
	},
	
	makeEditable : function(id){
		
		if(sb.browser.agent.match(/f/i)){
			this.document.designMode='on';
			this.document.execCommand("useCSS", false, false);
			this.document.execCommand("styleWithCSS", false, false); 
			
		} else {
			
			this.document.designMode="On";
			
		}
		 
	},
	
	createTextArea : function(){
		
		var textarea = new sb.element({
		tag : 'textarea',
		className : 'editor',
		styles : {
			border : '0px',
			display: 'none'
		}
		});

		
		return textarea;
	},
	
	processCommand : function(command){
		switch(command){
			
			case 'removeformat':
			try{
				this.editor.replaceSelection(this.editor.getSelection().stripHTML());
			} catch(e){}
				break;
				
			default:
				break;
		}
	
		return command;
	},
	
	makeButton : function(title, name, command, val){
		var btn = new sb.element({
			tag: 'button',
			innerHTML : name,
			title : title
		});
		
		btn.styles({
			cursor :'pointer'
		});
		
		btn.editor = this;
		btn.command = command;
		btn.val = val;
		return btn;
	
	},
	
	addButton : function(title, name, command, val){
		
		return this.toolbar.insertBefore(this.makeButton(title, name, command, val), this.toolbar.lastChild);
		
	},
	
	makeToolBar : function(){
		
		this.toolbar = new sb.element({
			tag :'toolbar',
			styles : {
				display : 'block'
			}, 
			events : {
				click : function(e){
					
					var target = e.target;
					if(target.nodeName =='BUTTON'){
						
						if(typeof target.command == 'string' && t.box.style.display === ''){
							//check here for command before exec
							var command = target.editor.processCommand(target.command);
							
							if(t.document.queryCommandEnabled(command)){
									t.document.execCommand(command, false, target.val);
							}
						} else if(typeof target.command == 'function'){
							
							target.command.call(t.editor);
						} else if(t.box.style.display=='none'){
							var beginTag='', endTag='';
							switch(target.command){
								case 'bold':
									beginTag = '<b>';
									endTag = '</b>';
									break;
								case 'italic':
									beginTag = '<i>';
									endTag = '</i>';
									break;
								case 'underline':
									beginTag = '<u>';
									endTag = '</u>';
									break;
								case 'subscript':
									beginTag = '<sub>';
									endTag = '</sub>';
									break;
								case 'superscript':
									beginTag = '<sup>';
									endTag = '</sup>';
									break;
								case 'strikethrough':
									beginTag = '<strikethrough>';
									endTag = '</strikethrough>';
									break;
								case 'inserthorizontalrule':
									beginTag = '<hr />';
									break;
								
							}
							if(beginTag !== ''){
							
								sb.forms.textarea.addTags(t.editor.textarea, beginTag, endTag);
							}
						}
					}
					
				}
			}
		});
			
		var t=this;

		this.toolbar.appendBefore(this.box);
		this.toolbar.appendChild(new sb.element({tag : 'span'}));
		
		
		//set cookies/flash storage backup button if data exists

		if(sb.widget.editor.buttons.restore){
			if(sb.sharedObject.recall(this.box.id)){
				this.addButton('restore', 'restore', function(){
					this.document.body.innerHTML = sb.sharedObject.recall(this.id);
				}).style.backgroundColor='red';
			}
		}
		
		if(sb.widget.editor.buttons.bold){ this.addButton('bold', '<b>b</b>', 'bold'); }
		
		if(sb.widget.editor.buttons.italic){this.addButton('italic', '<i>i</i>', 'italic');}
		
		if(sb.widget.editor.buttons.underline){this.addButton('underline', '<u>u</u>', 'underline');}
		
		if(sb.widget.editor.buttons.superscript){this.addButton('superscript', '<sup>sup</sup>', 'superscript');}
		
		if(sb.widget.editor.buttons.subscript){this.addButton('subscript', '<sub>sub</sub>', 'subscript');}
		
		if(sb.widget.editor.buttons.strikethrough){this.addButton('strikethrough', '<strike>strike</strike>', 'strikethrough');}
		
		if(sb.widget.editor.buttons.horizontalRule){this.addButton('horizontal rule', '<u>hr</u>', 'inserthorizontalrule');}
		
		if(sb.widget.editor.buttons.link){
			this.addButton('create link on selected text', 'link', function(){
				var url = prompt('What address would you like to link this text to?', 'http://');
				this.document.execCommand('createlink', false, url);
			});
		}
		
		if(sb.widget.editor.buttons.unorderedList){this.addButton('make list', 'ulist', 'insertunorderedlist');}
			
		if(sb.widget.editor.buttons.orderedList){this.addButton('make list','list', 'insertorderedlist');}
			
		
		if(sb.widget.editor.buttons.undo){this.addButton('undo last edit', 'undo', 'undo');}
		if(sb.widget.editor.buttons.redo){this.addButton('redo last edit', 'redo', 'redo');}
		
		this.addButton('clear formatting on selected text', 'clear', 'removeformat');

		this.addButton('view html source', 'view source', function(){
				
			this.textarea.styles({
				height : this.box.offsetHeight+'px',
				width :  this.box.offsetWidth+'px'
			});
			
			if(this.box.style.display =='none'){
				this.document.body.innerHTML = this.textarea.value.parseHTML(this.basicOnly);
				this.textarea.style.display ='none';
				this.box.style.display ='';
			} else {
				this.textarea.value = this.document.body.innerHTML.parseHTML(this.basicOnly);
				this.box.style.display ='none';
				this.textarea.style.display ='';
			}
		});
		
		var t=this;
		if(typeof sb.widget.editor.plugins !='undefined'){
			sb.widget.editor.plugins.forEach(function(v){
					var btn = t.addButton(v.title, v.name, v.handler);
					//apply styles if they exist
					if(typeof v.styles !='undefined'){
						btn.styles(v.styles);
					}
					if(typeof v.className !='undefined'){
						btn.className = v.className;
					}
			});
		}

		//create the plugin area
		this.toolbar.plugin = new sb.element({
			tag: 'plugin',
			state :0,
			styles : {
				width:'100%',
				display:'block'
			},
			clear : function(){
				this.innerHTML ='';
			}
		});
		
		
		this.toolbar.plugin.appendTo(this.toolbar);
		
		return this.toolbar;
	}
};

/**
@Name: sb.widget.editor.plugin
@Description: A plugin button maker for sb.editors, any plugins instantiated on the page are applied to every sb.widget.editor instance on the page.  Every plugin has three properties.
1. name: The name displayed on the button in the toolbar.  Can be html.
2. title: The title text that is displayed when hovering over the button
3. handler: The function to rn when the button is clicked.  The this is the editor.  Which has the following properties.
	a. this document - a reference to the editable document.  Can be used with this.document.body.innerHTML
	b. this.textarea - the textarea that is shown when the user is in view source mode
	c. this.getSelection - the current user text selection just before clicking the button.
	d. this.box - a reference to the iframe the document is in
	e. this.id - the id of the iframe that houses the editable document
	f
4. styles: A style object, whose properties are applied directly to the button after it is created
@Example:
var myTestPlugin = new sb.widget.editor.plugin({
	name : 'plugin',
	title : 'This is a demo plugin',
	handler : function(){
		alert('This does nothing but alerts plugin');
	},
	styles : {
		backgroundColor:'green',
		color: 'white'
	}
});
*/
sb.widget.editor.plugin = function(o){
	sb.objects.infuse(o, this);
	sb.widget.editor.plugins.push(this);
};

/**
@Name: sb.widget.editor.plugin
@Description: A plugin button maker for sb.editors, any plugins instantiated on the page are applied to every sb.widget.editor instance on the page.  Every plugin has three properties.
name: The name displayed on the button in the toolbar.  Can be html
title: the popo up title
handler: can either be an inline function that has reference to the editor with this or a richtext area exec command as a string e.g. 'justifyright', see the exmaples below
@Example:
var myTestPlugin = new sb.widget.editor.plugin({
	name : 'plugin',
	title : 'This is a demo plugin',
	handler : function(){
		alert('This does nothing but alerts plugin');
	}
});
*/
sb.widget.editor.plugin.prototype = {
	typeOf: function(){
		return 'sb.widget.editor plugin';
	},
	name : 'plugin',
	title : 'This is a demo plugin',
	handler : function(){
		alert('This does nothing but alerts plugin');
	}
};

/**
@Name: String.prototype.removeBlankHTML
@Description: Removes blank HTML tags
@Example:
var str = 'hello<b></b>world';
str = str.removeBlankHTML();
//str ='helloworld'
*/

String.prototype.removeBlankHTML=function(){
	return this.replace( /<(\w*)\s*[^\/>]*>\s*<\/\1>/g, '' );
};

/**
@Name: String.prototype.stripHTMLComments
@Description: Strips HTML comments from a string
@Example:
str = str.stripHTMLComments();
*/
String.prototype.stripHTMLComments=function(){
		return this.replace(/<!(?:--[\s\S]*?--\s*)?>\s*/g,'');
};

/**
@Name: String.prototype.stripWordHTML
@Description: Strips word HTML from a string. The following function is a slight variation of the word cleaner code posted by Weeezl (user @ InteractiveTools forums).
@Example:
str = str.stripWordHTML();
*/
String.prototype.stripWordHTML = function() {
    var str = this;
   
    if (str.indexOf('class="mso') >= 0 || str.indexOf('class=mso') >= 0) {

        // make one line
        str = str.replace(/\r\n/g, ' ').
            replace(/\n/g, ' ').
            replace(/\r/g, ' ').
            replace(/\&nbsp\;/g,' ');

        // keep tags, strip attributes
        str = str.replace(/ class=[^\s|>]*/gi,'').
            //replace(/<p [^>]*TEXT-ALIGN: justify[^>]*>/gi,'<p align="justify">').
            replace(/ style=\"[^>]*\"/gi,'').
            replace(/ align=[^\s|>]*/gi,'');

        //clean up tags
        str = str.replace(/<b [^>]*>/gi,'<b>').
            replace(/<i [^>]*>/gi,'<i>').
            replace(/<li [^>]*>/gi,'<li>').
            replace(/<ul [^>]*>/gi,'<ul>');

        // replace outdated tags
        str = str.replace(/<b>/gi,'<strong>').
            replace(/<\/b>/gi,'</strong>');

        // mozilla doesn't like <em> tags
        str = str.replace(/<em>/gi,'<i>').
            replace(/<\/em>/gi,'</i>');

        // kill unwanted tags
        str = str.replace(/<\?xml:[^>]*>/g, '').       // Word xml
            replace(/<\/?st1:[^>]*>/g,'').     // Word SmartTags
            replace(/<\/?[a-z]\:[^>]*>/g,'').  // All other funny Word non-HTML stuff
            replace(/<\/?font[^>]*>/gi,'').    // Disable if you want to keep font formatting
            replace(/<\/?span[^>]*>/gi,' ').
            replace(/<\/?div[^>]*>/gi,' ').
            replace(/<\/?pre[^>]*>/gi,' ').
            replace(/<\/?h[1-6][^>]*>/gi,' ');

        //remove empty tags
        //str = str.replace(/<strong><\/strong>/gi,'').
        //replace(/<i><\/i>/gi,'').
        //replace(/<P[^>]*><\/P>/gi,'');

        // nuke double tags
        var oldlen = str.length + 1;
        while(oldlen > str.length) {
            oldlen = str.length;
            // join us now and free the tags, we'll be free hackers, we'll be free... ;-)
            str = str.replace(/<([a-z][a-z]*)> *<\/\1>/gi,' ').
                replace(/<([a-z][a-z]*)> *<([a-z][^>]*)> *<\/\1>/gi,'<$2>');
        }
        str = str.replace(/<([a-z][a-z]*)><\1>/gi,'<$1>').
            replace(/<\/([a-z][a-z]*)><\/\1>/gi,'<\/$1>');

        // nuke double spaces
        str = str.replace(/  */gi,' ');
		
    }
       return str;
};

/**
@Name: String.prototype.parseHTML
@Description: Parses HTML to for sb.widget.editor dataOut and viewSource methods. 
@Param: boolean basicOnly Removes all non basic html is basicOnly =1
@Example:
str = str.parseHTML(1);
*/
String.prototype.parseHTML = function(basicOnly){
	
		var html = this;
		html = html.replace(/<(.*?)>/g, function(m){
			return m.toLowerCase();
		});
		
	    //remove word crap
	    html = html.stripWordHTML();
	    
	    //strip extra line returns?
	    html = html.replace(/\n+/g, "\n"); 
		html = html.replace(/class=([^"].*?)>/ig, 'class="$1">');
	   
	    
	    if(basicOnly==true){ 
	    	
			html = html.replace(/<br>/g, "<br />");
			html = html.replace(/<hr.*?>/g, "<hr />");
			html = html.replace(/<hr \/>/g, "\[hr /\]");
			html = html.replace(/<br \/>/g, "\[br /\]");
			html = html.replace(/<(\/?)strong>/g, "<$1b>");
			html = html.replace(/<(\/?)em>/g, "<$1i>");
		
			html = html.replace(/<(\/?)sub>/g, "\[$1sub\]");
			html = html.replace(/<(\/?)sup>/g, "\[$1sup\]");
			html = html.replace(/<(\/?)ol>/g, "\[$1ol\]");
			html = html.replace(/<(\/?)li>/g, "\[$1li\]");
			html = html.replace(/<(\/?)p>/g, "\[$1p\]");
			html = html.replace(/<(\/?)strikethrough>/g, "\[$1li\]");
			
			html = html.replace(/<(\/?)u>/g, "\[$1u\]");
			html = html.replace(/<(\/?)i>/g, "\[$1i\]");
			html = html.replace(/<(\/?)b>/g, "\[$1b\]");
			html = html.replace(/<(\/?)p>/g, "\[$1p\]");
			
			//strip all other html
			html = html.stripHTML();
			
			//return all html
			html = html.replace(/\[(\/?)p\]/g, "<$1p>");
			html = html.replace(/\[(\/?)u\]/g, "<$1u>");
			html = html.replace(/\[(\/?)i\]/g, "<$1i>");
			html = html.replace(/\[(\/?)b\]/g, "<$1b>");
			html = html.replace(/\[(\/?)p\]/g, "<$1p>");
			html = html.replace(/\[(\/?)sub\]/g, "<$1sub>");
			html = html.replace(/\[(\/?)sup\]/g, "<$1sup>");
			html = html.replace(/\[(\/?)ol\]/g, "<$1ol>");
			html = html.replace(/\[(\/?)li\]/g, "<$1li>");
			html = html.replace(/\[hr \/\]/g, "<hr />");
			html = html.replace(/\[br \/\]/g, "<br />");
			html = html.replace(/\[(\/?)strikethrough\]/g, "<$1strikethrough>");
			
	    }
		//remove blank html
		html = html.removeBlankHTML();
		html = html.stripHTMLComments();
	
		return html;
		
};