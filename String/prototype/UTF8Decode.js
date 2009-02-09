/**
@Name: String.prototype.UTF8Decode
@Author: Paul Visco - Adapted/Taken from http://www.webtoolkit.info/
@Version: 1.0 02/09/09
@Description: encodes and decodes UTF8
@Return: String
@Example:
var newString = myString.UTF8Decode();
*/	
String.prototype.UTF8Decode = function () {

		var utftext = this;
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;

        while ( i < utftext.length ) {

            c = utftext.charCodeAt(i);

            if (c < 128) {
                string += String.fromCharCode(c);
                i++;
            }
            else if((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i+1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2;
            }
            else {
                c2 = utftext.charCodeAt(i+1);
                c3 = utftext.charCodeAt(i+2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3;
            }

        }

        return string;
};