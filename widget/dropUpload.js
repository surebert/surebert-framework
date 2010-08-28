/**
@Name: sb.widget.dropUpload
@Author: Paul Visco v1.0 8/27/2010
@Description: Allows for multifile drag drag upload from the desktop.  Works only in Firefox 3.6+ and chrome 5+
@Param: object o params that setup uploader, see property notes for more info
@Example:
var myUploader = new sb.widget.dropUpload({
	url : '/test/upload2',
	target : '#pad',
	allowedFilePatterns : /\.(jpg|txt|png|pdf|zip|flv)$/i,
	lis : [],
	onDropFiles : function(e){},
	onDropFile : function(file){
		this.lis[file.index] = new sb.element({
			tag : 'li',
			innerHTML : file.name
		});
		this.lis[file.index].appendTo(this.target);
		this.lis[file.index].styles({
			backgroundColor : 'rgb(255,0,0)',
			color : '#fff',
			padding: '5px'
		});

		this.lis[file.index].percentage = new sb.element({
			tag : 'span',
			innerHTML : '0%',
			styles : {
				paddingLeft : '5px'
			}
		});
		this.lis[file.index].percentage.appendTo(this.lis[file.index]);
	},
	onFileUploaded : function(file, response){

		this.lis[file.index].percentage.innerHTML = 'DONE!';
	},
	onUploadProgress : function(file, percentage){

		this.lis[file.index].percentage.innerHTML = percentage+'%';
		this.lis[file.index].styles({
			backgroundColor : 'rgb(0,'+percentage*2+',0)'
		});
	},
	onDownloadProgress : function(file, percent){

	},
	onDragEnter : function(e){

	},
	onDragOver : function(e){

	},
	onNonAllowedFilePattern : function(e){

	}
});
myUploader.init();
*/
sb.widget.dropUpload = function(o){
	sb.objects.infuse(o, this);
	this.target = $(this.target);
};

/**
@Name: sb.widget.dropUpload.files
@Description: used internally to calculate files index
*/
sb.widget.dropUpload.files = 1;

sb.widget.dropUpload.prototype = {
	/**
	@Name: sb.widget.dropUpload.prototype.onNonAllowedFilePattern
	@Description: fires when file name does match allowed pattern.  Upload of that file is canceled when this fires
	*/
	onNonAllowedFilePattern : function(file){},

	/**
	@Name: sb.widget.dropUpload.prototype.onUploadProgress
	@Description: fires on UploadProgress
	@Params:
	file {index string, name string, size int}
	percentage int
	*/
	onUploadProgress : function(file, percentage){},

	/**
	@Name: sb.widget.dropUpload.prototype.onDownloadProgress
	@Description: fires on onDownloadProgress
	@Params:
	file {index string, name string, size int}
	percentage int
	*/
	onDownloadProgress : function(file, percentage){},

	/**
	@Name: sb.widget.dropUpload.prototype.onDropFile
	@Description: fires once for each file
	@Params:
	file {index string, name string, size int}
	percentage int
	*/
	onDropFile : function(file){},
	/**
	@Name: sb.widget.dropUpload.prototype.onDropFiles
	@Description: fires when files are dropped
	@Params:
	e {total int} and other properties
	*/
	onDropFiles : function(e){},

	/**
	@Name: sb.widget.dropUpload.prototype.onDragOver
	@Description: fires on onDragOver
	@Params:
	e event from the dragover event
	*/
	onDragOver : function(e){},

	/**
	@Name: sb.widget.dropUpload.prototype.onDragEnter
	@Description: fires on onDragEnter
	@Params:
	e event from the dragenter event
	*/
	onDragEnter : function(e){},

	/**
	@Name: sb.widget.dropUpload.prototype.onDropHandler
	@Description: Used internally
	*/
	onDropHandler : function(e){
		var self = this,i = 0;
		e.preventDefault();
		e.stopPropagation();
		e.total = e.dataTransfer.files.length;
		self.onDropFiles(e);


		var files = sb.toArray(e.dataTransfer.files);

		files.forEach(function(file){
			file.index = sb.widget.dropUpload.files++;

			
			if(!file.name.match(self.allowedFilePatterns)){
				self.onNonAllowedFilePattern(file);
				return;
			};

			self.onDropFile(file);

			var xhr = new XMLHttpRequest;
			xhr.upload.addEventListener("progress", function(e) {
				if (e.lengthComputable) {
					var percentage = Math.round((e.loaded * 100) / e.total);
					self.onUploadProgress(file, percentage);
				}
			}, false);
			xhr.addEventListener("progress", function(e) {
				if (e.lengthComputable) {
					var percentage = Math.round((e.loaded * 100) / e.total);
					self.onDownloadProgress(file, percentage);
				}
			}, false);

			xhr.open('post', self.url, true);

			xhr.onreadystatechange = function () {
				if (this.readyState != 4) {return;}
				self.onUploadProgress(file, 100);
				self.onFileUploaded(file, this.responseTxt);
			}

			xhr.setRequestHeader('X_FILE_NAME', file.fileName);
			xhr.setRequestHeader('X_FILE_SIZE', file.fileSize);
			xhr.send(file);

		});
	},

	/**
	@Name: sb.widget.dropUpload.prototype.addEvents
	@Description: Used internally
	*/
	addEvents : function(){
		var self = this;
		this.target.addEventListener("dragenter",  function(e){

			e.preventDefault();
			e.dataTransfer.dropEffect = 'copy';
			 self.onDragEnter(e);
			return false;
		}, false);
		this.target.addEventListener("dragover", function(e){

			e.preventDefault();
			self.onDragOver(e);
			return false;
		}, false);
		this.target.addEventListener("drop", function(e){
			e.files = e.dataTransfer.files;
			self.onDropHandler(e);
		}, false);

	},

	/**
	@Name: sb.widget.dropUpload.prototype.init
	@Description: initializes the events
	*/
	init : function(){
		this.addEvents();
	}
};

