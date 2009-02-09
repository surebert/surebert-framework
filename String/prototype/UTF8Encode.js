/**
@Name: String.prototype.UTF8Encode
@Author: Paul Visco - Adapted/Taken from http://www.webtoolkit.info/
@Version: 1.0 02/09/09
@Description: encodes and decodes UTF8
@Return: String
@Example:
var myString = 'hello world';
var newString = myString.UTF8Encode();
*/
String.prototype.UTF8Encode = function () {
	
    var string = this.replace(/\r\n/g,"\n");
    var utftext = "";

    for (var n = 0; n < string.length; n++) {

        var c = string.charCodeAt(n);

        if (c < 128) {
            utftext += String.fromCharCode(c);
        }
        else if((c > 127) && (c < 2048)) {
            utftext += String.fromCharCode((c >> 6) | 192);
            utftext += String.fromCharCode((c & 63) | 128);
        }
        else {
            utftext += String.fromCharCode((c >> 12) | 224);
            utftext += String.fromCharCode(((c >> 6) & 63) | 128);
            utftext += String.fromCharCode((c & 63) | 128);
        }

    }

    return utftext;
};