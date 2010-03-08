sb.include('flashGate');

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
		return sb.flashGate.getInterface().storage_engine_get(key);
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
		sb.flashGate.getInterface().storage_engine_set(key, val);
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
		sb.flashGate.getInterface().storage_engine_clear_all();
	}
};