/**
@Name: sb.arrays.iteration
@Description: Can be used to iterate arrays with next, rewind, forward, 
*/
sb.arrays.iteration = {
	
	/*
	@Name: sb.arrays.pointer
	@Description: Used to keep track of the array key we are referenceing with next, prev, end, current, rewind
	
	*/
	pointer : 0,
	
	/*
	@Name: sb.arrays.current
	@Description: Returns the array value of the key we are currently on as referenced by the pointer.  Starts at 0 and chanegs base don use of next(), prev(), rewind(), end()
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	myArray.current(); //returns 1 at first
	
	//after manipulations it changes reference based on the pointer
	myArray.next();
	
	//now it is the next one
	myArray.current(); //returns 10
	*/
	current : function(){
		return this[this.pointer];
	},
	
	/*
	@Name: sb.arrays.end
	@Description: Returns the array value of the last key and sets the array's pointer to that key
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	myArray.end(); //sets the pointer to the last array key and returns the value, in this case 5
	
	*/
	end : function(){
		this.pointer = this.length-1;
		return this[this.pointer];
	},
	
	/*
	@Name: sb.arrays.first
	@Description: Returns the first value in the array without moving the pointer.
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	myArray.first(); //returns 1
	
	*/
	first : function(){
		return this[0];
	},
	
	/*
	@Name: sb.arrays.last
	@Description: Returns the last value of the array without moving the pointer.
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	myArray.last(); //returns 5
	
	*/
	last : function(){
		return this[this.length-1];
	},

	/*
	@Name: sb.arrays.next
	@Description: Returns the next value of the array and moves the pointer forward to it.
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	myArray.current(); //returns 1
	myArray.next(); //returns 10
	myArray.current(); //returns 10
	*/
	next : function(){
		this.pointer +=1;
		return this[this.pointer];
	},
	
	/*
	@Name: sb.arrays.rewind
	@Description: Returns the first value of the array and moves the pointer back to the beginning.
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	myArray.rewind(); //returns 1
	*/
	rewind : function(){
		this.pointer=0;
		return this[this.pointer];
	},

	/*
	@Name: sb.arrays.prev
	@Description: Returns the next value of the array and moves the pointer forward to it.
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	myArray.end(); //returns 5
	myArray.prev(); //returns 4
	myArray.current(); //returns 4
	*/
	prev : function(){
		this.pointer -=1;
		return this[this.pointer];
	},
	
	/**
	@Name: sb.arrays.cycle
	@Author: Paul Visco
	@Version: 1.1 11/19/07
	@Description: Cycles through an array by incrememtning its pointer and reseting it back to the beginng (0) when it gets to the end.
	@Param: Number direction Accepts either 1 for ascending order or -1 for decending order. If not specified that ascending order is the default. 
	@Return: Array Returns The array sorted naturally.
	@Example:
	
	var myArray = [1,10,2,3,4,5];
	var answer = myArray.cycle();
	alert(myArray.cycle());
	
	*/
	cycle : function(){
		
		if(!this.sb_beginCycle){
			this.sb_beginCycle =1;
			var val = this.first();
		} else {
			var val = this.next();
		}
		
		if(typeof val == 'undefined'){
			return this.rewind();
		} else {
			return val;
		}
	}
};

Array.prototype.pointer = sb.arrays.iteration.pointer;
Array.prototype.current = sb.arrays.iteration.current;
Array.prototype.next = sb.arrays.iteration.next;
Array.prototype.prev = sb.arrays.iteration.prev;
Array.prototype.rewind = sb.arrays.iteration.rewind;
Array.prototype.first = sb.arrays.iteration.first;
Array.prototype.last = sb.arrays.iteration.last;
Array.prototype.cycle = sb.arrays.iteration.cycle;