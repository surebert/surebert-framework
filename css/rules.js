/**
@Name: sb.css.rules.write
@Description: Used to write or override CSS rules on the page
@Example:
sb.css.rules.write('p', 'background-color;red;');
//writes the following into the pages css
p{
	background-color:red;
}

sb.css.rules.write('.box', 'background-color;red;');
//writes the following into the pages css
.box{
	background-color:red;
}

sb.css.rules.write('#myList', 'background-color;red;');
//writes the following into the pages css
#myList{
	background-color:red;
}

sb.css.rules.write('#myList:hover', 'background-color:blue;font-size:30px;');
//writes the following into the pages CSS
#myList:hover{
	background-color:red;
	font-size:30px;
}

*/

sb.css.rules = {
	numRules : 1,
	
	write : function(domEl, rule){
	
		var sheet;
		if(typeof this.sheet ==='undefined'){
			if(document.createStyleSheet) {
				this.sheet = document.createStyleSheet('');
			} else {
			
				sheet = document.createElement('style');
				sheet.type="text/css";
				sheet.appendChild(document.createTextNode(domEl+'{'+rule+'}'));
				this.sheet = sb.$('head').appendChild(sheet);
				sheet=null;
			}
		}
		
		if(this.sheet.insertRule){
			this.sheet.insertRule(domEl+'{'+rule+'}', this.numRules);
		} else if(this.sheet.addRule){
			this.sheet.addRule(domEl, rule);
		} else if (document.styleSheets.length >0){
			document.styleSheets[document.styleSheets.length-1].insertRule(domEl+'{'+rule+'}', this.numRules);
		}
		
		this.numRules++;
	}
};