if(typeof sb.swf =='undefined'){
	sb.include('swf');
}
/*
@Name: sb.uploadButton
@Description: Instantiates a new upload
@Example:
var uploader = new sb.uploadButton({
	debug : true,
	maxFiles : 5,
	maxFileSizeK : 5000000,
	url : 'http://frameworkdev.sv/uploads/test',
	data : {
		friend : 'tim',
		nano : "Hello there timmy's dog"
	},
	onReturnData : function(file){
		sb.objects.alert(file);
	},
	onBeforeBrowse : function(){
		return true;
	},
	onSelect : function(filenames){
		return true;
	},
	onExceedsMaxFiles : function(){},
	onExceedsMaxFileSizeK : function(file){},
	onError : function(data){
		alert(data.message);
	},
	styles : {
		backgroundColor : '0x00FF00',
		backgroundColorRoll : '0xFFFF00',
		borderColor : '0xFF0000',
		color : '0xFF0000',
		cornerRadius : '15',
		borderThickness : '0',
		fontSize : 16,
		width : 62,
		height : 24,
		fontSize : 16,
		font : 'Tahoma'
	}
});
uploader.embed('#chicken');
*/

sb.uploadButton = function(parameters){
	
	this.id = sb.uploadButton.uploads.length;
	
	for(var prop in parameters){
		this[prop] = parameters[prop];
	}
	
	sb.uploadButton.uploads.push(this);

	//create swf and associate call to thei sb.uploadButton for event handling
	
	this.swf = new sb.swf({
		//src : sb.base+"/UploadButton.swf?id="+this.id,
		src : "UploadButton.swf?debug=1&id="+this.id+"&i="+Math.random(),
		
		width : this.styles.width || 62,
		height : this.styles.height || 24,
		id : 'upload'+this.id,
		bgcolor : '#000000',
		wmode: 'transparent',
		flashvars : {
			debug : (this.debug != null) ? this.debug : true,
			innerHTML : this.innerHTML || 'upload'
		},
		version : 9,
		alt : parameters.alt || '<h1>You need <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash">flash player 9+</a> to upload</h1>'
	});

	this.load_params = function(){
	
		this.swf.getInterface().create_upload(this.id);
		
	};
};

	
/**
@Name: sb.uploadButton.uploads
@Description: Used Internally
*/
sb.uploadButton.uploads = [];

sb.uploadButton.prototype = {
	styles : {},
	
	/**
	 * Returns the HTML of the swf for testing
	 */
	toHTML : function(){
		return this.swf.toHTML();
	},
	
	/**
	 * Embeds the button into another element
	 */
	embed : function(el){
		this.swf.embed(el);
	},
	
	/**
	@Name: sb.uploadButton.prototype.setStyles
	@Description: Sets the MXML CSS styles for the button
	@Param: styles Object Hash of css properties
	@Example: 
		this.setStyles(
			letterSpacing : '20'
		);
	*/
	setStyles : function(styles){
		this.swf.getInterface().set_button_styles(styles);
	},

	/**
	@Name: sb.uploadButton.prototype.cancels
	@Description: Cancels all file uploads for this instance
	@Name: string name optionally cancels only for files that match the file name given
	*/
	cancel : function(name){
		name = name || '';
		this.swf.getInterface().upload_cancel(name);
	},
	
	/**
	@Name: sb.uploadButton.prototype.id
	@Description: Used Internally
	*/
	id: 0,
	
	/**
	@Name: sb.uploadButton.prototype.maxFiles
	@Description: The maximum number of files the user can select in the browser before it throws an error and fires onMaxFilesExceeded
	*/
	maxFiles : 5,
	
	/**
	@Name: sb.uploadButton.prototype.maxFileSizeK
	@Description: The maximum file size per file that the user can upload before it throws an error and fires onMaxFileSizeExceeded
	*/
	maxFileSizeK : 1024,

	/**
	@Name: sb.uploadButton.prototype.acceptedFileTypes
	@Description: The file types to accept for upload
	*/
	acceptedFileTypes : '*.*',

	/**
	@Name: sb.uploadButton.prototype.method
	@Description: The default method to send data
	*/
	method : 'post',
	
	/**
	@Name: sb.uploadButton.prototype.url
	@Description: The URL to upload the data to	
	*/
	url : '',
	
	/**
	@Name: sb.uploadButton.prototype.data
	@Description: Additional data objectw hich is url encoded into post data and sent with the files
	*/
	data : {},
	
	/**
	@Name: sb.uploadButton.prototype.debug
	@Description: Determines if file upload debug info is traced to the flash debug player
	*/
	debug : true,
	
	/**
	@Name: sb.uploadButton.prototype.onBeforeBrowse
	@Description: Fires when the user presses the browse button, but before the file browser opens
	@Return: boolean true opens file browser, false cancels file browser opening.  It does not fire oncancel, you can call it directly before issuing false return if you would like
	*/
	onBeforeBrowse : function(data){return true;},
	
	/**
	@Name: sb.uploadButton.prototype.onSelect
	@Description: Fires when the user selects files from the browse box that pops up from pressing the button
	@Param: array names the file names selected
	@Return: boolean true uploads, false cancels upload before it starts.  It does not fire oncancel, you can call it directly before issuing false return if you would like
	*/
	onSelect : function(data){return true;},
	
	/**
	@Name: sb.uploadButton.prototype.onExceedsMaxFileSizeK
	@Description: Fires when a file exceeds the maximum file size specified and is therefore not uplaoded
	@Param: object file file.name, file.size, file.sizeK, file.exceededBy, file.limit, file.message
	*/
	onExceedsMaxFileSizeK : function(file){},
	
	/**
	@Name: sb.uploadButton.prototype.onExceedsMaxFiles
	@Description: Fires when a user selects too many files
	@Param: object files.chosen, files.limit, files.message
	*/
	onExceedsMaxFiles : function(files){},
	
	/**
	@Name: sb.uploadButton.prototype.onError
	@Description: Fires if the upload is canceled due to an error
	@Param: object file.name, file.size, file.sizeK, file.type, file.error
	*/
	onError : function(file){},
	
	/**
	@Name: sb.uploadButton.prototype.onOpen
	@Description: Fires when the file is opened for upload on the client's computer
	@Param: object file.name, file.size, file.sizeK, file.type
	*/
	onOpen : function(file){},
	
	/**
	@Name: sb.uploadButton.prototype.onReturnData
	@Description: Fires when the data is returned from the server, , must beturn something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type, file.data
	*/
	onReturnData : function(file){},
	
	/**
	@Name: sb.uploadButton.prototype.onAllComplete
	@Description: Fires when all uploads for this upload instance are complete
	@Param: object files.total
	*/
	onAllComplete : function(files){},
	
	/**
	@Name: sb.uploadButton.prototype.onComplete
	@Description: Fires when a file is done uploading, must beturn something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type
	*/
	onComplete : function(files){},
	
	/**
	@Name: sb.uploadButton.prototype.onAllProgress
	@Description: Fires each time one more file is uploaded until the que is empty
	@Param: object files.total, files.remaining
	*/
	onAllProgress : function(files){},
	
	/**
	@Name: sb.uploadButton.prototype.onProgress
	@Description: Fires periodically as a file uploads alerting you of the progress in percent, deosn't seem to fire for really quick uploads on local server, must return something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type, file.bytesLoaded, file.bytesTotal, file.percent
	*/
	onProgress : function(files){},
	//files.remaining
	
	/**
	@Name: sb.uploadButton.prototype.onCancelBrowse
	@Description: Fires when the user hits cancel in the file browser
	*/
	onCancelBrowse : function(){},
	
	/**
	@Name: sb.uploadButton.prototype.onCancelAll
	@Description: Fires once when the upload que is canceled using upload.cancel();
	*/
	onCancelAll : function(){},
	
	/**
	@Name: sb.uploadButton.prototype.onCancelFile
	@Description: Fires when one file in the que is canceled by filename with upload.cancel(file.name); or once per file when upload.cancel()l is fired without a name specified
	@Param: object file.name
	*/
	onCancelFile : function(){}
		
};