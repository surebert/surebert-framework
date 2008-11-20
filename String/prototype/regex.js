/**
@Name: String.prototype.regex
@Description: Used to match strings to common patterns
@Example:
alert('01/22/1977'.match(String.prototype.regex('mm/dd/yyyy'));
*/
String.prototype.regex = {
	'mm/dd/yyyy' : new RegExp(/^\d{2}\/\d{2}\/\d{4}$/),
	'mm/dd/yy' : new RegExp(/^\d{2}\/\d{2}\/\d{2}$/),
	'phone_with_area' : new RegExp(/^\d{3}-\d{3}-\d{4}$/),
	'social_security' : new RegExp(/^\d{3}-\d{2}-\d{4}$/),
	'zip5' : new RegExp(/^\d{5}$/),
	'zip9' : new RegExp(/^\d{5}-\d{4}$/)
};