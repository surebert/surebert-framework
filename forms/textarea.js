/**
@Name: sb.forms.textarea
@Description: Used to manipuate textarea elements on HTML forms
*/
sb.forms.textarea = {
	/**
	@Name: sb.forms.textarea.getSelection
	@Description: Returns the text selection in a textarea or text input
	@Param: String field The id or a reference to the textarea or text input.  Can be an obj reference or a string such as '#myField'.
	
	@Return: Object An object with selection data properties as follows
	begin - the character position of the beginning of the selection
	end - the character position of the end of the selection
	caret - the character position of the caret
	before - a string representing all text before the selection
	selected - a string representing all text in the selection
	after - a string represenitng all text after the selection
	
	@Example:
	<textarea id="myTextArea">surebert toolkit rocks</textarea>
	
	$('#myTextArea').evt('mouseup', function(e){
		var sel = sb.forms.textarea.getSelection(this);
	});

	//if the string toolkit was selected by the user with the mouse and then they let go of the mouse button sel would be defined as this in the event handler above
	
	sel = {
		begin : 9,
		end : 16,
		caret : 9,
		before : 'surebert ',
		selected : 'toolkit',
		after : ' rocks'
	}
	*/
	getSelection : function(field){
		field = sb.$(field);
		var range,sel={},selectionEnd,selectionStart,stored_range;
		field.focus(); 
		
		if (document.selection) {
			range = document.selection.createRange();
			
			stored_range = range.duplicate();
			try{
				stored_range.moveToElementText(field);
			}catch(e){}
			
			stored_range.setEndPoint( 'EndToEnd', range );
			selectionStart = stored_range.text.length - range.text.length;
			
			selectionEnd = selectionStart + range.text.length;
			sel.begin = selectionStart;
			sel.end = selectionEnd;
	
		} else if(typeof field.selectionStart !='undefined'){
	
			sel.begin = field.selectionStart;
			sel.end = field.selectionEnd;
		} 
	
		sel.caret = sel.begin;
		sel.before = field.value.substr(0, sel.begin);
		sel.selected = field.value.substr(sel.begin, (sel.end - sel.begin));
		sel.after = field.value.substr(sel.end, (field.value.length - sel.end));
		return sel;
	},
	
	
	/**
	@Name: sb.forms.textarea.addTags
	@Description: Add tags before and after the selection in a text area
	@Param: String field The id or a reference to the textarea or text input.  Can be an obj reference or a string such as '#myField'.
	@Param: String beginTag The tag to be added to the beginning of the user text selection
	@Param: String endtag The tag to be added to the end of the user text selection
	@Example:
		sb.forms.textarea.addTags('#myField', '[b]', '[/b]');
		//would change the text field by putting the begin and end tags around wha the user selected e.g. surebert toolkit rocks.  If toolkit was selected and this method was run, it would turn into surebert [b]toolkit[/b] rocks.
	*/
	addTags : function(field, beginTag, endTag){
		field = sb.$(field);
		var sel = sb.forms.textarea.getSelection(field); 
		var tagLength = beginTag.length +endTag.length;
		field.value = sel.before + beginTag + sel.selected + endTag + sel.after;
		window.setTimeout(function(){
			field.focus();
			sb.forms.textarea.moveCaret(field, sel.caret);
		}, 0);
	},
	
	
	/**
	@Name: sb.forms.textarea.setSelection
	@Description: Forces a selection of a certain substring in a text area
	@Param: String field The id or a reference to the textarea or text input.  Can be an obj reference or a string such as '#myField'.
	@Param: String start The start character position of the selection, character position starts at 0.
	@Param: String end The end position of the selection.
	@Example:
		sb.forms.textarea.setSelection('#myTextArea', 0, 5);
		//forces characters 0-5 to be selected in the form field specified
	*/
	setSelection : function(field, start, end) {
		var range;
		field = sb.$(field);
		if (field.setSelectionRange) {
			field.setSelectionRange(start, end);
		} else {
			range = field.createTextRange();
			range.collapse(true);
			range.moveStart("character", start);
			range.moveEnd("character", end - start);
			range.select();
		}
	},
	
	
	/**
	@Name: sb.forms.textarea.replaceSelection
	@Description: replaces a selection with another text string
	@Param: String field The id or a reference to the textarea or text input.  Can be an obj reference or a string such as '#myField'.
	@Param: String txt The new string which will replace the current selection

	@Example:
		sb.forms.textarea.replaceSelection('#myTextArea', 'newString');
		//replaces the current selection with the string 'newString' and moves the caret to the end of the newString string
	*/
	replaceSelection : function(field, txt){
		field = sb.$(field);
		var sel = sb.forms.textarea.getSelection(field);
		
		field.value = sel.before + txt + sel.after;	
		sb.forms.textarea.moveCaret(field, sel.end);
		field.focus();
	},
	
	/**
	@Name: sb.forms.textarea.insertAtCaret
	@Description: inserts a string into a textarea at the caret location
	@Param: String field The id or a reference to the textarea or text input.  Can be an obj reference or a string such as '#myField'.
	@Param: String txt The new string to insert

	@Example:
		sb.forms.textarea.insertAtCaret('#myTextArea', 'newString');
		//inserts the text in the textarea at the caret
	*/
	insertAtCaret : function(field, txt){
		field = sb.$(field);
		sb.forms.textarea.replaceSelection(field, txt);
	},
	
	/**
	@Name: sb.forms.textarea.moveCaret
	@Description: moves the caret (cursor location) within a textarea
	@Param: String field The id or a reference to the textarea or text input.  Can be an obj reference or a string such as '#myField'.
	@Param: Number pos The character position to move the mouse to, rememeber it starts at 0.

	@Example:
		sb.forms.textarea.moveCaret('#myTextArea', 6);
		//moves the caret to character position 6
	*/
	moveCaret : function(field, pos){
		var range;
		field = sb.$(field);
		if (field.setSelectionRange) {
			field.setSelectionRange(pos, pos);
		} else if(field.createTextRange){
			range = field.createTextRange();
			range.collapse(true);
			range.moveStart("character", pos);
			range.moveEnd("character", pos - pos);
			range.select();
		}
		field.focus();
	}
};