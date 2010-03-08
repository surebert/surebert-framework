/**
@Name: sb.widget.editor.plugins.searchPlugin
@Description: A plugins that adds search and replace to the widget
*/
var searchPlugin = new sb.widget.editor.plugin({
	
	name :'search',
	title : 'search and replace plugin by Paul Visco',
	handler : function(){
		var t=this;
		
		var selection =this.getSelection();
		
		if(this.toolbar.state==1){
			this.toolbar.plugin.clear();
			this.toolbar.plugin.state=0;
			return;
		} else {
			this.toolbar.plugin.state=1;
		}
		
		var lbl = new sb.element({
			tag: 'label',
			innerHTML : 'Search: '
		});
		
		var inp = new sb.element({
			tag: 'input',
			value : selection,
			stage : 1
		});
		
		var f,r,stage=1;
		
		inp.evt('keydown', function(e){
			if(e.keyCode ==13  ){
				switch(stage){
					case 1:
						f = this.value;
						this.value ='';
						lbl.innerHTML = 'Replace: ';
						this.focus();
						stage=2;
						break;
						
					case 2:
						if(t.textarea.style.display=='none'){
							t.document.body.innerHTML = t.document.body.innerHTML.replace(new RegExp(f, "ig"), this.value);
						} else {
							t.textarea.value = t.textarea.value.replace(new RegExp(f, "ig"), this.value);
						}
						
						lbl.remove();
						inp.remove();
						t.toolbar.plugin.state =0;
						break;	
				}
				
			}
			
		});
	
		this.toolbar.plugin.clear();
		lbl.appendTo(this.toolbar.plugin);
		inp.appendTo(this.toolbar.plugin);
		
		inp.focus();
		
	}
});

/**
@Name: sb.widget.editor.plugins.justifyLeft
@Description: A plugins that justifies selected text to the left
*/
var justifyLeft = new sb.widget.editor.plugin({
	
	name :'L',
	title : 'Left Justify Text',
	handler : 'justifyleft'
});

/**
@Name: sb.widget.editor.plugins.justifyCenter
@Description: A plugins that justifies selected text to the center
*/
var justifyCenter = new sb.widget.editor.plugin({
	
	name :'C',
	title : 'center Justify Text',
	handler : 'justifycenter'
});

/**
@Name: sb.widget.editor.plugins.justifyRight
@Description: A plugins that justifies selected text to the right
*/
var justifyRight = new sb.widget.editor.plugin({
	
	name :'R',
	title : 'Right Justify Text',
	handler : 'justifyright'
});

/**
@Name: sb.widget.editor.plugins.justifyFull
@Description: A plugins that justifies selected text full
*/
var justifyFull = new sb.widget.editor.plugin({
	
	name :'F',
	title : 'Full Justify Text',
	handler : 'justifyfull'
});


/**
@Name: sb.widget.editor.plugins.save
@Description: A plugins that allows you to save the html
*/
var save = new sb.widget.editor.plugin({
	
	name :'SAVE PLUGIN',
	title : 'Save the text',
	handler : function(){
		//do something with the data here
		alert(this.dataOut());
	}
});


/**
@Name: sb.widget.editor.plugins.stripToText
@Description: A plugins that allows you to strip html to text only

var save = new sb.widget.editor.plugin({
	
	name :'strip all text',
	title : 'Clear All Formatting',
	handler : function(){
		if(confirm('Are you sure you want to clear all HTML formatting')){
			this.document.body.innerHTML =this.document.body.innerHTML.stripHTML();
		}
		
	}
});*/