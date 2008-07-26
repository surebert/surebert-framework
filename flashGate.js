/**
@Author: Paul Visco of http://elmwoodstrip.org?u=paul
@Version: 4.10
@Release: 02/12/08 05/27/08
@Package: surebert.flashGate
@Desciption: Allows communications between surebert.swf flashGlate and the surebert toolkit, extending javascript by allowing it to borrow functionality from flash.  Currently it can play sounds, save data to the flash storage space, and allow multi file uploads.
*/

if(typeof sb.swf =='undefined'){
	sb.include('swf');
}

/**
@Name: sb.flashGateDebug
@Description: Boolean Determines if the surebert.swf debugs actions to the sb.consol.  Requires sb.developer.
*/
sb.flashGateDebug = 0;

/**
@Name: sb.onFlashGateLoad
@Description: An array of functions that should fire when the sb.flashGate loads.  You can push functions in here to have them fire when the flashGate loads.
*/
sb.onFlashGateLoad = [];

/**
@Name: sb.sound
@Description: A constructor for creating new sound object instances.  Allows javascript to load, play and stop mp3 sounds.  Also has hooks for changing pan(left and right speaker), volume, and position of track, and reading id3 tag data from the song.
@Param String url The address of the sound file e.g. http://myexample.com/mySound.mp3
@Param Number vol The volume to play the song at
@Example:
var mySound = new sb.sound('http://myexample.com/mySound.mp3);
mySound.play();
*/

sb.sound = function(url, vol){
	
	if(typeof url == 'undefined'){return;}
	this.url = url;
	this.vol = vol || sb.sound.globalVolume;
	sb.sound.sounds.push(this);
}; 

//add infuse in case globals are turned off
sb.sound.infuse = sb.objects.infuse;
	
sb.sound.infuse({
	
	/**
	@Name: sb.sound.stopAll
	@Description: Stops all sounds currently on the page
	@Example:
	sb.sounds.stopAll();
	*/
	stopAll : function(){
		sb.sound.sounds.forEach(function(v){
			v.stop();
		});
	},
	
	/**
	@Name: sb.sound.muteAll
	@Description: Mutes all sounds currently on the page, does not stop them from playing.
	@Example:
	sb.sounds.muteAll();
	*/
	muteAll : function(){
		sb.sound.sounds.forEach(function(v){
			v.setVolume(0);
		});
	},
	
	/**
	@Name: sb.sound.globalVolume
	@Description: The globalVolume on the page
	*/
	globalVolume : 50,

	/**
	@Name: sb.sound.sounds
	@Description: An array of all the sound object instances on the page.
	*/
	
	sounds : [],
	
	/**
	@Name: sb.sound.muted
	@Description: When set to 0 all sounds on the page are not muted when set to 1 all sounds are muted
	*/
	muted : 0,
	
	/**
	@Name: sb.sound.handlers
	@Description: Used internally. Passes events to individual sound instances when the events fire from the flash flashGate.
	*/
	handlers : {
		/**
		@Name: sb.sound.handlers.oncomplete
		@Description: Used internally. Fires when a sound is completed and triggers the firing of the sound instances oncomplete handler if it exists
		*/
		oncomplete : function(info){
			if(typeof sb.sound.sounds[info.id].oncomplete=='function'){
				sb.sound.sounds[info.id].oncomplete(info);
			}
		},
		/**
		@Name: sb.sound.handlers.onid3
		@Description: Used internally. Fires when a sound's id3 data is loaded and triggers the firing of the sound instance's onid3 handler if it exists
		*/
		onid3 : function(info){
			if(typeof sb.sound.sounds[info.id].onid3=='function'){
				sb.sound.sounds[info.id].onid3(info);
			}
		},
		/**
		@Name: sb.sound.handlers.onload
		@Description: Used internally. Fires when a sound is onload and triggers the firing of the sound instances onload handler if it exists
		*/
		onload : function(info){
			if(typeof sb.sound.sounds[info.id].onload=='function'){
				sb.sound.sounds[info.id].onload(info);
			}
		}
	}
});


/**
@Name: sb.sound.prototype
@Description: The properties and methods of all sound object instances.  All examples refer to a sound object instance called mySound which was created like this
@Example:
var mySound = new sb.sound('http://myexample.com/mySound.mp3);
*/
sb.sound.prototype = {
	
	/**
	@Name: sb.sound.prototype.playing
	@Description: Boolean The playing status of a sound object. 0 = not playing, 1 is playing
	*/
	playing :0,
	
	/**
	@Name: sb.sound.prototype.play
	@Description: Function Plays the sound file specified in the url property of the sound object.
	@Param: Number vol The volume to play the sound at measured between 0 and 100
	@Example:
	mySound.play();
	*/
	play : function(vol){
		if(typeof sb.flashGateInit=='undefined'){
			
			if(typeof this.interval !='undefined'){return;}
			
			var t=this;
			this.tries = 0;
			this.interval = window.setInterval(function(){
				if(typeof sb.flashGateInit !='undefined'){
					t.play();
					window.clearInterval(t.interval);
				}
				this.tries++;
				
				if(this.tries > 10){
					window.clearInterval(t.interval);
					sb.consol.error(sb.messages[16]+t.url);
				}
			}, 100);
			
			return;
		}
		
		if(sb.sound.muted===1){return;}
		vol = vol || this.vol;
		
		if (sb.flashGate.soundCreate) {
			if (typeof this.id == 'undefined') {
			
				this.id = sb.flashGate.soundCreate(this.url, vol);
			}
			else {
				this.setVolume(vol);
				this.start();
			}
		}
		return this.id;
	},
	
	/**
	@Name: sb.sound.prototype.start
	@Description: Function Starts a sound if it was stopped
	@Example:
	mySound.start();
	*/
	start : function(){
		this.playing =1;
		sb.flashGate.soundStart(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.stop
	@Description: Function Stops a sound taht was started
	@Example:
	mySound.stop();
	*/
	stop : function(){
		this.playing =0;
		sb.flashGate.soundStop(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.setVolume
	@Description: Function Sets the volume of a sound object instance
	@Param Number vol The volume measured between 0 and 100
	@Example:
	mySound.setVolume(20);
	*/
	setVolume : function(vol){
		sb.flashGate.soundSetVolume(this.id, vol);
	},
	
	/**
	@Name: sb.sound.prototype.mute
	@Description: Function Mutes the volume of a sound object instance
	@Example:
	mySound.mute();
	*/
	mute : function(){
		this.setVolume(0);
	},
	
	/**
	@Name: sb.sound.prototype.setPan
	@Description: Function Sets the pan of a soudn object instance
	@Param: Number pan Pan bewteen -100 (far left) and 100 (far right)
	@Param: String pan You can also pass it the shortcuts 'left', right', 'middle'
	@Example:
	mySound.setPan('left');
	mySound.setPan(-100);
	*/
	setPan : function(pan){
		switch(pan){
			case 'left':
				pan = -100;
				break;
			case 'right':
				pan = 100;
				break;
			case 'middle':
				pan = 0;
				break;
		}
		
		sb.flashGate.soundSetPan(this.id, pan);
	},
	
	/**
	@Name: sb.sound.prototype.getPan
	@Description: Function Gets the pan of a sound object instance
	@Return: Number Pan between -100(far left) and 100(far right)
	@Example:
	var pan = mySound.getPan();
	//pan = -100 //<-possible result
	*/
	getPan : function(){
		return sb.flashGate.soundGetPan(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.setPosition
	@Description: Function Sets the position of a sound object instance
	@Param: Number position A position between 0% and 100%
	@Example:
	//sets the sound position to 50%
	mySound.setPosition(50);
	*/
	setPosition : function(position){
		sb.flashGate.soundSetPosition(this.id, position);
	},
	
	/**
	@Name: sb.sound.prototype.getPosition
	@Description: Function Gets the position of a sound object instance
	@Example:
	var post = mySound.getPosition(50);
	//pan = 50 //<-possible result
	*/
	getPosition : function(){
		return sb.flashGate.soundGetPosition(this.id);
	}
	
};

/**
@Name: sb.sharedObject
@Description: Object Allows the sotring and retreiving of data in the flash shared object space on the client's computer.  This space is virtually unlimited and is not emptied when a user empties their cookies.  The calls work exactly the same as with sb.cookies.
*/
sb.sharedObject = {

	/**
	@Name: sb.sharedObject.remember
	@Description: Used to make the clients computer remember a value as a in the flash shared object space
	@Param: String name The name (key) of the cookie which will hold the valuee
	@Param: String value The value the cookie holds
	@Example:
	sb.sharedObject.remember('name', 'paul');
	*/
	remember :function(key, v){
	
		try{
			sb.flashGate.remember(key,escape(v));
		} catch(e){
			window.setTimeout(function(){sb.sharedObject.remember(key,v);}, 1000);
		}
	},
	
	/**
	@Name: sb.sharedObject.recall
	@Description: Used to recall flash shared object stored values
	@Param: String name The name of the shared object who's value you are trying to recall
	@Return: String Returns the value stored for the shared object or false if the shared object is not found
	@Example:
	var answer = sb.sharedObject.recall('myData');
	//answer = the value the shared object was set to with sb.sharedObject.remember
	*/
	recall : function(key){
		try{
			var val = unescape(sb.flashGate.recall(key));
			if(val == 'null'){return false;} else {return val;}
		} catch(e){return false;}
	},
	
	/**
	@Name: sb.sharedObject.forget
	@Description: Used to make the clients computer forget a flash shared object stored value
	@Param: String name The name (key) of the shared object which will be forgotten
	@Example:
	sb.sharedObject.forget('myData');
	*/
	forget : function(key){
		try{
			sb.flashGate.forget(key);
		} catch(e){return false;}
		return true;
	}
};

/**
@Name: sb.upload
@Version: 2.0 05/27/08
@Description: Used to upload files.  Flash puts all callback functions in a try catch during execution so if you have errors in your event listeners, nothing happends making it a bit tricky to debug.  Make sure to have something echoed out in your upload script or it will not return progress data, etc.  Even a space suffices.  Also, don't forgot to up you max upload size on the server side to handle larger files.
@Param Object param An object which set the file type accepted, serveside script, passes data, sets max file size in K and max number of files.
@Example: 

//fires when the files are selected
function onSelect(){}

//fires when the files are opened
//the file object argument has the following properties {name, size, type}
function onOpen(file){}

//fires when the user hits cancel button on the file browser
function onCancel(){}

//fires once for each file when you run sb.uploads.cancel or sb.uploads.cancelAll
//the file object argument has the following properties {name}
function onFileCancel(file){}

//fires for each progress increment of the file uploads, includes percent, bytesTotal and bytesLoaded
//the file object argument has the following properties {name, size, type, bytesTotal, bytesLoaded, percent}
//the files objects argument has the following properties {total, remaining}
function onProgress(file, files){}

//fires once after each file completes to give total progress of all files
files object argument has the following properties files {total, remaining, percent}
function onAllProgress(files){}

//fires once for each file as it completes upload
//the file object argument has the following properties {name, size, type}
function onComplete(file){}

//fires when all files are done uploading, can be used to cleanup gui
function onAllComplete(){}

//fires when the server page echoes any return data
//the info object argument has the following properties {name, size, type, data}
function onReturnData(info){}

//fires when there is a file security or http error
error object argument has the following properties {name, size, type, error, errorType}
function onError(error){}

//fires when the user selects too many files
error object argument has the following properties {chosen, limit}
function onExceedsMaxFiles(error){}

//fires when there is a file security or http error
error object argument has the following properties {name, sizeK, exceededby, limit}
function onExceedsMaxFileSizeK(error){}

 var uploader = sb.upload({
	acceptedFileTypes: '*',
	serverSideScript : '../data/upload.php?s='+sb.cookies.get('PHPSESSID'),
	maxFiles : 10,
	maxFileSizeK : 1000000,
	eventListeners : {
		//each property must be a string reference to a function name, unfortunately you cannot use inline anonymous functions here - could also be properties of another object as string e.g. 'app.upload.onSelect'
	    onSelect : 'onSelect', 
		onOpen : 'onOpen', 
		onCancel : 'onCancel', 
		onFileCancel : 'onFileCancel', 
		onProgress : 'onProgress', 
		onAllProgress : 'onAllProgress', 
		onComplete : 'onComplete',
		onAllComplete : 'onAllComplete',
		onReturnData : 'onReturnData',
		onError : 'onError',
		onExceedsMaxFiles : 'onExceedsMaxFiles',
		onExceedsMaxFileSizeK : 'onExceedsMaxFileSizeK'
	}
});


//to cancel the uploads during progress use sb.upload.cancel(uploader); as the contructor returns the id of the current upload batch
*/
sb.upload = function(param){
	sb.consol.error(sb.messages[15]);
};

/**
@Name: sb.getBandwidth();
@Description: Used to get the bandwidth of the client in kpbs
@Param Object param An object which set the file type accepted, serveside script, passes data, sets max file size in K and max number of files.
@Example: 
sb.getBandwidth();
*/
sb.getBandwidth = function(){
	sb.consol.error(sb.messages[15]);
};

/**
@Name: sb.bandwidthTest
@Description: Handlers that fire when sb.flashGate.getBandwidth(); if fired
*/
sb.bandwidthTest = {
	
	/**
	@Name: sb.bandwidthTest.onComplete
	@Description: fires when an sb.stciker.getBandwidth test is complete and passes it one object with the following properties.
	o.kbps Integer The number of kilobytes per second
	*/
	onComplete : function(o){},
	
	/*
	o.kb Float The current number of kilobytes loads
	o.time Integer The current amount of time passed in milliseconds
	*/
	onProgress: function(o){
	}
};

/**
@Name: sb.flashGateLoaded
@Description: Fires when flashGate loads.  To make events fire after stciker loads push them into the sb.onFlashGateLoad array
*/
sb.flashGateLoaded = function(){
	
	window.setTimeout(
		function(){
		
			if(sb.flashGateInit === undefined){
				
				sb.getBandwidth = function(){
					sb.flashGate.getBandwidth();
				};
				
				sb.upload = function(params){
					return sb.flashGate.upload(params);
				};
				
				sb.upload.cancel = function(fileIndex){
					return sb.flashGate.cancelUpload(fileIndex);
				};
				
				sb.upload.cancelAll = function(){
					return sb.flashGate.cancelAllUploads();
				};
				
				sb.setFlashGateDebug = function(state){
					
					sb.flashGateDebug = state;
					sb.flashGate.setDebug(state);
				};
				
				sb.onFlashGateLoad.forEach(function(v){
					if(typeof v =='function'){v();}
				});
				
				sb.flashGateInit=1;
				
				if(sb.flashGateDebug ==1){
					
					sb.setFlashGateDebug(1);
				}
			}
	}, 5);
	
	
};

sb.flashGateInclude = function(){
	
	var surebertSwf = new sb.element({
		id : 'surebertSwf',
		tag : 'div',
		styles : {
			width : '1px',height : '1px'
		}
		
	});
	surebertSwf.appendToTop(document.body);
	
	if(sb.browser.agent =='ff'){
		
		if(window.screenX < 0){
			var screenX =(window.screenX*-1)+20;
			surebertSwf.mv((window.screenX*-1)+20,0,999);
		}
	}
	
	sb.swfBox = new sb.swf({
		src : sb.base+'/surebert.swf',
		width : 1,
		height : 1,
		bgColor :'#000000',
		wmode: 'transparent'
	});
	
	sb.swfBox.id = 'sb_flashGate';
	sb.flashGate = sb.swfBox.embed(surebertSwf);
	
	
};

/**
@Description: Check the surebert flashGate sound system by loading an mp3 from surebert.com
@Param string mp3 An optional paramter that allows you to specify the mp3 to play by url
*/
sb.soundCheck = function(mp3){
	var snd = new sb.sound(mp3 || 'http://surebert.com/song.mp3');
	snd.play();
	return snd;
};

sb.dom.onReady({
	id : 'body',
	onReady : function(){
		sb.flashGateInclude();
	},
	interval : 10,
	tries : 600
});