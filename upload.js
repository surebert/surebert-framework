if(typeof sb.swf =='undefined'){
	sb.include('swf');
}
/*
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
	},
	styles : {
		backgroundAlpha : '0.5',
		backgroundColor : '#000000',
		borderColor: '#000000',
		color: '#FF000',
		fillAlphas: ['1.0', '1.0'],
		fillColors: ['#FF0000', '#FFBBBB', '#FF7777', '#FF8899'],
		themeColor: '#FF9C00',
		textRollOverColor: '#A47505',
		textSelectedColor: '#BC9C07',
		disabledColor: '#B1B3B3',
		cornerRadius : '120',
		letterSpacing : '5',
		width : 300,
		height : 400,
		fontSize : 20,
		src : '/media/bg.png'
	},
	embedIn : '#chicken',
});

*/

sb.upload = function(parameters){
	
	this.id = sb.upload.uploads.length;
	
	for(var prop in parameters){
		this[prop] = parameters[prop];
	}

	sb.upload.uploads.push(this);

	//create swf and associate call to thei sb.upload for event handling
	
	this.swf = new sb.swf({
		src : "surebert_uploader.swf?debug=1&id="+this.id+"&i="+Math.random(),
		width : this.styles.width || 64,
		height : this.styles.height || 22,
		id : 'upload'+this.id,
		bgcolor : '#000000',
		wmode: 'transparent',
		flashvars : {
			debug : this.debug || true,
			innerHTML : this.innerHTML || 'upload'
		}
	});
	this.swf.embed(parameters.embedIn || new sb.element({tag : 'span'}).appendTo('body'));
	
	var self = this;
	
	this.load_params = function(){
		
		self.flash().create_upload(self.id);
		if(self.disabled){
			self.disableButton();
		}
		
		if(self.styles){
			
			self.setStyles(self.styles);
		}
	};
};

	
/**
@Name: sb.upload.uploads
@Description: Used Internally
*/
sb.upload.uploads = [];

sb.upload.prototype = {
	styles : {},
	
	/**
	@Name: sb.upload.prototype.setStyles
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
	@Name: sb.upload.prototype.setStyles
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
	@Name: sb.upload.prototype.enableButton
	@Description: Sets the button state to enabled
	*/
	enableButton : function(){
		this.flash().enable_button();
	},

	/**
	@Name: sb.upload.prototype.disableButton
	@Description: Sets the button state to disabled
	*/
	disableButton : function(){
		this.flash().disable_button();
	},

	/**
	@Name: sb.upload.prototype.cancels
	@Description: Cancels all file uploads for this instance
	@Name: string name optionally cancels only for files that match the file name given
	*/
	cancel : function(name){
		name = name || '';
		this.flash().upload_cancel(name);
	},
	
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
	@Description: The file types to accept for upload
	*/
	acceptedFileTypes : '*.*',

	/**
	@Name: sb.upload.prototype.method
	@Description: The default method to send data
	*/
	method : 'post',
	
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