/**
@Name: sb.flashGate
@Description: Used to include swf for surebert communicate with flash player for sound, multi-file/progress upload and storage.  If you want to use one of these functions on page load, (e.g. play music) make sure to wrap it in the function sb_onFlashGateLoaded so that it fires once sb.flashGate has loaded.
@Author: Paul Visco
@Version: 4.2 02/12/06 09/03/08

*/

if(typeof sb.swf =='undefined'){
	sb.include('swf');
}

sb.swfBox = new sb.swf({
	src : sb.base+"/Surebert.swf",
	width : 1,
	height : 1,
	bgColor :'#FF0000',
	id : 'Flashgate',
	wmode: '',
	flashvars : {
		debug : true
	}
});

if(sb.browser.ie6){
	document.write(sb.swfBox.toHTML());
} else {
	sb.flashGateContainer = new sb.element({
		tag : 'x',
		innerHTML : sb.swfBox.toHTML(),
		style : {
			display : 'block',
			position : 'absolute',
			left : '-200px',
			top : '-200px'
		}
	}).appendTo('body');
}

/**
 * @Name sb.flashGate
 * @Description: Used Internally - The connection to the swf
 */
sb.flashGate = $('#Flashgate');

/**
 * @Name sb_onFlashGateLoad
 * @Description: Used Internally
 */
sb_onFlashGateLoad = function(){
	
	if(typeof sb_onFlashGateLoaded == 'function'){
		sb_onFlashGateLoaded();
	}
};

/**
@Name: sb.sound
@Author: Paul Visco
@Description: A constructor for creating new sound object instances.  Allows javascript to load, play and stop mp3 sounds.
@Param String url The url of the file to play
@Example:
var yellow = new sb.sound(
	url : 'yellow.mp3',
	debug : true,
	onID3 : function(){},
	onProgress : function(){}
);
yellow.play();
*/
sb.sound = function(params){
	if(!params.url){
		throw('You must pass a url to the sb.sound');
	}
	
	for(var prop in params){
		this[prop] = params[prop];
	}
	
	this.id = sb.flashGate.sound_create(this.url, this.debug);
	sb.sound.sounds[this.id] = this;
};

/**
@Name: sb.sound.sounds
@Description: Used Internally
*/
sb.sound.sounds = [];

/**
@Name: sb.sound.stopAll
@Description: Stops all sounds playing on the page
@Param String url Optional The url of the file to stop
@Example:
sb.sound.stopAll();
//or
sb.sound.stopAll('yellow.mp3');
*/
sb.sound.stopAll = function(url){
	url = url || '';
	sb.flashGate.sounds_stop_all(url);
};

/**
@Name: sb.sound.stopAll
@Description: Sets the global volume of all sounds
@Param Float A float between 0 and 1
@Example:
sb.sound.setGlobalVolume(0.5);
*/

sb.sound.setGlobalVolume = function(volume){
	sb.flashGate.sounds_set_global_volume(volume);
};

/**
@Name: sb.sound.muteAll
@Description: Mutes all sounds playing on the page
@Param String url Optional The url of the file to mute
@Example:
sb.sound.muteAll();
//or
sb.sound.muteAll('yellow.mp3');
*/
sb.sound.muteAll = function(){
	sb.flashGate.sounds_mute_all();
};

/**
@Name: sb.sound.prototype
@Description: The methods of sb.sound instances
*/
sb.sound.prototype = {
	/**
	@Name: sb.sound.prototype.url
	@Description: String The url of the mp3 file
	*/
	url : '',
	
	/**
	@Name: sb.sound.prototype.id
	@Description: Used Internally
	*/
	id : 0,
	
	/**
	@Name: sb.sound.prototype.play
	@Param Number position The position to start the file at in milliseconds 
	@Param Number loops The number of times to repeat the sound
	@Description: Plays the sound file
	@Example:
	mySound.play();
	*/
	play : function(position, loops){
		position = position || 0;
		loops = loops || 0;
		return sb.flashGate.sound_play(this.id, position, loops);
	},
	
	/**
	@Name: sb.sound.prototype.stop
	@Description: Stops the sound file
	@Example:
	mySound.play();
	*/
	stop : function(){
		return sb.flashGate.sound_stop(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.getPosition
	@Description: Gets the current position in milliseconds
	@Return: Number return the current position in milliseconds
	@Example:
	mySound.getPosition();
	*/
	getPosition : function(){
		return sb.flashGate.sound_get_position(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.setPosition
	@Description: Moves the playhead to a certain position in milliseconds
	@Example:
	mySound.setPosition(4135);
	*/
	setPosition : function(position){
		return sb.flashGate.sound_set_position(this.id, position);
	},
	
	/**
	@Name: sb.sound.prototype.getVolume
	@Description: Gets the current volume 
	@Return: float between 0 and 1
	@Example:
	mySound.getVolume();
	*/
	getVolume : function(volume){
		return sb.flashGate.sound_get_volume(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.getVolume
	@Description: Gets the current volume 
	@Param: Float volume between 0 and 1
	@Example:
	mySound.setVolume(0.5);
	*/
	setVolume : function(volume){
		sb.flashGate.sound_set_volume(this.id, volume);
	},
	
	/**
	@Name: sb.sound.prototype.getPan
	@Description: Gets the current pan position
	@Return: float between -1 (left) and 1 (right)
	@Example:
	mySound.getPan();
	*/
	getPan : function(){
		return sb.flashGate.sound_get_pan(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.setPan
	@Description: sets the current pan position
	@Param: float pan between -1 (left) and 1 (right)
	@Example:
	mySound.setPan(0.5);
	*/
	setPan : function(pan){
		sb.flashGate.sound_set_pan(this.id, pan);
	},
	
	/**
	@Name: sb.sound.prototype.mute
	@Description: sets the volume to zero for this sound but keeps playing
	@Example:
	mySound.mute();
	*/
	mute : function(){
		this.setVolume(0);
	},
	//tags.album, tags.year, tags.artist, tags.songName, tags.comment, tags.track, tags.genre
	onID3 : function(){},
	//song.sizeK, song.bytesLoaded, song.bytesTotal
	onLoad : function(){},
	//message
	onError : function(){}
};

/**
@Name: sb.sharedObject
@Author: Paul Visco
@Description: gives javascript access to the flash storage
*/
sb.sharedObject = {

	/**
	@Name: sb.sharedObject.load
	@Description: loads data from the sharedObject
	@Param: string key The name of the stored data
	@Example:
	sb.sharedObject.load('friend');
	*/
	load : function(key){
		return sb.flashGate.storage_engine_get(key);
	},
	
	/**
	@Name: sb.sharedObject.save
	@Description: saves data in the sharedObject
	@Param: string key The name of the stored data
	@Param: string val The value to store
	@Example:
	sb.sharedObject.save('friend', 'paul');
	*/
	save : function(key, val){
		sb.flashGate.storage_engine_set(key, val);
	},
	
	/**
	@Name: sb.sharedObject.clear
	@Description: clears data for a specific key in the sharedObject
	@Param: string key The name of the stored data
	@Example:
	sb.sharedObject.clear('friend');
	*/
	clear : function(key){
		this.save(key, '');
	},
	
	/**
	@Name: sb.sharedObject.clearAll
	@Description: clears all data stored in the sharedObject
	@Example:
	sb.sharedObject.clearAll();
	*/
	clearAll : function(){
		sb.flashGate.storage_engine_clear_all();
	}
};

/**
@Name: sb.upload
@Description: Instantiates a new upload
@Example:
var uploader = new sb.upload({
	debug : true,
	maxFiles : 5,
	maxFileSizeK : 5000000,
	url : 'http://framework.sv/post',
	data : {
		friend : 'tim',
		nano : "Hello there timmy's dog"
	},
	onReturnData : function(file){},
	onExceedsMaxFiles : function(){},
	onExceedsMaxFileSizeK : function(file){},
	onError : function(data){
		alert(data.message);
	}
});

uploader.browse();
*/
sb.upload = function(parameters){
	this.id = sb.upload.uploads.length;
	
	for(var prop in parameters){
		this[prop] = parameters[prop];
	}
	
	sb.upload.uploads.push(this);
};

/**
@Name: sb.upload.uploads
@Description: Used Internally
*/
sb.upload.uploads = [];

/**
@Name: sb.upload.cancel
@Description: Cancels any upload currently in process in any sb.upload instance
*/
sb.upload.cancel = function(name){
	name = name || '';
	sb.flashGate.upload_cancel_all(name);
};

sb.upload.prototype = {
	/**
	@Name: sb.upload.prototype.id
	@Description: Used Internally
	*/
	id: 0,
	
	/**
	@Name: sb.upload.prototype.maxFiles
	@Description: The maximum number of files the user can select in the browser before it throws an error and fires onMaxFilesExceeded
	*/
	maxFiles : 5,
	
	/**
	@Name: sb.upload.prototype.maxFileSizeK
	@Description: The maximum file size per file that the user can upload before it throws an error and fires onMaxFileSizeExceeded
	*/
	maxFileSizeK : 1024,
	
	/**
	@Name: sb.upload.prototype.acceptedFileTypes
	@Description: The accepted file types/names as a string e.g. '*.jpg;*.png;'
	*/
	acceptedFileTypes : '*',
	
	/**
	@Name: sb.upload.prototype.url
	@Description: The URL to upload the data to	
	*/
	url : '',
	
	/**
	@Name: sb.upload.prototype.data
	@Description: Additional data objectw hich is url encoded into post data and sent with the files
	*/
	data : {},
	
	/**
	@Name: sb.upload.prototype.debug
	@Description: Determines if file upload debug info is traced to the flash debug player
	*/
	debug : true,
	
	/**
	@Name: sb.upload.prototype.browse
	@Description: Starts the file upload by prompting the user with a file browse box
	*/
	browse : function(){
	
		var parameters = {};
		
		for(var prop in this){
		
			if(typeof this[prop] == 'function' && prop.match(/^on/)){
				
				parameters[prop] = 'sb.upload.uploads['+this.id+'].'+prop;
				
			} else if(['maxFiles', 'maxFileSizeK', 'url', 'data', 'debug', 'acceptedFileTypes'].inArray(prop)){
				parameters[prop] = this[prop];
			}
		}
		
		sb.flashGate.upload_browse(parameters);
	},
	
	/**
	@Name: sb.upload.prototype.cancels
	@Description: Cancels all file uploads for this instance
	@Name: string name optionally cancels only for files that match the file name given
	*/
	cancel : function(name){
		name = name || '';
		sb.flashGate.upload_cancel(this.id, name);
	},
	
	/**
	@Name: sb.upload.prototype.onSelect
	@Description: Fires when the user selects files from the browse box that pops up when you begin an upload
	@Param: object files.total
	*/
	onSelect : function(files){},
	
	/**
	@Name: sb.upload.prototype.onExceedsMaxFileSizeK
	@Description: Fires when a file exceeds the maximum file size specified and is therefore not uplaoded
	@Param: object file file.name, file.size, file.sizeK, file.exceededBy, file.limit, file.message
	*/
	onExceedsMaxFileSizeK : function(file){},
	
	/**
	@Name: sb.upload.prototype.onExceedsMaxFiles
	@Description: Fires when a user selects too many files
	@Param: object files.chosen, files.limit, files.message
	*/
	onExceedsMaxFiles : function(files){},
	
	/**
	@Name: sb.upload.prototype.onError
	@Description: Fires if the upload is canceled due to an error
	@Param: object file.name, file.size, file.sizeK, file.type, file.error
	*/
	onError : function(file){},
	
	/**
	@Name: sb.upload.prototype.onOpen
	@Description: Fires when the file is opened for upload on the client's computer
	@Param: object file.name, file.size, file.sizeK, file.type
	*/
	onOpen : function(file){},
	
	/**
	@Name: sb.upload.prototype.onReturnData
	@Description: Fires when the data is returned from the server, , must beturn something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type, file.data
	*/
	onReturnData : function(file){},
	
	/**
	@Name: sb.upload.prototype.onAllComplete
	@Description: Fires when all uploads for this upload instance are complete
	@Param: object files.total
	*/
	onAllComplete : function(files){},
	
	/**
	@Name: sb.upload.prototype.onComplete
	@Description: Fires when a file is done uploading, must beturn something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type
	*/
	onComplete : function(files){},
	
	/**
	@Name: sb.upload.prototype.onAllProgress
	@Description: Fires each time one more file is uploaded until the que is empty
	@Param: object files.total, files.remaining
	*/
	onAllProgress : function(files){},
	
	/**
	@Name: sb.upload.prototype.onProgress
	@Description: Fires periodically as a file uploads alerting you of the progress in percent, deosn't seem to fire for really quick uploads on local server, must return something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type, file.bytesLoaded, file.bytesTotal, file.percent
	*/
	onProgress : function(files){},
	//files.remaining
	
	/**
	@Name: sb.upload.prototype.onCancelBrowse
	@Description: Fires when the user hits cancel in the file browser
	*/
	onCancelBrowse : function(){},
	
	/**
	@Name: sb.upload.prototype.onCancelAll
	@Description: Fires once when the upload que is canceled using upload.cancel();
	*/
	onCancelAll : function(){},
	
	/**
	@Name: sb.upload.prototype.onCancelFile
	@Description: Fires when one file in the que is canceled by filename with upload.cancel(file.name); or once per file when upload.cancel()l is fired without a name specified
	@Param: object file.name
	*/
	onCancelFile : function(){}
		
};