sb.include('flashGate');
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
	
	this.id = sb.flashGate.getInterface().sound_create(this.url, this.debug);
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
	sb.flashGate.getInterface().sounds_stop_all(url);
};

/**
@Name: sb.sound.stopAll
@Description: Sets the global volume of all sounds
@Param Float A float between 0 and 1
@Example:
sb.sound.setGlobalVolume(0.5);
*/

sb.sound.setGlobalVolume = function(volume){
	sb.flashGate.getInterface().sounds_set_global_volume(volume);
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
	sb.flashGate.getInterface().sounds_mute_all();
};

/**
@Name: sb.sound.prototype
@Description: The methods of sb.sound instances
*/
sb.sound.prototype = {

	duration : -1,
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
		return sb.flashGate.getInterface().sound_play(this.id, position, loops);
	},
	
	/**
	@Name: sb.sound.prototype.stop
	@Description: Stops the sound file
	@Example:
	mySound.play();
	*/
	stop : function(){
		return sb.flashGate.getInterface().sound_stop(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.getPosition
	@Description: Gets the current position in milliseconds
	@Return: Number return the current position in milliseconds
	@Example:
	mySound.getPosition();
	*/
	getPosition : function(){
		return sb.flashGate.getInterface().sound_get_position(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.setPositionPercent
	@Description: Moves the playhead to a certain position in percent of total
	@Example:
	mySound.setPositionPercent(40);
	*/
	setPositionPercent : function(percent){
		return sb.flashGate.getInterface().sound_set_position_percent(this.id, percent);
	},

	/**
	@Name: sb.sound.prototype.getPositionPercent
	@Description: Gets the current position in percent of total
	@Return: Number return the current position in percent of total
	@Example:
	mySound.getPositionPercent();
	*/
	getPositionPercent : function(){
		return sb.flashGate.getInterface().sound_get_position_percent(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.setPosition
	@Description: Moves the playhead to a certain position in milliseconds
	@Example:
	mySound.setPosition(4135);
	*/
	setPosition : function(position){
		return sb.flashGate.getInterface().sound_set_position(this.id, position);
	},
	
	/**
	@Name: sb.sound.prototype.getVolume
	@Description: Gets the current volume 
	@Return: float between 0 and 1
	@Example:
	mySound.getVolume();
	*/
	getVolume : function(volume){
		return sb.flashGate.getInterface().sound_get_volume(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.getVolume
	@Description: Gets the current volume 
	@Param: Float volume between 0 and 1
	@Example:
	mySound.setVolume(0.5);
	*/
	setVolume : function(volume){
		sb.flashGate.getInterface().sound_set_volume(this.id, volume);
	},
	
	/**
	@Name: sb.sound.prototype.getPan
	@Description: Gets the current pan position
	@Return: float between -1 (left) and 1 (right)
	@Example:
	mySound.getPan();
	*/
	getPan : function(){
		return sb.flashGate.getInterface().sound_get_pan(this.id);
	},
	
	/**
	@Name: sb.sound.prototype.setPan
	@Description: sets the current pan position
	@Param: float pan between -1 (left) and 1 (right)
	@Example:
	mySound.setPan(0.5);
	*/
	setPan : function(pan){
		sb.flashGate.getInterface().sound_set_pan(this.id, pan);
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
	onError : function(){},
	//song.position, song.length, song.percent
	onProgress : function(data){}
};