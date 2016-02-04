/** This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version, see http://www.gnu.org/licenses/
alternative liceses can be attained by
Klaus Hammermueller, Open Learning Association klaus@o-le.org **/
//some security features
//----------------------
//adapted from String.hashCode()
function hash(str) {
  var h = 1275801696342597; // prime
  var len = str.length;
  if (len < 1) return h;
  for (var i = 0; i < len; i++) {
    var c = str.charCodeAt(i);
    h = ((h<<5)-h)+c;
    h = h & h;  // Convert to 32bit integer
  }
  return h;
} 

//base64 encoding chars for url (last two chars are non-standard)
var base64_chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ-.";

//generate a random base64 string
function generateRandomString(len) {
	var randomString = '';
	for (var i = 0; i < len; i++) {
		randomString = randomString + base64_chars.substr(Math.floor((Math.random() * base64_chars.length-1)),1);
	}	
	return randomString;
}

//constructor for a crypto object to en/decrypt names, requires a base64 encoded key
function AESchiper(myKey) {
	//init
	var prime_key = "kOsRf7Zq2Ka-dHjw8UfkLex0"; // backup key- replace with your own and generally use client side generated keys
	AES_Init();
	this.chars = base64_chars;
	this.url2bin = url2bin;
	if (typeof myKey === 'undefined' || myKey === null)
		myKey = prime_key;
	this.keyHash = hash(myKey);
	var aKey = myKey + prime_key;
	this.key = this.url2bin(aKey.substr(0,24)).slice(0,16);
	AES_ExpandKey(this.key);

	//private functions
	this.encrypt = function ( inputStr ) {
		var block = this.string2Bin(inputStr);
		AES_Encrypt(block, this.key);		
		return block;
	}
	
	this.decrypt = function ( array ) {
		var block = array;		
		AES_Decrypt(block, this.key);		
		return this.bin2String(block);
	}
	
	//these functions you want to call
	this.encryptSaltedString = function ( myString, saltedLength ) {
		if (ENCRYPT_DATA) {
			var len = saltedLength;
			if (len < 1)
				len = myString.length + 2;
			var str = myString.substr(0, len-2).replace('|',''); 
			str += "|" + generateRandomString(len-1-str.length);
			var data=[];
			for(var i=0;i<len;i=i+16){
				data = data.concat( this.encrypt(str.substr(i,16)));
			}
			return this.bin2url( data );
		} else
			return myString;
	}
	
	this.decryptSaltedString = function ( myString ) {				
		if (ENCRYPT_DATA) {
			var arr = this.url2bin( myString );		
			var data='';
			var i, j, len = arr.length;	
			for( i=0;i < len; i=i+16) {
				data+=this.decrypt(arr.slice(i,i+16));			
			}
			var res = data.split("|");
			if (res.length < 2)
				return "?";
			else
				return res[0];
		} else
			return myString;
	}

	this.encryptSaltedArray = function  ( myArray, saltedLength ) {	
		var result = myArray;
		for(var i = 0;i < result.length;i++) {
			var s = $.trim(result[i]);
			result[i] = this.encryptSaltedString(s, saltedLength);
	  	}
		return result;
	}	
	
	this.decryptSaltedArray = function  ( myArray ) {	
		var result = myArray;
		for(var i = 0;i < result.length;i++) {
			var s = result[i];
			result[i] = this.decryptSaltedString( s );
	  	}
		return result;
	}	
		
	//destructor
	this.finish = function (){
		AES_Done();
		this.key = null;
		this.encrypted = null;
	}

	//some encoding functions
	this.bin2String = bin2String;
	function bin2String(array) {
		var result = "";
		for (var i = 0; i < array.length; i++) {
			result += String.fromCharCode(parseInt(array[i], 2));
		}
		return result;
	}

	this.bin2url = function (input) {
		var output = "", chr1, chr2, chr3, enc1, enc2, enc3, enc4, i = 0;
	    while (i < input.length) {
	    	chr1 = input[i++];
	        chr2 = input[i++];
	        chr3 = input[i++];
	        enc1 = chr1 >> 2;
	        enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
	        enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
	        enc4 = chr3 & 63;
	        output += this.chars.charAt(enc1);
	        output += this.chars.charAt(enc2);
	        output += this.chars.charAt(enc3);
	        output += this.chars.charAt(enc4);
	    }
	    return output;
	}
		
	function url2bin(input) {
	    var output = [], chr1, chr2, chr3, enc1, enc2, enc3, enc4, i = 0;
        while (i < input.length) {
            enc1 = this.chars.indexOf(input.charAt(i++));
            enc2 = this.chars.indexOf(input.charAt(i++));
            enc3 = this.chars.indexOf(input.charAt(i++));
            enc4 = this.chars.indexOf(input.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            output = output.concat([chr1, chr2, chr3]); 
        }
        return output;
    }

	this.string2Bin=string2Bin;
	function string2Bin(str) {
		var result = [];
		for (var i = 0; i < str.length; i++) {
			result.push(str.charCodeAt(i));
		}
		return result;
	}
	
	function bin2String(array) {
		return String.fromCharCode.apply(String, array);
	}

}