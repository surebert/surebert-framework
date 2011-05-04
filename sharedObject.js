sb.include('flashGate');

/**
@Name: sb.sharedObject
@Author: Paul Visco
@Description: gives javascript access to the flash storage
*/
sb.sharedObject = {

	/**
	@Name: sb.sharedObject.load
	@Description: gets data from the sharedObject, alias for this.load for backwards compat
	@Param: string key The name of the stored data
	@Example:
	sb.sharedObject.load('friend');
	*/
	load : function(key){
		return this.get(key);
	},
	
	/**
	@Name: sb.sharedObject.get
	@Description: get data from the sharedObject
	@Param: string key The name of the stored data
	@Example:
	sb.sharedObject.get('friend');
	*/
	get : function(key){
		return sb.flashGate.getInterface().storage_engine_get(key);
	},
	
	/**
	@Name: sb.sharedObject.save
	@Description: saves data in the sharedObject
	@Param: string key The name of the stored data
	@Param: string val The value to store
	@Example:
	sb.sharedObject.set('friend', 'paul');
	*/
	set : function(key, val){
		return sb.flashGate.getInterface().storage_engine_set(key, val);
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
		return this.set(key, val);
	},
	
	/**
	@Name: sb.sharedObject.clear
	@Description: clears data for a specific key in the sharedObject
	@Param: string key The name of the stored data
	@Example:
	sb.sharedObject.clear('friend');
	*/
	clear : function(key){
		this.set(key, '');
	},
	
	/**
	@Name: sb.sharedObject.clearAll
	@Description: clears all data stored in the sharedObject
	@Example:
	sb.sharedObject.clearAll();
	*/
	clearAll : function(){
		sb.flashGate.getInterface().storage_engine_clear_all();
	},
	
	/**
	@Name: sb.sharedObject.typeOf
	@Description: returns the typeOf for easier integration with sb.storage
	@Example:
	sb.sharedObject.typeOf();
	*/
	typeOf : function(){
		return 'sb.sharedObject';
	}
};