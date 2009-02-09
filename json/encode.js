/**
 @Name: sb.json
 @Description:  Converts a string or object to json.  Taken from http://devers.blogspot.com/2007/09/worlds-smallest-tojson-function.html
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