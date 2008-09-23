
/**
@Name: sb.arrays.reduce
@Author: Paul Visco
@Version: 1.1 11/19/07
@Description: This emulates sb.arrays.reduce from javascript 1.6 and is taken from the MDC site reference.  .reduce executes the callback function once for each element present in the array, excluding holes in the array, receiving four arguments: the initial value (or value from the previous callback call), the value of the current element, the current index, and the array over which iteration is occurring.

The call to the reduce callback would look something like this: 
@Param: function The function to apply the array elements to
@Example:
myArray.reduce(function(previousValue, currentValue, index, array){
  // ...
});

//real examples
var total = [0, 1, 2, 3].reduce(function(a, b){ return a + b; });
// total == 6

var flattened = [[0,1], [2,3], [4,5]].reduce(function(a,b) {
  return a.concat(b);
}, []);
// flattened is [0, 1, 2, 3, 4, 5]
*/
sb.arrays.reduce = function(func){
	var len = this.length;
	var rv;
	if (typeof func != "function"){
	  throw new TypeError();
	}

	// no value to return if no initial value and an empty array
	if (len === 0 && arguments.length == 1){
	  throw new TypeError();
	}

	var i = 0;
	if (arguments.length >= 2){
	  rv = arguments[1];
	} else {
		
	  do{
	    if (i in this){
	      rv = this[i++];
	      break;
	    }
	
	    // if array contains no values, no initial value to return
	    if (++i >= len){
	      throw new TypeError();
	    }
	  }
	  while (true);
	}

	for (; i < len; i++){
	  if (i in this){
	    rv = func.call(null, rv, this[i], i, this);
	  }
	}
	
	return rv;
};
Array.prototype.reduce = sb.arrays.reduce;