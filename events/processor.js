/**
@Name: sb.events.processor
@Description: Handles events with framework
*/
sb.events.processor = {
	
	send : function(url, data, target){
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
			}
		}).fetch();
	},
	handleEvent : function(e){
		
		if(e.which && e.which != 1){
			return;
		}
		var target = e.target;
		if(target.processing){
			return;
		}
		
		var self = this;
		var sb_set_url = target.attr('sb_set_url');
		if(!sb_set_url){
			return;
		}	

		var sb_confirm = target.attr('sb_confirm');
		if(sb_confirm && !confirm(sb_confirm)){
			return false;
		}

		var sb_prompt = target.attr('sb_prompt');
		if(sb_prompt){
			var p = sb_prompt.split('|');
			 if(prompt(p[0], '') != p[1]){
				 return false;
			 }
		}
			
		var sb_editable = target.attr('sb_editable');
		var sb_send_data = target.attr('sb_send_data');
						
		var data = {};
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
		
		if(!sb_editable){
			if(target.nodeName == 'INPUT' && target.type == 'checkbox'){
				data.checked = target.checked ? 1: 0;
			}
			
			self.send(sb_set_url, data, target);
		} else if(e.type == 'dblclick'){
			
			var sb_get_url = target.attr('sb_get_url');
			
			target.editor = new sb.forms.editable.field({
				type : 'input',
				attributes : {
					maxlength : 55,
					size : 30
				},
				editableElement : target,
				onBeforeEdit : function(){
					var self = this;
					this.setValue(target.innerHTML);
				},
				onSave : function(value){
					if(sb_set_url){
						data.value = value;
						self.send(sb_set_url, data, target);
						
					}
				}

			});

			target.editor.edit();
			console.log('f');
		}
	},
	
	init : function(){
		var self = this;
		sb.events.add(document, 'click', function(e){self.handleEvent(e);});

		sb.events.add(document, 'dblclick', function(e){self.handleEvent(e);});
	}
	
}
sb.events.processor.init();