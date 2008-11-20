sb.include('forms.textarea');
sb.include('browser.removeSelection');
sb.include('sharedObject');
sb.include('String.prototype.isNumeric');

/*
Author: Paul Visco
Created: 10/05/03
Modified: 10/23/07 v2.0
Note: TextBling is used for formatting text in a text area
youtube ex. id: [youtube]Wq-eHv7OiBo[/youtube]
*/

/**
@Name: sb.forms.textarea.textBling
@Description: Allows tagging of selected text in textareas with custom tags and properties
@Param: element editBar A reference to a DOM element that hold the buttons which add the tags
Param: element editBox A reference to a textarea that the editing is occurring in
@Example: 
var myBling = new sb.forms.textarea.textBling('#editBar', '#editBox');
		
customTags = ["q, e.g. [q]here is some quoted text[/q] - adds quote bubble around text", "box, e.g.[box]text in a box[/box] - great for code chunks"];

myBling.custom(customTags);

myBling.basic();
*/

sb.forms.textarea.textBling = function(editBar, editBox){
	this.editBar = $(editBar);
	this.editBox = $(editBox);
	this.editBar.editBox = this.editBox;
	this.addEvents();
};

sb.forms.textarea.textBling.save = function(){
	sb.sharedObject.save(this.id, this.value);
};

sb.forms.textarea.textBling.restore = function(){
	var str = sb.sharedObject.load(this.id);
	if(str){this.value = str;}
	return this.value;
};

sb.forms.textarea.textBling.clearStorage = function(){
	sb.sharedObject.clear(this.id);
};

sb.forms.textarea.textBling.prototype = {
	
	renderStyles :1,
	defaultStyles : ["b", "i", "u", "caps", "hilite", "strike"],
	buttonCss :0,
	buttonSize :1,
	buttonColor :1,
	fetchBackup :1,
	
	checkStorage : function(){
		return sb.sharedObject.load(this.editBox.id);
	},
	
	clearStorage : function(){
		sb.sharedObject.clear(this.editBox.id);
	},
	
	addEvents : function(){
			
			this.editBox.event('keydown', sb.forms.textarea.textBling.save);
			this.editBox.event('onchange', sb.forms.textarea.textBling.save);
			
			this.editBar.event('mousedown', function(e){
				var target = sb.events.target(e);
				
				//for safari which uses the span as the target
				if(target.nodeName =='SPAN'){
					target=target.parentNode;
				}
				
				if(target.kind){
				
					switch(target.kind){
						case 'basic':
							sb.forms.textarea.addTags(this.editBox, '['+target.bling+']', '[/'+target.bling+']');
							break;
							
						case 'prompt':
							var myPrompt = window.prompt(target.question, "");
							if(myPrompt){
								if(myPrompt.isNumeric()){
									myPrompt +='px';
								}
								sb.forms.textarea.addTags(this.editBox, '['+target.bling+'='+myPrompt+']', '[/'+target.bling+']');
							}
							break;
							
						case 'restore':
							if(this.editBox.value !==''){
								if(!window.confirm('Restoring the editor to the last draft saved point will erase anything in your text editor, is that ok?')){
									return;
								}
							}
							sb.forms.textarea.textBling.restore.call(this.editBox);
							
							break;
					}
					
					if(typeof this.update == 'function'){
						this.update();
					}
				}
				
				//clear any selection of button text that occurred
				sb.browser.removeSelection;
				
			});
	},

	addButton : function(bling, title){
	
		var btn = new sb.element({
			tag : 'button',
			innerHTML : '<span class="tb_'+bling+'">'+bling+'</span>',
			bling : bling,
			title : title || '['+bling+']text[/'+bling+']',
			kind : 'basic'
		});
		//btn.setAttribute('kind', 'basic');
		
		btn.appendTo(this.editBar);
		
	},
	
	addPromptButton : function(bling, title, question){
	
		var btn = new sb.element({
			tag : 'button',
			innerHTML : '<span class="tb_'+bling+'">'+bling+'</span>',
			title : title,
			bling : bling,
			kind : 'prompt',
			question : question
		});
		
		btn.appendTo(this.editBar);
	},
	
	addRestoreButton : function(){
		var str = this.checkStorage();
		if(str){
			var btn = new sb.element({
				tag : 'button',
				innerHTML:'restore backup',
				styles : {
					backgroundColor:'red',
					color:'white'
				},
				kind:'restore'
			});
			
			btn.appendTo(this.editBar);
		}
	},
	
	basic : function(){
		
		for(var x=0;x<this.defaultStyles.length;x++){
			this.addButton(this.defaultStyles[x]);
		}
	
		if(this.buttonSize == 1){
			this.addPromptButton("size", "e.g. [size=12px]12 pixeltext[/size] - sets the font size - use 12px 1.2em large", "Enter the font-size in standard form e.g. 12px, 1em, large");
		}
		
		if(this.buttonColor == 1){
			this.addPromptButton("color", "e.g. [color=blue]blue text[/color] - sets the font color for the text in the tags", "Enter the color e.g. #FFFFFF or white.");
		}
		
		//add basic prompt buttons such as color, size, link, table, and css
		this.addPromptButton("link", "e.g. [link=http://www.artvoice.com]links the the address in the link tags[/link]", "Enter the URL address for the link.");
		
		if(this.buttonCss == 1){
			this.addPromptButton("css", "e.g. [css=background-color:blue]blue text[/css] - sets the font color for the text in the tags", "Enter the any valid CSS style data e.g. background-color:blue;border:2px dotted black;");
		}
	
	
	},
	
	custom : function(customTags){
		
		for(var x=0;x<customTags.length;x++){
			var bling = customTags[x];
			var title ='';
			
			if(bling.match(/,/)){
				var data = bling.split(',');
				bling = data[0];
				title = data[1];
			}
			
			this.addButton(bling, title);
		}
	}
};