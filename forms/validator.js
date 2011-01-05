/**
form.validator = new sb.forms.validator({
		form : $('#form'),
		validations : {
			//simple regex validation
			acct : /^\d\.\d{2}$/,
			//validation as function
			email : function(input){
				if(input.value.match(sb.forms.validator.validations['email'])){
					//do servside check
					var aj = new sb.ajax({
						url : '/validate/email',
						data : {
							value : input.value
						},
						format : 'json',
						onResponse : function(r){

							if(r.is_valid){
								form.validator.onInputValid(input);
							} else {
								form.validator.onInputInvalid(input);
								board.notify(message, 'error');
							}

						}
					});

					aj.fetch();
					return true;
				} else {
					return false;
				}
			}
		},
		onFormValid : function(form){
			//do something
		}
	});
}

form.validator.validate();
*/

sb.forms.validator = function(params){
	sb.objects.infuse(params, this);
	this.form = sb.$(params.form);

};

sb.forms.validator.validations = {
	phone : /\d{3}-\d{3}-\d{4}/,
	number : /^\d+$/,
	email : /\b(^(\S+@).+(\.\w+)$)\b/gi,
	usdate : /\d{2}\/\d{2}\/\d{4}/,
	usdateflex : /\d{1,2}\/\d{1,2}\/\d{2,4}/
};

sb.forms.validator.prototype= {

	validate : function(){
		var self = this;
		var formValid = true;
		this.form.$('input').forEach(function(input){

			if(input.type == 'text'){
				var validate = input.getAttribute('validate');
				var required = input.hasClassName('required');

				/*var m = v.className.match(/\bval_(\w+)\b/);
				if(m){
					var validate = m[1];
				}*/

				if(!validate && required){
					if(input.value == ''){
						self.onInputRequiredButBlank(input);
						formValid = false;
					} else if(input.value != ''){
						input.style.cssText = '';
					}
				} else if(validate){
					
					var validator = false;
					var valid = true;

					if(!required && input.value == ''){
						valid = true;
					} else {
						if(self.validations && self.validations[validate]){
							validator = self.validations[validate];
						} else if(sb.forms.validator.validations[validate]){
							validator = sb.forms.validator.validations[validate];
						}

						if (validator instanceof RegExp){
							valid = input.value.match(validator);
						} else if(typeof validator == 'function'){
							valid = validator(input);
						}
					}
					
					if(valid){
						self.onInputValid(input);
					} else {
						self.onInputInvalid(input);
						formValid = false;
					}
					
				}


			}
		});

		if(formValid){
			this.onFormValid(this.form);
		} else {
			this.onFormInvalid(this.form);
		}

		return formValid;
	},

	identifyRequiredInputs : function(){
		var self = this;
		this.form.$('input').forEach(function(v){
			if(v.hasClassName('required')){
				self.onInputRequired(v);
			}
		});

		return this;
	},

	onInputRequiredButBlank : function(input){
		this.onInputInvalid(input);
	},

	onInputValid : function(input){
		input.style.backgroundColor = 'lime';
	},

	onInputInvalid : function(input){
		input.style.backgroundColor = 'red';
	},

	onFormInvalid : function(form){},

	onFormValid : function(form){},

	onInputRequired : function(input){
		input.style.border = '3px solid black';
	}
};


