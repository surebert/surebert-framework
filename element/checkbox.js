/**
@Name: sb.element.checkBox
@Description: Used to create labeled cross-browser checkboxes.  When the label is selected it checks or unchecks the box
@Param: Object params
params.label - The text label
params.name - The name property of the input
params.value - The value property of the input
params.checked - The checked property of the input
@Return: String Returns the value stored for the cookie or false if the cookie is not found
@Example:
var x = new sb.element.checkBox({
	label : 'Add me to the mailing list',
	name : 'Add To Mailing List',
	value : 'yes',
	checked : true
});

x.label.appendToTop('body');
*/
sb.element.checkBox = function(params){
	var checkbox = {};
	params.id = params.id || sb.uniqueID();
	
	checkbox.label = new sb.element({
		tag : 'label',
		innerHTML : params.label,
		className : params.labelClassName,
		unselectable : 'on',
		styles : {
			MozUserSelect : 'none'
		},
		events : {
			click : function(e){
				this.input.checked = (this.input.checked === true) ? false : true;
			}
		}
	});
	
	checkbox.label.setAttribute('for', params.id);

	checkbox.label.onselectstart = function() {
        return false;
    };
	
	checkbox.input = new sb.element({
		tag : 'input',
		id : params.id,
		name : params.name || '',
		type : 'checkbox',
		defaultChecked : params.checked || false,
		value : params.value || '',
		className : params.inputClassName
	});
	
	checkbox.label.input = checkbox.input;
	checkbox.input.label = checkbox.label;
	checkbox.input.appendTo(checkbox.label);
	return checkbox;
};