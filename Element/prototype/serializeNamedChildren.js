/**
@Name: Element.prototype.serializeNamedChildren
@Description: Serialzies all child elements that have name attributes contained within the Element including text, textarea, select, select-multiple, radio, checkbox and any other misc elements with name attributes
@Param: object o Optional, The properties of an sb.ajax instance, e.g. url, onResponse, etc, if sent the data is send to that address
@Return: String first_name=paul&day=tues
@Example:
//just return the serialized data
var string = $('#myElement').serializeNamedChildren();

//serialize and send data to url
var string = $('#myElement').serializeNamedChildren('/some/page');

//serialize and send using more complex ajax object
var string = $('#myElement').serializeNamedChildren({
    url : '/some/page',
    onResponse : function(r){
        alert(r);
    }
});
*/

Element.prototype.serializeNamedChildren = function(o) {
    var dat=[],s,enc=encodeURIComponent;

    this.$('*[name]').forEach(function(v,k,a){
        var n=v.getAttribute('name'),t=v.type,val=v.value||v.getAttribute('value')||'',enc=encodeURIComponent;

        //add array to name if multiple value type
        if(t == 'select-multiple' || t == 'checkbox' && !n.match(/\[\]$/)){
            n += '[]';
        }

        switch(t){
            case 'select-one':
                dat.push(n + "=" + enc(v.options[v.selectedIndex].value));
                break;

            case 'select-multiple':

                for(s=0;s<v.options.length;s++){
                    if(v.options[s].selected === true){
                        dat.push(n + "=" + enc(v.options[s].value));
                    }
                }
                break;

            default:
                if((t == "checkbox" || t=="radio") && v.checked ==  false){ break; }

                dat.push(n + "=" + enc(val));
                break;
        }

    });

    s = dat.join('&');

    //handle ajax
    if(o){
        if(typeof o == 'string'){
            o = { url : o};
        }

        if(o.url){
            var aj = new sb.ajax(o);
            aj.data = s+'&'+aj.data;
            aj.fetch();
        }
    }

    return s;

};