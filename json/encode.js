/**
 @Name: sb.json
 @Author: Paul Visco v1.02 02/09/09 Taken from http://devers.blogspot.com/2007/09/worlds-smallest-tojson-function.html
 @Description:  Converts a string or object to json.  
 @Param: String x A String, array or object to convert to JSON
 @Return: String in JSON format
 @Example:
 var f = {
 	name : 'fred'
 };
 sb.json.encode(f);
*/
sb.json.encode = function(x) {
	
	switch (typeof x) {
		case 'object':
			if (x) {
				var list = [];
				if (x instanceof Array) {
					for (var i=0;i < x.length;i++) {
						list.push(sb.json.encode(x[i]));
					}
					return '[' + list.join(',') + ']';
				} else {
					for (var prop in x) {
						list.push('"' + prop + '":' + sb.json.encode(x[prop]));
					}
					return '{' + list.join(',') + '}';
				}
			} else {
				return 'null';
			}
		case 'string':
			return '"' + x.replace(/"/g, '\\"') + '"';
			case 'number':
			case 'boolean':
			return new String(x);
	}

};