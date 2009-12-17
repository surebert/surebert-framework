/**
@Name: sb.widget.textarea
@Description: Creates a click to edit text block
@Param: Object
onBeforeEdit function Fires before editing begins,  Can be used to load raw text wihtout HTML from server
onSave function Fires after save, send back to server, stop editing
className string the classname for the widget, defaults to
target node The node to replace with a textarea
value string The default value to fill the textarea with

@Example:
//use this in a doubleclick event for the text you want to make editable
var target = e.target;

//var document_id = 'something from target';

if(!target.editor){
	target.editor = new sb.widget.textarea.editable({
		value : 'loading...',
		target : target,
		onBeforeEdit : function(){
			var self = this;
			var aj = new sb.ajax({
				url : '/admin/document_description_get_raw',
				data : {
					document_id : document_id
				},
				onResponse : function(raw_desc){
					self.textarea.value = raw_desc;
				}
			}).fetch();
		},
		onSave : function(value){
			var self = this;
			var aj = new sb.ajax({
				url : '/admin/document_description_update',
				data : {
					document_id : document_id,
					desc : value
				},
				onResponse : function(r){
					target.innerHTML = r;

				}
			}).fetch();
		}

	});
}

target.editor.edit();


.sb_widget_textarea textarea{
	cursor:text;
	display:block;
	width:97%;
	height:100px;
	overflow:auto;
	font-family:tahoma;
	font-size:1.1em;
}

.sb_widget_textarea editbar{
	text-align:right;
}

.sb_widget_textarea button{
	background-color:#d88713;
	color:#7c4e0d;
}

.sb_widget_textarea button:hover{
	background-color:#e2b370;
}

*/
sb.widget.textarea = function(params){

	this.onBeforeEdit = params.onBeforeEdit || this.onBeforeEdit;
	this.onSave = params.onSave || this.onSave;
	this.className = params.className || 'sb_widget_textarea_editable';
	this.target = $(params.target) || '';
	this.value = params.value || '';

	this.create();
	this.textarea.title = params.title || '';

};

sb.widget.textarea.prototype = {
	onBeforeEdit : function(){},
	onSave : function(){},

	edit : function(){

		this.editor.title = 'esc to exit';
		if(typeof this.onBeforeEdit == 'function'){
			this.onBeforeEdit.call(this);
		}
		this.editor.target = this.target;

		this.editor.replace(this.target);
		var textarea = this.textarea;
		window.setTimeout(function(){
			textarea.focus();
		}, 100);

	},

	edit_stop : function(){
		this.target.replace(this.editor);
	},
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
					keypress : function(e){
						if(e.keyCode == 27){
							self.edit_stop();
						}
					},
					blur : function(){
						self.onSave.call(self, self.textarea.value);
						self.edit_stop();
					}
				}
			});

			this.textarea.appendTo(this.editor);

			this.editBar = new sb.element({
				tag : 'editbar',
				styles : {
					display : 'block'
				},
				innerHTML : '<button>cancel</button> <button>save</button>',
				events : {
					mousedown : function(e){
						var target = e.target;
						if(target.innerHTML == 'save'){
							self.onSave.call(self, self.textarea.value);
						}
						self.edit_stop();

					}
				}
			});

			this.editBar.appendTo(this.editor);
		}


	}
};