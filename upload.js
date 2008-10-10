
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