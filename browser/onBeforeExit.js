/**
@Name: sb.browser.onBeforeExit
@Description: Allows you to check for any conditions and then stop browser from
 leaving page if they are met and to give the user a message why.  You can use
 this to check for text being edited and prompt to ask if they want to leave page.
  You can push multiple checks, it will combine all returned messages into one
 long one.
@Example:
//if there are any editable textareas with
sb.browser.onBeforeExit.checks.push(function(){
	if($('textarea.editable').some(function(ta){return ta.value != '';})){
		return 'You have editable text areas open, are you sure you want to leave the page and lose that information.';
	}
});
*/
sb.browser.onBeforeExit = {
	checks : [],
	check : function(){
		var messages = [];
		sb.browser.onBeforeExit.checks.forEach(function(func){
			if(typeof func == 'function'){
				var str = func();
				if(sb.typeOf(str) == 'string'){
					messages.push(str);
				}
			}
		});

		if(messages.length){
			return messages.join("\n");
		}
	}
};

window.onbeforeunload = sb.browser.onBeforeExit.check;