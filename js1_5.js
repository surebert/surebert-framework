/**
@Name: sb.js1_5
@Description: All of these array prototypes are part of Javascript 1.5 and are included by defaut in sureert for browsers that do not have them (IE and Opera).  They are built in by defualt in Firefox(mozilla) and Safari (webkit)
*/
sb.js1_5 = {};
if(!Array.prototype.filter){

	/**
	@Name: Array.prototype.forEach
	@Description: Runs a function on every value in an array
	@Param: Function func An anonymous function or a reference to a function.  Array data is passed to the function for each vlaue in the array.  Values passed are v,k,a which stand for value, key and array.  v is the current value as it loops through the array, k is the current key as it loops through tthe array and a is the entire array.
	@Example:
	function addOne(val,key,arr){
		val = val+1;

	}
	var myArray=[1,2,3];
	myArray.forEach(addOne);

	//afterwards myArray = [2,3,4]
	*/
	Array.prototype.forEach = function(func){
		var k;
		if(typeof func == 'function'){
			var len = this.length;
			for(k=0;k<len;k++){
				func(this[k], k, this);
			}
		}
	};

	/**
	@Name: Array.prototype.filter
	@Description: Filters values out of an array that do not return true from the test function.
	@Param: Function func An anonymous function or a reference to a function.  Array data is passed to the function for each vlaue in the array.  Values passed are v,k,a which stand for value, key and array.  v is the current value as it loops through the array, k is the current key as it loops through tthe array and a is the entire array.
	@Return: Array The new array contains only the values which were true.
	@Example:
	function over10(val, key, arr) {
		if(val > 10){return true;}
	}

	var myArray = [5, 10, 15];
	var newArray = myArray.filter(over10);
	//returns the array 10,15 because those two values are >=10

	*/
	Array.prototype.filter = function(func){
		var n=[];
		if(typeof func == 'function'){
			this.forEach(function(v,k,arr){
				if(func(arr[k], k, arr) === true){
					n.push(v);
				}
			});
		}

		return n;

	};

	/**
	@Name: Array.prototype.every
	@Description: Checks to see if every value in an array returns true from the function provided
	@Param: Function func An anonymous function or a reference to a function.  Array data is passed to the function for each vlaue in the array.  Values passed are v,k,a which stand for value, key and array.  v is the current value as it loops through the array, k is the current key as it loops through tthe array and a is the entire array.
	@Return: Boolean True or False
	@Example:

	function over10(val, key, arr) {
		if(val > 10){return true;}
	}

	var myArray = [5, 10, 15];
	myArray.every(over10);
	//returns false because not every number in the array is over 10
	*/
	Array.prototype.every = function(func){
		var k;
		if(typeof func == 'function'){
			for(k=0;k<this.length;k++){

				if(func(this[k], k, this) !== true){

					return false;
				}
			}
			return true;
		}
	};


	/**
	@Name: Array.prototype.indexOf
	@Description: Finds the index of the value given within the array.  Return the position of the first matching value.  Rememeber that array start at 0.
	@Param: Object/String/Number val The value you want to search for in the array.
	@Return: Integer
	@Example:

	var myArray = [1,2,3,'a','b'];
	var answer = myArray.indexOf('a');
	//answer is 3

	*/
	Array.prototype.indexOf = function(val){
		for(var k=0;k<this.length;k++){
			if(this[k] == val){
				return k;
			}
		}
		return -1;
	};

	/**
	@Name: Array.prototype.lastIndexOf
	@Description: Finds the last index of the value given within the array.Rememeber that array start at 0.
	@Param: Object/String/Number val The value you want to search for in the array.
	@Return: Integer
	@Example:

	var myArray = [1,2,3,2];
	var answer = myArray.lastIndexOf(2);
	//answer is 3
	*/
	Array.prototype.lastIndexOf = function(val){
		var p=-1,k;
		for(k=0;k<this.length;k++){
			if(this[k] == val){
				p=k;
			}
		}
		return p;
	};

	/**
	@Name: Array.prototype.map
	@Description: Runs a function on every item in the array and returns the results in an array.
	@Param: Function func The function you want applied run on every value in the array.  It is automatically passed the current (value, key, and array) as arguments on eqach loop through the array.  The function can be either a reference to a global function or an inline anonymouse function.
	@Return: Array A new array with each value mapping to the result of the original arrays value after is is passed through the function specified.
	@Example:
	function addTen(val, key, array) {
		return val+10;
	}

	var myArray = [5, 10, 15];
	var answer = myArray.map(addTen);
	//answer = [15, 20, 25];

	*/
	Array.prototype.map = function(func){
		var n=[];
		if(typeof func == 'function'){
			this.forEach(function(v,k,a){n.push(func(v,k,a));});
		}
		return n;
	};

	/**
	@Name: Array.prototype.some
	@Description: Similar to sb.arrays.every - if some of the function results are true then some returns true
	@Param: Function func A function that every value of the array is passed to.  The function is passed (val, key, arr) on every pass of the loop.
	@Return: Boolean Returns true if some of the values return true when run through the function provided

	@Example:
	function isAboveFive(val, key, arr){
		if(val >5) {return true;}
	}
	var myArray = [5, 10, 15];
	var answer = myArray.some(isAboveFive);
	//answer = true //because some values return true when passed through the isAboveFive function

	*/
	Array.prototype.some = function(func){
		var k;
		if(typeof func == 'function'){
			for(k=0;k<this.length;k++){
				if(func(this[k], k, this) === true){
					return true;
				}
			}
			return false;
		}
	};

}