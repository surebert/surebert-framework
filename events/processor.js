/**
@Name: sb.events.processor
@Description: Handles events with framework.  events are added to any elements
by adding the following properties
sb_send_data="name=paul&age=34" - sends extra data with the request
sb_set_url="/admin/set_title" - sends an ajax request to the url specified
sb_confirm="Some message" - when event fires, a confirm is popped up to let user confirm or cancel
sb_prompt="Are you sure type SURE|SURE" - when event fires a prompt is given where user must type word after pipe to continue or cancel
sb_method="get" - determines the HTTP method used for the request, default is post
sb_serialize="#someId" - serializes all of the named children in the element and sends their values with the request

sb_editable="1" determines if textbox or input should be editable text
sb_get_url="/admin/get_title" - grabs data from url to populate editor on load
*/
sb.events.processor = {
	/**
	@Name: sb.events.processor._handleSend
	@Description: Used internally to manage sending data
	*/
	_handleSend : function(url, data, target){

		target.processing = 1;
		target.success = function(){
			this.getParent().flashBg('lime');
		};
		target.fail = function(){
			this.getParent().flashBg('red');
		};
		var aj = new sb.ajax({
			target : target,
			url : url,
			method : target.attr('sb_method') || 'post',
			data : data,
			onResponse : function(html){
				target.processing = 0;
				if(target.editor){
					target.editor.setHTML(html);
				}
				
				if(typeof sb.events.processor.onAfterResponse == 'function' && target){
					sb.events.processor.onAfterResponse(target, html);
				}
			}
		}).fetch();
	},
	/**
	@Name: sb.events.processor._handlePrompt
	@Description: Used internally to manage prompts
	*/
	_handlePrompt : function(e){
		var target = e.target;
		var sb_prompt = target.attr('sb_prompt');
		if(sb_prompt){
			if(typeof sb.events.processor.onBeforePrompt == 'function'){
				if(sb.events.processor.onBeforePrompt(e) === false){
					return false;
				};
			}
			
			var p = sb_prompt.split('|');
			var confirmed = prompt(p[0], '') != p[1];
			if(typeof sb.events.processor.onAfterPrompt == 'function'){
				sb.events.processor.onAfterPrompt(e, confirmed);
			}
			if(!confirmed){
				return false;
			}
		}
		return true;
	},
	/**
	@Name: sb.events.processor._handleConfirm
	@Description: Used internally to manage confirms
	*/
	_handleConfirm : function(e){
		var target = e.target;
		var sb_confirm = target.attr('sb_confirm');
		if(sb_confirm){
			if(typeof sb.events.processor.onBeforeConfirm == 'function'){
				if(sb.events.processor.onBeforeConfirm(e) === false){
					return false;
				};
			}
			
			var confirmed = confirm(sb_confirm);
			if(typeof sb.events.processor.onAfterConfirm == 'function'){
				sb.events.processor.onAfterConfirm(e, confirmed);
			}
			
			if(!confirmed){
				return false;
			}
		 
		}
		return true;
	},
	/**
	@Name: sb.events.processor._distillData
	@Description: Used internally to grab data from target element
	*/
	_distillData : function(e){
		var target = e.target;
		var data = {};
		var sb_send_data = target.attr('sb_send_data');
		if(sb_send_data){
			var d = sb_send_data.split('&');

			d.forEach(function(v){
				var f = v.split('=');
				data[f[0]] = f[1];
			});
		}
		
		var sb_serialize = target.attr('sb_serialize');
		if(sb_serialize){
			data = $(sb_serialize).serializeNamedChildren();
		}
		
		if(target.nodeName == 'INPUT' && target.type == 'checkbox'){
			var checked = target.checked ? 1: 0;
			if(typeof data == 'string'){
				data +='checked='+checked;
			} else {
				data.checked = checked;
			}
		}
			
		return data;
	},
	/**
	@Name: sb.events.processor._handleEditor
	@Description: Used internally to create editor
	*/
	_handleEditor : function(e, data){
		var self = this;
		var target = e.target;
		var sb_get_url = target.attr('sb_get_url');
		var sb_set_url = target.attr('sb_set_url');
		var sb_editable = target.attr('sb_editable');
		var type = sb_editable === "1" ? 'input' : 'textarea';
		var sb_maxlength = target.getAttribute('sb_maxlength');
		if(!sb_maxlength){
			sb_maxlength = type == 'input' ? 55 : 50000;
		}
		
		target.editor = new sb.forms.editable.field({
			type : type,
			attributes : {
				maxlength : sb_maxlength,
				size : 30
			},
			editableElement : target,
			onBeforeEdit : function(){
				var self = this;
				if(sb_get_url){
					var aj = new sb.ajax({
						url : sb_get_url,
						data : data,
						onResponse : function(r){
							self.setValue(r);
						}
					}).fetch();
				} else {
					this.setValue(target.innerHTML);
				}

			},
			onSave : function(value){
				if(sb_set_url){
					data.value = value;
					self._handleSend(sb_set_url, data, target);

				}
			}

		});

		target.editor.edit();
		if(typeof this.onAfterEditorCreated == 'function'){
			this.onAfterEditorCreated(e);
		}
	},
	/**
	@Name: sb.events.processor._handleEvent
	@Description: Used internally to handle events
	*/
	_handleEvent : function(e){
		if(e.which && e.which != 1){
			return false;
		}
		
		var target = e.target;
		
		var sb_set_url = target.attr('sb_set_url');
		
		if(!sb_set_url){
			return false;
		}
		
		if(target.processing){
			return false;
		}
		
		if(typeof sb.events.processor.onBeforeEvent == 'function'){
			if(sb.events.processor.onBeforeEvent(e) === false){
				return false;
			};
		}
		
		if(this._handleConfirm(e) === false){
			return false;
		}

		if(this._handlePrompt(e) === false){
			return false;
		}
		
		var data = this._distillData(e);
		var sb_editable = target.attr('sb_editable');
		if(!sb_editable){
			this._handleSend(sb_set_url, data, target);
		} else if(sb_editable && e.type == 'dblclick'){
			this._handleEditor(e, data);
		}
		
		return true;
	},
	/**
	@Name: sb.events.processor.onBeforeEvent
	@Description: Fires before event is processed.  If it returns false then event
	is canceled
	*/
	onBeforeEvent : function(e){},
	/**
	@Name: sb.events.processor.onBeforePrompt
	@Description: Fires before prompt is shown.  Allows you to do stuff like
	highlight a row in red before delete 
	@Param: event e The event itself
	*/
	onBeforePrompt : function(e){},
   /**
	@Name: sb.events.processor.onBeforeConfirm
	@Description: Fires before confirm is shown.  Allows you to do stuff like
	highlight a row in red before delete 
	@Param: event e The event itself
	*/
	onBeforeConfirm : function(e){},
   /**
	@Name: sb.events.processor.onAfterPrompt
	@Description: Fires after prompt is confirmed or cancelled
	@Param: event e The event itself
	@Param: boolean confirmed If the prompt was canceled or not
	*/
	onAfterPrompt : function(e, confirmed){},
	/**
	@Name: sb.events.processor.onAfterConfirm
	@Description: Fires after confirm is confirmed or cancelled
	@Param: event e The event itself
	@Param: boolean confirmed If the confirm was canceled or not
	*/
	onAfterConfirm : function(e, confirmed){},
	/**
	@Name: sb.events.processor.onAfterResponse
	@Description: Fires after data comes back from sb_set_url
	*/
	onAfterResponse : function(target, response){},
	/**
	@Name: sb.events.processor.onAfterEditorCreated
	@Description: Fires after inline editor is displayed
	*/
	onAfterEditorCreated : function(e){},
	/**
	@Name: sb.events.processor.init
	@Description: sets up event processor, is called by default upon load of js
	*/
	init : function(){
		var self = this;
		sb.events.add(document, 'click', function(e){
			self._handleEvent(e);
		});
		sb.events.add(document, 'dblclick', function(e){
			self._handleEvent(e);
		});
	}
	
};

sb.events.processor.init();