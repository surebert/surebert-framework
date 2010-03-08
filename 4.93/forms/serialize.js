/**
@Name: sb.forms.serialize
@Description: Serializes a form input types including text, textarea, select, select-multiple, radio, checkbox
@Param: String form The name of the form to serialize

@Return: String The serialized form dat e.g.first_name=paul&day=monday
@Example:
<form method="post" action="index.php">
<input type="text" name="first_name" value="paul" />
<input type="text" name="last_name" value="visco" />
</form>

var data = sb.forms.serialize("#myForm");
//data = first_name=paul&last_name=visco
*/
sb.forms.serialize = function(form) { 
	var dat=[],s,e=sb.toArray(sb.$(form).elements),enc=encodeURIComponent;
	e.forEach(function(v){
		var n=v.name,t=v.type;
		if(n && v.value){
			if(t=='select-one'){
				dat.push(n + "=" + enc(v.options[v.selectedIndex].value));
			} else if(t =="select-multiple"){
				for(s=0;s<v.options.length;s++){
					if(v.options[s].selected===true){
						dat.push(n + "=" + enc(v.options[s].value));
					}
				}
			} else if(t == "checkbox" || t=="radio"){
				if(v.checked==1){dat.push(n + "=" + enc(v.value));}
			} else {
				dat.push(n + "=" + enc(v.value));
			}
		}
	});
	
	return dat.join('&');
};