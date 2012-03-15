/**
@Name: sb.date.isDate
@Description: Determines if string is actual date in mm/dd/YYYY format
@Param: Object params
minYear int The minimum year to allow
maxYear int The max year to allow
@Example:
sb.date.isDate('01/22/1977');
*/
sb.date.isDate = function(txtDate, params){
	if(!params){
		params = {};
	}

	if(!txtDate){
		return false;
	}
	var currentYear = new Date().getFullYear();
	var minYear = params.minYear || false;
	var maxYear = params.maxYear || false;
	var separator = params.separator || '/';
	var objDate,mSeconds,day, month, year;

	if (txtDate.length !== 10) {
		return false;
	}

	if (txtDate.substring(2, 3) !== separator || txtDate.substring(5, 6) !== separator) {
		return false;
	}

	month = txtDate.substring(0, 2) - 1;//starts from 0
	day = txtDate.substring(3, 5) - 0;
	year = txtDate.substring(6, 10) - 0;

	if (minYear && year < minYear){
		return false;
	}

	if(maxYear && year > maxYear){
		return false;
	}

	mSeconds = (new Date(year, month, day)).getTime();

	objDate = new Date();
	objDate.setTime(mSeconds);

	if (objDate.getFullYear() !== year ||
		objDate.getMonth() !== month ||
		objDate.getDate() !== day) {
		return false;
	}

	return true;
};