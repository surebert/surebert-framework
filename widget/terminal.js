sb.include('String.prototype.trim');

sb.widget.terminal = function(params){
	sb.objects.infuse(params, this);
	var self = this;
	var type = this.type || 'input';
	self.className = self.className || 'sb_terminal';
	
	this.create(type);
	this.stack = [];
	
};

sb.widget.terminal.prototype = {
	create : function(type){
		var self = this;
		type = type || 'input';
		if(this.textField){
			this.textField.remove();
			this.textField = null;
		}
		this.textField = new sb.element({
			tag : type,
			type: type,
			value : '',
			className : self.className,
			styles : {
				width : '100%',
				backgroundColor : 'black',
				color: 'lime',
				fontFamily : 'courier',
				border : 0,
				fontSize : '14px',
				padding : '5px'
			},
			events : {

				keydown : function(e){

					var target = e.target;
					var data = [];
					var value = this.value.trim();

					if(value == 'textarea' || value == 'input'){
						self.create(value);
						e.preventDefault();
						return true;
					}
					
					switch(e.keyCode){
						case 13:
							if(self.type == 'input' || self.type == 'textarea' && e.shiftKey){
								self.process(e);
								e.preventDefault();
							}

							break;

						case 27:
							target.value = '';
							target.select();

						case 38:
							var command = self.stack.pop();
							if(command){
								target.value = command;
							}

					}
				}

			}

		});

		this.textField.appendToTop('body');

		if(type == 'textarea'){
			sb.include('forms.textarea');
			sb.include('forms.textarea.allowTabs');
			this.textField.evt('keydown', sb.forms.textarea.allowTabs);
		}

		if(typeof self.onAfterCreate == 'function'){
			self.onAfterCreate();
		}
		
		this.textField.focus();
	},
	process : function(e){
		var self = this;
		var data = '';
		var value = e.target.value;
		self.stack.push(value);

		if(value.match(/^js:/)){
			eval(value.replace(/^js:/, ''));
			return;
		}

		var arr = value.split(' ');

		if(arr[0]){
			data = arr.slice(1);

		}
		var aj = new sb.ajax({
			url : '/terminal/'+arr[0],
			data : {
				arguments : data.join(' ')
			},
			onHeaders : function(status){
				if(status == 404){
					if(typeof(self.onError == 'function')){
						self.onError('Command not found');
					}

					target.value = '';
					target.select();

				}
			},
			onResponse : function(r){
				if(r !== '' && typeof(self.onResponse == 'function')){
					self.onResponse(r);
				} else if(typeof(self.onError == 'function')){
					self.onError('No Response');
				}

				target.value = '';
				target.select();
			}

		}).fetch();
	},
	typeOf : function(){
		return 'sb.terminal';
	}
};