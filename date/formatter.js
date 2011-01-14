sb.include('String.prototype.numpad');

/**
@Name: sb.date.formatter
@Description: A constructor used to format date strings, based on PHP date function
@Param: integer timestamp A unix timestamp to specify a date other than now
@Return: sb.date object with the follwoing properties

Experiment with it:
.m = month as two digit padded string e.g. 03
.F = month as full name e.g. July
.M = month as full name truncated to three letters e.g. Aug
.d = day of month as two digit padded string e.g. 03
.l = (lowercase L) day of week as full name e.g. Sunday
.D = day as full name truncated to three letters e.g. Sun
.Y = full year 4 digit year e.g. 2006
.y = 2 digit year e.g. 06
.H = hour in 24 hour time e.g. 15
.h = hour in standard US format 03
.G - hour in 24 hour time minus the zero padding in H
.g - hour in standard US format minus the zero padding in h
.a - am or pm
.A - AM or PM
.i = minutes as two digit padded string e.g. 04
.s = seconds as two digit padded string e.g. 04
.n = milliseconds e.g. 0-1000
.u = UNIX time
@Example:
//create a sb.date object of the current date/time
var myDate = new sb.date.formatter();

//create a sb.date object of another date/time by unixtimestamp
var myDate = new sb.date.formatter(1174337641);

//alerts the minute of the date object
alert(myDate.i);

//alerts a formatted date string based on your date object
var myDate = new sb.date();
alert(myDate.format('/m/d/y H:i:s');
*/
sb.date.formatter = function(timestamp, offset){

	this.timestamp = timestamp || false;
	offset = offset || 0;
	if(timestamp && timestamp.match(/T/)){
		var d = timestamp.replace(/[ZT\-]/g, ':').split(':');
		this.date = new Date(d[0], d[1]-1, d[2], d[3]-offset, d[4], d[5]);
	} else {
		this.date = new Date();
		if(this.timestamp){
			this.date.setTime(this.timestamp*1000);
		}
	}

	this.getDate();
};

sb.date.formatter.prototype= {

	/**
	@Name: sb.date.formatter.prototype.process
	@Description: Used Internally
	*/
	getDate : function(str){
		//'2011-01-14T00:21:07Z'
		var d=this.date,self=this;

		d.m = String(d.getMonth()+1).numpad();
		d.F = sb.dates.months[d.getMonth()];
		d.M = d.F.substr(0,3);
		d.d = String(d.getDate()).numpad();

		d.l = String(sb.dates.days[d.getDay()]);
		d.D = d.l.substr(0,3);
		//d.w = sb.dates.days.indexOf(d.l);
		d.Y = String(d.getFullYear());
		d.y = d.Y.substr(2,4);

		d.H = String(d.getHours()).numpad();
		d.G = d.getHours();
		d.g = (d.H >12) ?  d.H-12 : d.H;
		d.h = (d.H >12) ?  String(d.H-12).numpad() : d.H;
		d.a = (d.H < 12) ? 'am' : 'pm';
		d.A = d.a.toUpperCase();

		d.i = d.getMinutes();
		d.s = String(d.getSeconds()).numpad();
		d.U = Date.parse(d)/1000;
		d.n = d.getMilliseconds();
		d.date = d.m+'/'+d.d+'/'+d.y;
		d.time = d.h+':'+d.i;
		d.time24 = d.H+':'+d.i;
		sb.objects.forEach.call(d, function(val, prop, o){

			self[prop] = val;
		});

	},

	/**
	@Name: sb.date.formatter.prototype.format
	@Description: Can be used on any sb.date insatnce to return another formatted date string.  See formatting options in sb.date.
	@Param: String format The string format of the desired date format
	Experiment with it:
	m = month as two digit padded string e.g. 03
	F = month as full name e.g. July
	M = month as full name truncated to three letters e.g. Aug
	d = day of month as two digit padded string e.g. 03
	l = (lowercase L) day of week as full name e.g. Sunday
	D = day as full name truncated to three letters e.g. Sun
	Y = full year 4 digit year e.g. 2006
	y = 2 digit year e.g. 06
	H = hour in 24 hour time e.g. 15
	h = hour in standard US format 03
	G = hour in 24 hour time wihtout the leading zeros e.g. 9
	g = hour in 12 hour time wihtout the leading zeros e.g. 9
	a = 'am' or 'pm'
	A = 'AM' or 'PM'
	i = minutes as two digit padded string e.g. 04
	s = seconds as two digit padded string e.g. 04
	n = milliseconds e.g. 0-1000
	u = UNIX time
	@Param: String timestamp A unix timestamp to specify a date other than now
	@Example:
	//get a formatted date string of the current date/time
	var myDate = new sb.date('m/d/y H:i:s');
	//myDate.toString = 10/29/06 01:41:33

	//use the format method of the instance to switch date formats
	myDate.format('d/m/y');

	*/
	format : function(str){
		var x,f='';
		if(str !== undefined){

			for(x=0;x<str.length;x++){
				switch(str.charAt(x)){

					case 'm':
						f += str.charAt(x).replace(/m/, this.m);
					break;

					case 'M':
						f += str.charAt(x).replace(/M/, this.M);
					break;

					case 'F':
						f += str.charAt(x).replace(/F/, this.F);
					break;

					case 'd':
						f += str.charAt(x).replace(/d/, this.d);
					break;

					case 'D':
						f += str.charAt(x).replace(/D/, this.D);
					break;

					case 'l':
						f += str.charAt(x).replace(/l/, this.l);
					break;

					case 'w':
						f += str.charAt(x).replace(/w/, this.w);
					break;

					case 'Y':
						f += str.charAt(x).replace(/Y/, this.Y);
					break;

					case 'y':
						f += str.charAt(x).replace(/y/, this.y);
					break;

					case 'H':
						f += str.charAt(x).replace(/H/, this.H);
					break;

					case 'h':
						f += str.charAt(x).replace(/h/, this.h);
					break;

					case 'G':
						f += str.charAt(x).replace(/G/, this.G);
					break;

					case 'g':
						f += str.charAt(x).replace(/g/, this.g);
					break;

					case 'i':
						f += str.charAt(x).replace(/i/, this.i);
					break;

					case 's':
						f += str.charAt(x).replace(/s/, this.s);
					break;

					case 'U':
						f += str.charAt(x).replace(/U/, this.U);
					break;

					case 'n':
						f += str.charAt(x).replace(/n/, this.n);
					break;

					case 'w':
						f += str.charAt(x).replace(/w/, this.w);
					break;

					case 'a':
						f += str.charAt(x).replace(/a/, this.a);
					break;

					case 'A':
						f += str.charAt(x).replace(/A/, this.A);
					break;

					default:
						f+= str.charAt(x);
				}
			}

			this.formatted =f;
			f=null;
			return this.formatted;
		}
	}
};

sb.dates = {
	months : ["January","Febuary","March","April","May","June","July","August","September","October","November","December"],

	days : ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday']

};
