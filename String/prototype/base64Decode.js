sb.include('String.prototype.UTF8Decode');

/**
@Name: String.prototype.base64Decode
@Author: Paul Visco - Adapted/Taken from http://www.webtoolkit.info/
@Version: 1.0 02/09/09
@Description: decodes base64 strings
@Return: String
@Example:
var myString = 'aGVsbG8gd29ybGQ=';
var newString = myString.base64Decode();
*/
  
String.prototype.base64Decode= function(input){
	var key = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
    var output = "";
    var chr1, chr2, chr3;
    var enc1, enc2, enc3, enc4;
    var i = 0;

    input = this.replace(/[^A-Za-z0-9\+\/\=]/g, "");

    while (i < input.length) {

        enc1 = key.indexOf(input.charAt(i++));
        enc2 = key.indexOf(input.charAt(i++));
        enc3 = key.indexOf(input.charAt(i++));
        enc4 = key.indexOf(input.charAt(i++));

        chr1 = (enc1 << 2) | (enc2 >> 4);
        chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
        chr3 = ((enc3 & 3) << 6) | enc4;

        output = output + String.fromCharCode(chr1);

        if (enc3 != 64) {
            output = output + String.fromCharCode(chr2);
        }
        if (enc4 != 64) {
            output = output + String.fromCharCode(chr3);
        }

    }
    
    output = output.UTF8Decode();

    return output;

};