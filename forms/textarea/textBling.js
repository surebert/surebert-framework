sb.include('forms.textarea');
sb.include('browser.removeSelection');

/**
@Name: sb.forms.textarea.textBling
@Created: 10-05-03 07-29-09
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
	this.editBar = sb.$(editBar);
	this.editBox = sb.$(editBox);
	this.editBar.editBox = this.editBox;
	this.addEvents();
};

sb.forms.textarea.textBling.prototype = {
	
	renderStyles :1,
	defaultStyles : ["b", "i", "u", "hilite", "strike"],
	buttonCss :0,
	buttonSize :1,
	buttonColor :1,
	addEvents : function(){
			
			this.editBar.evt('mousedown', function(e){
				var target = e.target;
				
				//for safari which uses the span as the target
				if(target.nodeName =='SPAN'){
					target=target.parentNode;
				}

                                if(typeof target.onPress == 'function'){
                                    target.onPress(e);
                                }

				if(target.kind){
				
					switch(target.kind){
						case 'basic':
							sb.forms.textarea.addTags(this.editBox, '['+target.bling+']', '[/'+target.bling+']');
							break;
							
						case 'prompt':
							var myPrompt = window.prompt(target.question, "");
							if(myPrompt){
								if(/^\d+?(\.\d+)?$/.test(myPrompt)){
									myPrompt +='px';
								}
								sb.forms.textarea.addTags(this.editBox, '['+target.bling+'='+myPrompt+']', '[/'+target.bling+']');
							}
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
		this.addPromptButton("link", "e.g. [link="+window.location.protocol+"://"+window.location.host+"]links the the address in the link tags[/link]", "Enter the URL address for the link.");
		
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