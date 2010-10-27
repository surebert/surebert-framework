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
	if(this.validateOnKeyUp == true){
		this.validateOnKeyUp = sb.events.add(document, 'keyup', function(e){
			self._validate(e.target);
		});
	}

};

sb.forms.inputValidator.prototype = {
	_validate : function(input){
		var validate = input.getAttribute('validate');
		if(input.getAttribute('validate') && this.validations[validate]){
			var validation = this.validations[validate];

			if(validation == 'function'){
				input.valid = validation(input.value);
			} else {
				input.valid = input.value.match(validation);
			}

			if(input.valid){
				this.onValid(input);
			} else {
				this.onInValid(input);
			}
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
	@Name: sb.forms.inputValidator.prototype.validateInputsWithinElement
	@Description: USed to validate all inputs contained within a specific element
	@Param: input The input that is valid
	@Example:
	validator.validateInputsWithinElement('#myDiv');
	*/
	validateInputsWithinElement : function(el){
		var self = this;
		$(el).$('input').forEach(function(inp){
			self._validate(inp);
		});
	}

};