/**
@Name: Array.prototype.reduceRight
@Description: This emulates Array.prototype.reduceRight from javascript 1.6 and is taken from the MDC site reference.  .reduceRight executes the callback function once for each element present in the array, excluding holes in the array, receiving four arguments: the initial value (or value from the previous callback call), the value of the current element, the current index, and the array over which iteration is occurring.

The call to the reduce callback would look something like this: 
@Param: function The function to apply the array elements to
@Example:
myArray.reduceRight(function(previousValue, currentValue, index, array){
  // ...
})

//real examples
var total = [0, 1, 2, 3].reduceRight(function(a, b) { return a + b; });
// total == 6

var flattened = [[0, 1], [2, 3], [4, 5]].reduceRight(function(a, b) {
  return a.concat(b);
}, []);
// flattened is [4, 5, 2, 3, 0, 1]
*/
Array.prototype.reduceRight = function(func){
	var len = this.length;
	var rv;
	
	if (typeof func != "function"){
	  throw new TypeError();
	}

	// no value to return if no initial value, empty array
	if (len === 0 && arguments.length == 1){
	  throw new TypeError();
	}

	var i = len - 1;
	if (arguments.length >= 2){
	  rv = arguments[1];
	} else {
	  do{
	    if (i in this){
	      rv = this[i--];
	      break;
	    }
	
	    // if array contains no values, no initial value to return
	    if (--i < 0){
	      throw new TypeError();
	    }
	  }while (true);
	}

	for (; i >= 0; i--){
	  if (i in this){
	    rv = func.call(null, rv, this[i], i, this);
	  }
	}
	
	return rv;
};