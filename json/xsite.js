/**
@Name: sb.json.xsite
@Version: 1.0 07-27-09 07-29-09
@Description: Calls for cross site json data to load into a local callback function via script tag
@Param: Object params An objectw ith all the properties of the swf should have once embedded
	url - the url of the json data
	callback - The callback function to pass the json data to
    data - Additional post data to pass to the url, is GET data and must adhere to GET data length restrictions
@Return: Object A sb.json.xsite instance with the properties specifed in the param argument and by sb.json.xsite.prototype
@Example:
var xsite = new sb.json.xsite({
    url : 'http://somesite.com/get_user/78',
    data : {
        'dog' : 'big'
    },
    callback : function(json){
       alert(json.uname);
    }
});

xsite.fetch();

//to test with test data
xsite.fetch({uname: 'paul'});
*/
sb.json.xsite = function(o){

    var sjx=sb.json.xsite;
  
    if(!o.url){
        throw('sb.json.xsite argument must be an object with url property');
    }

    this.url = o.url;
    this.autoGC = o.autoGC === false ? false : true;
    this.data = o.data || {};
    this.callback = o.callback || function(){};
    this.id = 'i'+(sb.json.xsite.instances.length+1);

};

/**
@Name: sb.json.xsite.instances
@Description: Used Internally. An array of references to scripts loaded with sb.json.xsite
 */
sb.json.xsite.instances = [];
var sbjxs = sb.json.xsite.instances;

/**
@Name: sb.json.xsite.prototype
@Description: Used Internally.
 */
sb.json.xsite.prototype = {

    /**
    @Name: sb.json.xsite.prototype.autoGC
    @Description: Determines if the script is auto garbage collected or not
     */
    autoGC : true,

    /**
    @Name: sb.json.xsite.prototype.callback
    @Description: The callback function that the json data is passed to
     */
    callback : function(){

    },

    /**
    @Name: sb.json.xsite.prototype.fetch
    @Description: Loads the json data from the url
     */
    fetch : function(json){

        if(json){
            this.callback(json);
        } else {

            var data = sb.objects.serialize(this.data);

            var script = new sb.script({
                src : this.url+'?sb_callback=sbjxs["'+this.id+'"].callback&'+data
            });
            script.setAttribute('xsite', 1);
            script.autoGC = this.autoGC;
          
            script.onload = function(){
              
                if(this.autoGC){
                    this.remove();
                }
            };

            sb.json.xsite.instances[this.id] = this;

            script.load();
            
        }
    }
};

