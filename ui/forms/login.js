/**
@Name: sb.widget.editor
@Author: Paul Visco
@Description: Creates a login form that uses ajax and passes the password back in md5 format from the client
@Param: object params
params.url string The URL to hit
params.onSuccess function The callback that fires the url returns 1 or true
params.onFailure function The callback that fires when the url return 0 or false
params.onUrlNotFound function The callback that fires if the URL returns 404

@Example:
sb.include('ui.forms.login');

board.login = new sb.ui.forms.login({
	parentNode : '#login',
	url : '/user/login',
	onSuccess : function(){
		//do something
	},
	onFailure : function(){
		//do something
	}
});

*/
sb.ui.forms.login = function(params){

	var self = this;
	this.url = params.url;
	this.onSuccess = params.onSuccess || this.onSuccess;
	this.onFailure = params.onFailure || this.onFailure;
	this.onUrlNotFound = params.onUrlNotFound || function(){};
	this.labelUname = params.labelUname || 'uname';
	this.labelPass = params.labelPass || 'pass';
	this.parentNode = $(params.parentNode);

	if(this.form){
		return this.form;
	} else {
		this.createForm();
	}
};

sb.ui.forms.login.prototype = {

	createForm : function(){
		var self = this;
		
		this.form = new sb.element({
			tag : 'form',
			events : {
				click : function(e){
					var target = e.target;
					if(target.nodeName == 'INPUT' && target.type == 'submit'){
						e.preventDefault();
						self.onSubmit(e);
					}
				}
			}
		});

		
		this.form.innerHTML = '<label>'+this.labelUname+': </label><input autocomplete="off" type="text" name="uname" value="" /> <label>'+this.labelPass+': </label><input type="password" name="pass" value="" autocomplete="off" /> <input type="submit" style="cursor:pointer;" id="sb_widget_login_form_submit" value="login" /> <span id="sb_widget_login_feedback"></span>';
		
		if(this.parentNode){
			this.parentNode.innerHTML = '';
			this.form.appendTo(this.parentNode);
			this.submitBtn = $('#sb_widget_login_form_submit');

			this.submitBtn.waiting = function(){
				this.value = 'checking...';
				this.disabled = true;
				this.style.cursor = 'wait';
			};

			this.submitBtn.reset = function(){
				this.value = 'login';
				this.disabled = false;
				this.style.cursor = '';
			};

		}
	},
	showMessage : function(message, type){

		if(!this.message){
			this.message = $('#sb_widget_login_feedback');
		}

		var colors = {
			bg : type == 'error' ? 'pink' : 'lime',
			fg : type == 'error' ? 'red' : 'green'
		};

		this.message.innerHTML = '<span style="padding:5px">'+message+'</span>';

		this.message.style.backgroundColor = colors.bg;
		this.message.style.color = colors.fg;

		this.form.$('input').forEach(function(v){
			if(v.type == 'text' || v.type == 'password'){
				v.style.backgroundColor = colors.bg;
				v.style.color = colors.fg;
				window.setTimeout(function(){
					v.style.backgroundColor = '';
					v.style.color = '';
				}, 2000);
			}

		});
		var self = this;
		window.setTimeout(function(){
			self.message.innerHTML = '';
		}, 6000);
	},

	onSubmit : function(e){

		sb.include('String.prototype.md5');
		var self = this;

		self.submitBtn.waiting();

		var data = {
			uname : self.form.uname.value,
			pass : self.form.pass.value.md5()
		};

		var aj = new sb.ajax({
			url : self.url,
			onHeaders : function(status){

				if(status == '404'){
					self.onUrlNotFound();
					btn.reset();
				}
			},
			onResponse : function(r){
			
				if(r == true){
					self.onSuccess(r);

				} else {
					self.onFailure(r);
				}

				self.submitBtn.reset();
			},
			data : data
		}).fetch();
	}

};