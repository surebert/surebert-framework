/**
@Name: sb.debugwin
@Description: Write debug information to a pop up window
@Param: String data The data to write
@Param: String backgroundColor The background color to write behind the data message
@Example:
sb.debugWin('hello world error', 'red');
*/
sb.debugwin = function(data, backgroundColor){
	backgroundColor = backgroundColor || 'green';
	if(!this.win){
		this.win = window.open ("", "mywindow1","status=1,width=400,height=700,scrollbars=yes");
		this.win.document.write('<h1>debugger open</h1>');
		
	}
	
	this.win.document.write('<pre style="background-color:'+backgroundColor+';color:white;padding:10px;display:block;width:100%;">'+data+'</pre>');  
};