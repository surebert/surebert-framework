/**
@Name: sb.forms.textarea.allowTabs
@Description: Allows the user to use tabs in a textarea
@Param: String textarea The id or a reference to the textarea which you want to allow tabs on.

@Example:
	s$('#myTextArea').events({
		keydown :  sb.forms.textarea.allowTabs
	});
*/

sb.include('forms.textarea');

sb.forms.textarea.allowTabs = function(e){
	var sel = sb.forms.textarea.getSelection(this), textarea = this, scrollTo;

	if(e.keyCode == 9){
	
			scrollTo = this.scrollTop;
			window.setTimeout(function(){
				textarea.value = sel.before + unescape('%09') + sel.after;	
				sb.forms.textarea.moveCaret(textarea, sel.end+1);
				textarea.focus();
			}, 0);
			setTimeout(function(){
				textarea.scrollTop =  scrollTo;
			}, 10);
	
		return false;
	}
};