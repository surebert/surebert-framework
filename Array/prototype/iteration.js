/**
@Name: Array.prototype.iteration
@Description: Can be used to iterate arrays with next, rewind, forward, 
*/
Array.prototype.iteration = function(){
	if(this.pointer === null){
		this.point = 0;
	}
};
	
/**
@Name: Array.prototype.pointer
@Description: Used to keep track of the array key we are referenceing with next, prev, end, current, rewind

*/
Array.prototype.pointer = 0;

/**
@Name: Array.prototype.current
@Description: Returns the array value of the key we are currently on as referenced by the pointer.  Starts at 0 and chanegs base don use of next(), prev(), rewind(), end()
@Example:

var myArray = [1,10,2,3,4,5];
myArray.current(); //returns 1 at first

//after manipulations it changes reference based on the pointer
myArray.next();

//now it is the next one
myArray.current(); //returns 10
*/
Array.prototype.current = function(){
	this.iteration();
	return this[this.pointer];
};
	
	/**
@Name: Array.prototype.end
@Description: Returns the array value of the last key and sets the array's pointer to that key
@Example:

var myArray = [1,10,2,3,4,5];
myArray.end(); //sets the pointer to the last array key and returns the value, in this case 5

*/
Array.prototype.end = function(){
	this.iteration();
	this.pointer = this.length-1;
	return this[this.pointer];
};

/**
@Name: Array.prototype.first
@Description: Returns the first value in the array without moving the pointer.
@Example:

var myArray = [1,10,2,3,4,5];
myArray.first(); //returns 1

*/
Array.prototype.first = function(){
	this.iteration();
	return this[0];
};

/**
@Name: Array.prototype.last
@Description: Returns the last value of the array without moving the pointer.
@Example:

var myArray = [1,10,2,3,4,5];
myArray.last(); //returns 5

*/
Array.prototype.last = function(){
	this.iteration();
	return this[this.length-1];
};

/**
@Name: Array.prototype.next
@Description: Returns the next value of the array and moves the pointer forward to it.
@Example:

var myArray = [1,10,2,3,4,5];
myArray.current(); //returns 1
myArray.next(); //returns 10
myArray.current(); //returns 10
*/
Array.prototype.next = function(){
	this.iteration();
	this.pointer +=1;
	return this[this.pointer];
};

/**
@Name: Array.prototype.rewind
@Description: Returns the first value of the array and moves the pointer back to the beginning.
@Example:

var myArray = [1,10,2,3,4,5];
myArray.rewind(); //returns 1
*/
Array.prototype.rewind = function(){
	this.iteration();
	this.pointer=0;
	return this[this.pointer];
};

/**
@Name: Array.prototype.prev
@Description: Returns the next value of the array and moves the pointer forward to it.
@Example:

var myArray = [1,10,2,3,4,5];
myArray.end(); //returns 5
myArray.prev(); //returns 4
myArray.current(); //returns 4
*/
Array.prototype.prev = function(){
	this.iteration();
	this.pointer -=1;
	return this[this.pointer];
};

/**
@Name: Array.prototype.cycle
@Author: Paul Visco
@Version: 1.2 08/21/09
@Description: Cycles through an array by incrememtning its pointer and reseting it back to the beginng (0) when it gets to the end.
@Param: Number direction Accepts either 1 for ascending order or -1 for decending order. If not specified that ascending order is the default. 
@Return: Array Returns The array sorted naturally.
@Example:

var myArray = [1,10,2,3,4,5];
var answer = myArray.cycle();
alert(myArray.cycle());

*/
Array.prototype.cycle = function(backwards){
    
    var val, b=backwards;
	this.iteration();
	if(!this.sb_beginCycle){
		this.sb_beginCycle =1;
        if(b){
            val = this.last();
        } else {
            val = this.first();
        }
	} else {
        if(b){
            val = this.prev();
        } else {
            val = this.next();
        }

	}
	
	if(typeof val == 'undefined'){
		
         if(b){
            return this.end();
        } else {
            return this.rewind();
        }
	} else {
		return val;
	}
};