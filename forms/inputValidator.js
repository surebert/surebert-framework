sb.include('Element.prototype.rollUp');
/**
@Name: sb.validation
@Description: Used to validate inputs
@Param: Object
validateOnKeyUp boolean should validations occurr on keyup
validator regex or function with input as argument, these are executed or matched when the input is validated
onValid(input) function Fires when the input is validate if it is valid
onInValid(input) function Fires when the input is not validate
@Example:
var eg1 = new sb.validation({
	validator : /^\d\.\d{2}$/,
	errorMessage :  'Sorry this does not match an acct number e.g. 4.32'
});

var eg2 = new sb.validation({
	validator : function(input){
		return input.value != 'ff';
	},
	errorMessage :  'Sorry the value is not ff'
});
*/
sb.validation = function(o){
	sb.objects.infuse(o, this);
};
sb.validation.prototype = {
	//func(input) or regex
	validator : null,
	//func(input)
	onValid : null,
	//func(input)
	onInValid : null,
	//string
	errorMessage : null
};

/**
@Name: sb.forms.inputValidator
@Description: Validates inputs based on validate attribute
@Param: Object
validateOnKeyUp boolean should validations occurr on keyup
validations object of regex or function properties, these are executed or matched when the input is validated
onValid(input) function Fires when the input is validate if it is valid
onInValid(input) function Fires when the input is not validate
@Example:
var validator = new sb.forms.inputValidator({
	validations : {
		acct : new sb.validation({
			validator : /^\d\.\d{2}$/,
			errorMessage :  'Sorry this does not match an acct number e.g. 4.32'
		}),
		phone : new sb.validation({
			validator :/^\d{3}-\d{3}-\d{4}$/,
			errorMessage :  'Sorry this does not match a phone number e.g. 716-877-9999'
		}),
		email : new sb.validation({
			validator : /\b(^(\S+@).+(\.\w+)$)\b/ig,
			errorMessage :  'Sorry this does not match a phone number e.g. test@test.com'
		}),
		at_least_one : new sb.validation({
			validator : function(input){
				var inputs = $("input[name='"+input.name+"']");
				return inputs.some(function(v){return v.checked;});
			},
			onValid : function(input){
				var inputs = $("input[name='"+input.name+"']");
				inputs.forEach(function(v){
					if(v.errorMessageP){
						v.errorMessageP.rollUp();
					}
				});
			},

			onInValid : function(input){
				var inputs = $("input[name='"+input.name+"']");
				sb.forms.inputValidator.prototype.onInValid(inputs.nodes[0]);
			},
			errorMessage :  'You must select at least one'
		})
	},

	validateOnKeyUp : true,

	onKeyDown : function(e) {
		//not allow pipe
		if(e.keyCode == 220){
			e.preventDefault();
		}
	}

});
validator.validateInputsWithinElement('#wrapper');
<input type="text" validate="acct" required="1" name="acct" />
*/
sb.forms.inputValidator = function(o){
	var self = this;
	sb.objects.infuse(o, this);
	this.validateOnKeyUp = this.validateOnKeyUp === false ? this.validateOnKeyUp : true;
	this.onKeyUpEvt = sb.events.add(document, 'keyup', function(e){
	
		if(typeof self.onKeyUp == 'function'){
			if(self.onKeyUp(e) === false){
				return;
			}
		}
		if(self.validateOnKeyUp == true){
			self._validate(e);
		}
	});
	
	this.onKeyDownEvt = sb.events.add(document, 'keydown', function(e){
		if(typeof self.onKeyDown == 'function'){
			var target = e.target;
			var nn = target.nodeName;
			var validate = target.attr('validate');
			if((nn == 'TEXTAREA' || e.target.nodeName == 'INPUT') && validate){
				self.onKeyDown(e);
			}
			
		}
	});

	this.parent = sb.forms.inputValidator.prototype;
};

sb.forms.inputValidator.prototype = {
	 validateOnKeyUp: true,
	_validate : function(e){
		var input = e.nodeName ? e : e.target;

		if(input.nodeName == 'INPUT'){
			//input.value = input.value.replace(/(^\s+|\s+$)/g, '');
		}
		
		var maxlength = input.getAttribute('maxlength');
		if(maxlength && input.value && input.value.length >= maxlength){
			input.value = input.value.substring(0, maxlength);
			e.preventDefault();
		}

		var validate = input.getAttribute('validate');
		var required = input.getAttribute('required') || 0;
		
		if(required && validate === ''){
			validate = '_required';
			this.validations['_required'] = new sb.validation({
				validator : /.*/,
				errorMessage :  'Field required'
			});
		}
		
		if(validate){
			var validation  = this.validations[validate];
			
			if(!validation){return false;}
			var self = this;
			input.valid = false;
			//if optional
			if(input.value === '' && required === '0'){
				input.valid = true;
			} else if(input.value !== ''){
				if(validation){
					if(typeof validation.validator == 'function'){
						input.valid = validation.validator(input);
					} else {
						input.valid = input.value.match(validation.validator);
					}
				}
			}
			
			if(input.valid){
				if(typeof validation.onValid == 'function'){
					validation.onValid(input);
				} else if(self.onValid){
					self.onValid(input);
				}
			} else {
				input.errorMessage = validation.errorMessage;

				if(typeof validation.onInValid == 'function'){
					validation.onInValid(input);
				} else if(self.onInValid){
					self.onInValid(input);
				}
			}

			return input.valid;
			
		}
		return false;
	},
	/**
	@Name: sb.forms.inputValidator.prototype.onValid
	@Description: Fires when the input is validated and it is valid
	@Param: input The input that is valid
	@Example:
	validator.onValid = function(input){
		input.style.backgroundColor = 'lime';
	};
	*/
	onValid: function(input){
		input.style.backgroundColor = 'lime';
		if(input.errorMessageP){
			input.errorMessageP.rollUp();
			input.errorMessageP = false;
		}
	},

	/**
	@Name: sb.forms.inputValidator.prototype.onInValid
	@Description: Fires when the input is validated and it is invalid
	@Param: input The input that is valid
	@Example:
	validator.onInValid = function(input){
		input.style.backgroundColor = 'red';
	};
	*/
	onInValid: function(input){
		input.style.backgroundColor = 'red';
		
		var parent = $(input.parentNode);
		if(!input.errorMessageP){
				input.errorMessageP = new sb.element({
					tag : 'p',
					innerHTML : '',
					styles : {
						color : 'red'
					}
				});
		}
		input.errorMessageP.innerHTML = input.errorMessage;
		input.errorMessageP.appendToTop(parent);
	},

	/**
	@Name: sb.forms.inputValidator.prototype.onKeyDown
	@Description: Fires when the input is validated and it is invalid
	@Param: input The input that is valid
	@Example:
	validator.onKeyDown = function(e){};
	*/
	onKeyDown : function(){},

	/**
	@Name: sb.forms.inputValidator.prototype.onKeyDown
	@Description: Fires when the input is validated and it is invalid
	@Param: input The input that is valid
	@Example:
	validator.onKeyUp = function(e){};
	*/
	onKeyUp : function(){},

	/**
	@Name: sb.forms.inputValidator.prototype.validateInputsWithinElement
	@Description: Used to validate all inputs contained within a specific element
	@Param: input The input that is valid
	@Example:
	validator.validateInputsWithinElement('#myDiv');
	*/
	validateInputsWithinElement : function(el){
		var self = this;
		$(el).$('input,textarea').forEach(function(inp){
			self._validate(inp);
		});
	},

	/**
	@Name: sb.forms.inputValidator.prototype.validateInput
	@Description: Validate a specific input
	@Param: input The input that is valid
	@Example:
	validator.validateInput('#myInput');
	*/
	validateInput : function(input){
		return this._validate($(input));
	}

};