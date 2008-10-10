if(typeof sb.swf =='undefined'){
	sb.include('swf');
}
/*
@Name: sb.upload_button
@Description: Instantiates a new upload
@Example:
var uploader = new sb.upload_button({
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
	onExceedsMaxFiles : function(){},
	onExceedsMaxFileSizeK : function(file){},
	onError : function(data){
		alert(data.message);
	},
	embedIn : '#chicken',
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

*/

sb.upload_button = function(parameters){
	
	this.id = sb.upload_button.uploads.length;
	
	for(var prop in parameters){
		this[prop] = parameters[prop];
	}
	
	sb.upload_button.uploads.push(this);

	//create swf and associate call to thei sb.upload_button for event handling
	
	this.swf = new sb.swf({
		src : sb.base+"/UploadButton.swf?id="+this.id,
		width : this.styles.width || 62,
		height : this.styles.height || 24,
		id : 'upload'+this.id,
		bgcolor : '#000000',
		wmode: 'transparent',
		flashvars : {
			debug : (this.debug != null) ? this.debug : true,
			innerHTML : this.innerHTML || 'upload'
		}
	});

	
	//this.swf.embed(parameters.embedIn || new sb.element({tag : 'span'}).appendTo('body'));
	
	var self = this;

	this.toHTML = function(){
		return self.swf.toHTML();
	};
	this.embed = function(id){
		self.swf.embed(id);
	};
	
	this.load_params = function(){
		
		self.flash().create_upload(self.id);
		
		/*
		if(self.disabled){
			self.disableButton();
		}
		
		if(self.styles){
			
			self.setStyles(self.styles);
		}*/
	};
};

	
/**
@Name: sb.upload_button.uploads
@Description: Used Internally
*/
sb.upload_button.uploads = [];

sb.upload_button.prototype = {
	styles : {},
	
	/**
	@Name: sb.upload_button.prototype.setStyles
	@Description: Used Internally - gets the reference to the flash movie
	*/
	flash : function(){
		var movieName = 'upload'+this.id;
		if (navigator.appName.indexOf("Microsoft") != -1) {
            return window[movieName];
        } else {
        	return document.getElementById(movieName);
        }
	
	},
	
	/**
	@Name: sb.upload_button.prototype.setStyles
	@Description: Sets the MXML CSS styles for the button
	@Param: styles Object Hash of css properties
	@Example: 
		this.setStyles(
			letterSpacing : '20'
		);
	*/
	setStyles : function(styles){
		this.flash().set_button_styles(styles);
	},

	/**
	@Name: sb.upload_button.prototype.cancels
	@Description: Cancels all file uploads for this instance
	@Name: string name optionally cancels only for files that match the file name given
	*/
	cancel : function(name){
		name = name || '';
		this.flash().upload_cancel(name);
	},
	
	/**
	@Name: sb.upload_button.prototype.id
	@Description: Used Internally
	*/
	id: 0,
	
	/**
	@Name: sb.upload_button.prototype.maxFiles
	@Description: The maximum number of files the user can select in the browser before it throws an error and fires onMaxFilesExceeded
	*/
	maxFiles : 5,
	
	/**
	@Name: sb.upload_button.prototype.maxFileSizeK
	@Description: The maximum file size per file that the user can upload before it throws an error and fires onMaxFileSizeExceeded
	*/
	maxFileSizeK : 1024,

	/**
	@Name: sb.upload_button.prototype.acceptedFileTypes
	@Description: The file types to accept for upload
	*/
	acceptedFileTypes : '*.*',

	/**
	@Name: sb.upload_button.prototype.method
	@Description: The default method to send data
	*/
	method : 'post',
	
	/**
	@Name: sb.upload_button.prototype.url
	@Description: The URL to upload the data to	
	*/
	url : '',
	
	/**
	@Name: sb.upload_button.prototype.data
	@Description: Additional data objectw hich is url encoded into post data and sent with the files
	*/
	data : {},
	
	/**
	@Name: sb.upload_button.prototype.debug
	@Description: Determines if file upload debug info is traced to the flash debug player
	*/
	debug : true,
	
	/**
	@Name: sb.upload_button.prototype.onSelect
	@Description: Fires when the user selects files from the browse box that pops up when you begin an upload
	@Param: object files.total
	*/
	onSelect : function(files){},
	
	/**
	@Name: sb.upload_button.prototype.onExceedsMaxFileSizeK
	@Description: Fires when a file exceeds the maximum file size specified and is therefore not uplaoded
	@Param: object file file.name, file.size, file.sizeK, file.exceededBy, file.limit, file.message
	*/
	onExceedsMaxFileSizeK : function(file){},
	
	/**
	@Name: sb.upload_button.prototype.onExceedsMaxFiles
	@Description: Fires when a user selects too many files
	@Param: object files.chosen, files.limit, files.message
	*/
	onExceedsMaxFiles : function(files){},
	
	/**
	@Name: sb.upload_button.prototype.onError
	@Description: Fires if the upload is canceled due to an error
	@Param: object file.name, file.size, file.sizeK, file.type, file.error
	*/
	onError : function(file){},
	
	/**
	@Name: sb.upload_button.prototype.onOpen
	@Description: Fires when the file is opened for upload on the client's computer
	@Param: object file.name, file.size, file.sizeK, file.type
	*/
	onOpen : function(file){},
	
	/**
	@Name: sb.upload_button.prototype.onReturnData
	@Description: Fires when the data is returned from the server, , must beturn something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type, file.data
	*/
	onReturnData : function(file){},
	
	/**
	@Name: sb.upload_button.prototype.onAllComplete
	@Description: Fires when all uploads for this upload instance are complete
	@Param: object files.total
	*/
	onAllComplete : function(files){},
	
	/**
	@Name: sb.upload_button.prototype.onComplete
	@Description: Fires when a file is done uploading, must beturn something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type
	*/
	onComplete : function(files){},
	
	/**
	@Name: sb.upload_button.prototype.onAllProgress
	@Description: Fires each time one more file is uploaded until the que is empty
	@Param: object files.total, files.remaining
	*/
	onAllProgress : function(files){},
	
	/**
	@Name: sb.upload_button.prototype.onProgress
	@Description: Fires periodically as a file uploads alerting you of the progress in percent, deosn't seem to fire for really quick uploads on local server, must return something from the serer for this to fire, can be a simple space
	@Param: object file.name, file.size, file.sizeK, file.type, file.bytesLoaded, file.bytesTotal, file.percent
	*/
	onProgress : function(files){},
	//files.remaining
	
	/**
	@Name: sb.upload_button.prototype.onCancelBrowse
	@Description: Fires when the user hits cancel in the file browser
	*/
	onCancelBrowse : function(){},
	
	/**
	@Name: sb.upload_button.prototype.onCancelAll
	@Description: Fires once when the upload que is canceled using upload.cancel();
	*/
	onCancelAll : function(){},
	
	/**
	@Name: sb.upload_button.prototype.onCancelFile
	@Description: Fires when one file in the que is canceled by filename with upload.cancel(file.name); or once per file when upload.cancel()l is fired without a name specified
	@Param: object file.name
	*/
	onCancelFile : function(){}
		
};