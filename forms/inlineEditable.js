sb.forms.inlineEditable = {};
sb.include('Element.prototype.isWithin');
/**
@Name: sb.forms.inlineEditable.textarea
@Description: Creates a click to edit text block
@Param: Object
onBeforeEdit function Fires before editing begins,  Can be used to load raw text wihtout HTML from server
onSave function Fires after save, send back to server, stop editing
className string the classname for the widget, defaults to

@Example:
//use the following in a dblclick event for the text you want to make editable
//I assign it to the target so it only makes an editor the first time its clicked

if(!e.target.editor){
	e.target.editor = new sb.forms.inlineEditable.textarea({
		onBeforeEdit : someFunction,
		onSave : someOtherFunction

	});
}

target.editor.edit(target);

//example CSS
.sb_inlineEditable textarea{
	cursor:text;
	display:block;
	width:97%;
	height:100px;
	overflow:auto;
	font-family:tahoma;
	font-size:1.1em;
}

.sb_inlineEditable editbar{
	text-align:right;
}

.sb_inlineEditable button{
	background-color:#d88713;
	color:#7c4e0d;
}

.sb_inlineEditable button:hover{
	background-color:#e2b370;
}

*/
sb.forms.inlineEditable.textarea = function(params){
	sb.objects.infuse(params, this);
	this.className = params.className || 'sb_inlineEditable';

};

sb.forms.inlineEditable.textarea.prototype = {

	/**
	@Name: sb.forms.inlineEditable.textarea.onBeforeEdit
	@Description: Fires before editing begins.  Can be used to load raw data with ajax
	to fill the textarea with.  Reference the textarea with this.textarea.  The default is
	to use the innerHTML of the area being edited
	@Example:
	editor.onBeforeEdit = function(){
		this.setValue('loading...');
		var editor = this;

		var aj = new sb.ajax({
			url : '/url/rawtext',
			data : {
				doc_id : doc_id
			},
			onResponse : function(raw_desc){
				editor.setValue(raw_desc);
			}
		}).fetch();
	};
	*/
	onBeforeEdit : function(){
		this.setValue(this.node.innerHTML);
	},

	/**
	@Name: sb.forms.inlineEditable.textarea.onSave
	@Description: Passes the value of the textarea for you to save back with ajax
	@Param string save The value of the textarea when save if fired
	editor.onSave = function(){
		if(value != 'loading...'){
			var editor = this;
			var aj = new sb.ajax({
				url : '/url/save',
				data : {
					doc_id : doc_id,
					value : value
				},
				onResponse : function(html){
					editor.setHTML(html);
				}
			}).fetch();
		}
	};

	*/
	onSave : function(value){},
	
	/**
	@Name: sb.forms.inlineEditable.textarea.onButtonPress
	@Description: Fires when a button is pressed other than save or cancel
	@Param event e The press event
	@Example:
	editor.onButtonPress = function(e){
		var button = e.target;
	};
	*/
	onButtonPress : function(e){},

	/**
	@Name: sb.forms.inlineEditable.textarea.setValue
	@Description: Sets the value of the textarea, use in onBeforeEdit after loading raw text from ajax
	@Example:
	editor.setValue('text to edit');
	*/
	setValue : function(value){
		this.textarea.value = value;
		this.focus();
	},

	/**
	@Name: sb.forms.inlineEditable.textarea.setHTML
	@Description: Sets the html of the element being edited, use in onSave after saving
	@Example:
	editor.setHTML('<p>text that was edited</p>');
	*/
	setHTML : function(html){
		this.element.innerHTML = html;
		this.editStop();
	},

	/**
	@Name: sb.forms.inlineEditable.textarea.edit
	@Description: Put the editor in edit mode
	@Param: element el the element to edit, generally the target from the dblclick
	@Example:
	editor.edit(target);
	*/
	edit : function(el){
		if(!this.editor){
			this.create();
		}
		this.editor.title = 'shortcuts: esc to cancel, ctrl+s to save';
		
		this.element = $(el);
		this.editor.replace(this.element);
		
		if(typeof this.onBeforeEdit == 'function'){
			this.onBeforeEdit.call(this);
		}
	},

	/**
	@Name: sb.forms.inlineEditable.textarea.editStop
	@Description: Exit edit mode
	@Example:
	editor.editStop();
	*/
	editStop : function(){
		this.element.replace(this.editor);
		
		this._origValue = '';
	},

	/**
	@Name: sb.forms.inlineEditable.textarea.focus
	@Description: Focuses on text area and puts cursor at top left. automatically fires after setValue
	@Example:
	editor.focus();
	*/
	focus : function(){
		var ta = this.textarea;
		var range;
		if (this.textarea.setSelectionRange) {
			this.textarea.setSelectionRange(0, 0);
		} else if(this.textarea.createTextRange){
			range = this.textarea.createTextRange();
			range.collapse(true);
			range.moveStart("character", 0);
			range.moveEnd("character", 0 - 0);
			range.select();
		}
		this.textarea.focus();

	},

	/**
	@Name: sb.forms.inlineEditable.textarea.addButton
	@Description: Adds a button to the editBar
	@Param string str The name of the button to add
	@Example:
	editor.addButton('email');
	*/
	addButton : function(str){
		this.editBar.innerHTML = '<button>'+str+'</button>'+this.editBar.innerHTML;

	},

	/**
	@Name: sb.forms.inlineEditable.textarea.isNotEdited
	@Description: Determines if field is edited or not
	@Example:
	if(editor.isNotEdited()){}
	*/
	isNotEdited : function(){
		return !this._origValue || this._origValue == this.textarea.value;
	},

	/**
	@Name: sb.forms.inlineEditable.textarea.editStop
	@Description: Used internally Creates editor
	*/
	create : function(){
		var self = this;
		if(!this.editor){
			this.editor = new sb.element({
				tag : 'div',
				className : self.className
			});

			this.textarea = new sb.element({
				tag : 'textarea',
				value : this.value,
				className : this.className,
				events : {
					keydown : function(e){

						if(!self._origValue){
							self._origValue = self.textarea.value;
						}

						if(e.keyCode == 9 && self.isNotEdited()){
							self.editStop();
						} else if(e.keyCode == 27){
							self.editStop();
						} else if((e.ctrlKey || e.metaKey) && e.keyCode == 83){
							e.stopPropagation();
							e.preventDefault();
							self.onSave.call(self, self.textarea.value);
							self.editStop();
						}
					},
					blur : function(e){
						if(self.isNotEdited()){
							self.editStop();
						}

					}
				}
			});

			this.textarea.appendTo(this.editor);

			this.editBar = new sb.element({
				tag : 'editbar',
				styles : {
					display : 'block'
				},
				innerHTML : '<button>cancel</button><button>save</button>',
				events : {
					mousedown : function(e){
						var target = e.target;
						e.preventDefault();
						e.stopPropagation();
						if(target.nodeName == 'BUTTON'){
							
							switch(target.innerHTML){
								case 'save':
									self.onSave(self.textarea.value);
									break;

								case 'cancel':
									self.editStop();
									break;

								default:
									self.onButtonPress(e);
							}
						}
						
						return true;

					}
				}
			});

			this.editBar.appendTo(this.editor);
		}
	}
};