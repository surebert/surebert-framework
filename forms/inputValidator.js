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
		//simple regex validation
		acct : /^\d\.\d{2}$/,
		phone : /\d{3}-\d{3}-\d{4}/,
		number : /^\d+$/,
		email : /\b(^(\S+@).+(\.\w+)$)\b/gi
	},
	validateOnKeyUp : true,
	onValid: function(input){
		input.style.backgroundColor = 'lime';
	},
	onInValid: function(input){
		input.style.backgroundColor = 'red';
	}
});
*/

sb.forms.inputValidator = function(o){
	sb.objects.infuse(o, this);
	var self = this;

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
			self.onKeyDown(e);
		}
	});

};

sb.forms.inputValidator.prototype = {
	_validate : function(e){
		var input = e.target;
		if(input.nodeName == 'INPUT'){
			input.value = input.value.replace(/(^\s+|\s+$)/g, '');
		}
		
		var maxlength = input.getAttribute('maxlength');
		if(maxlength && input.value && input.value.length >= maxlength){
			input.value = input.value.substring(0, maxlength);
			e.preventDefault();
		}

		var validate = input.getAttribute('validate');
		input.validationErrors = [];
		if(validate){
			var self = this;
			var validationTypes = validate.split(' ');
			validationTypes.forEach(function(validationType){

				var validation = self.validations[validationType];

				if(typeof validation == 'function'){
					input.valid = validation(input);
				} else {
					input.valid = input.value.match(validation);
				}

				if(input.valid){
					self.onValid(input);
				} else {
					input.validationErrors.push(validationType);
					self.onInValid(input);
				}
			});
			
		}
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
	onValid: function(input){},

	/**
	@Name: sb.forms.inputValidator.prototype.onInValid
	@Description: Fires when the input is validated and it is invalid
	@Param: input The input that is valid
	@Example:
	validator.onInValid = function(input){
		input.style.backgroundColor = 'red';
	};
	*/
	onInValid: function(input){},

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
	@Description: USed to validate all inputs contained within a specific element
	@Param: input The input that is valid
	@Example:
	validator.validateInputsWithinElement('#myDiv');
	*/
	validateInputsWithinElement : function(el){
		var self = this;
		$(el).$('input,textarea').forEach(function(inp){
			self._validate(inp);
		});
	}

};