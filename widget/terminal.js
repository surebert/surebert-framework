sb.include('String.prototype.trim');

sb.widget.terminal = function(params){
	sb.objects.infuse(params, this);
	var self = this;
	var type = this.type || 'input';
	self.className = self.className || 'sb_terminal';
	this.allowJS = this.allowJS || false;
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
				width : '95%',
				backgroundColor : 'black',
				color: 'lime',
				fontFamily : 'courier',
				border : 0,
				fontSize : '14px'
			},
			events : {
				keydown : function(e){

					var target = e.target;
					var data = [];
					var command = this.value.trim();
					if(command == 'server'){
						this.style.backgroundColor = 'orange';
						this.style.color = 'brown';
						self.sendToServer = true;
						self.clear();
						return true;
						
					}

					if(command == 'client'){
						this.style.backgroundColor = 'black';
						this.style.color = 'lime';
						self.sendToServer = false;
						self.clear();
						return true;

					}

					if(command == 'textarea' || command == 'input'){
						if(command == 'textarea'){

							sb.include('forms.textarea');
							sb.include('forms.textarea.allowTabs');
						}
						self.create(command);
						e.preventDefault();
						return true;
					}
					
					switch(e.keyCode){
						case 13:
							e.preventDefault();
							if(this.nodeName == 'INPUT' || this.nodeName == 'TEXTAREA' && e.shiftKey){

								self.stack.push(command);
								var s = sb.forms.textarea.getSelection(this);
								
								if(s.selected != ''){
									
									eval(s.selected);

								} else if(self.sendToServer){
									self.processServerside(command);
									
								} else {
									if(!self.processClientside(command)){
										eval(command);
										self.clear();
									}
								}
								
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
	clear : function(){
		this.textField.value = '';
	},
	processClientside : function(command){
		return false;
	},
	processServerside : function(command){
		var self = this;
		var data = '';
		
		var arr = command.split(' ');

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

					self.textField.value = '';
					self.textField.select();

				}
			},
			onResponse : function(r){
				if(this.ajax.getResponseHeader('Content-Type') != 'text/javascript'){
					if(r !== '' && typeof(self.onResponse == 'function')){
						self.onResponse(r);
					} else if(typeof(self.onError == 'function')){
						self.onError('No Response');
					}
				}
				self.textField.value = '';
				self.textField.select();
			}

		}).fetch();
	},
	typeOf : function(){
		return 'sb.terminal';
	}
};
