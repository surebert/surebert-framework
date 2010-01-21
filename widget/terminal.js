sb.widget.terminal = function(params){
	sb.objects.infuse(params, this);
	var self = this;
	var type = this.type || 'input';
	self.className = self.className || 'sb_terminal';
	this.textField = new sb.element({
		tag : type,
		type: 'text',
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
			
			keyup : function(e){

				var target = e.target;
				var data = [];

				switch(e.keyCode){
					case 13:
						if(self.type == 'input' || self.type == 'textarea' && e.shiftKey){
							self.process(e);
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

	if(type == 'textarea'){
		sb.include('forms.textarea');
		sb.include('forms.textarea.allowTabs');
		this.textField.evt('keydown', sb.forms.textarea.allowTabs);
	}

	this.stack = [];
	this.textField.appendToTop('body');
};

sb.widget.terminal.prototype = {
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