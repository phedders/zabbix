//Javascript document
/*
** ZABBIX
** Copyright (C) 2000-2009 SIA Zabbix
**
** This program is free software; you can redistribute it and/or modify
** it under the terms of the GNU General Public License as published by
** the Free Software Foundation; either version 2 of the License, or
** (at your option) any later version.
**
** This program is distributed in the hope that it will be useful,
** but WITHOUT ANY WARRANTY; without even the implied warranty of
** MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
** GNU General Public License for more details.
**
** You should have received a copy of the GNU General Public License
** along with this program; if not, write to the Free Software
** Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
**/ 

/************************************************************************************/
/*								URL MANIPULATION CLASS 								*/
/************************************************************************************/
// Author: Aly
var Curl = Class.create();

Curl.prototype = {
url: 		'',		//	actually, it's deprecated/private variable 
port:		 -1,
host: 		'',
protocol: 	'',
username:	'',
password:	'',
filr:		'',
reference:	'',
path:		'',
query:		'',
args:  null,

initialize: function(url){
	var url = url || location.href;
	
	this.url = decodeURI(url);
	this.args = {};

	this.query=(this.url.indexOf('?')>=0)?this.url.substring(this.url.indexOf('?')+1):'';
	if(this.query.indexOf('#')>=0) this.query=this.query.substring(0,this.query.indexOf('#'));
	
	var protocolSepIndex=this.url.indexOf('://');
	if(protocolSepIndex>=0){
		this.protocol=this.url.substring(0,protocolSepIndex).toLowerCase();
		this.host=this.url.substring(protocolSepIndex+3);
		if(this.host.indexOf('/')>=0) this.host=this.host.substring(0,this.host.indexOf('/'));
		var atIndex=this.host.indexOf('@');
		if(atIndex>=0){
			var credentials=this.host.substring(0,atIndex);
			var colonIndex=credentials.indexOf(':');
			if(colonIndex>=0){
				this.username=credentials.substring(0,colonIndex);
				this.password=credentials.substring(colonIndex);
			}
			else{
				this.username=credentials;
			}
			this.host=this.host.substring(atIndex+1);
		}
		
		var host_ipv6 = this.host.indexOf(']');
		if(host_ipv6>=0){
			if(host_ipv6 < (this.host.length-1)){
				host_ipv6++;
				var host_less = this.host.substring(host_ipv6);

				var portColonIndex=host_less.indexOf(':');
				if(portColonIndex>=0){
					this.port=host_less.substring(portColonIndex+1);
					this.host=this.host.substring(0,host_ipv6);
				}
			}
		}
		else{
			var portColonIndex=this.host.indexOf(':');
			if(portColonIndex>=0){
				this.port=this.host.substring(portColonIndex+1);
				this.host=this.host.substring(0,portColonIndex);
			}
		}
		this.file=this.url.substring(protocolSepIndex+3);
		this.file=this.file.substring(this.file.indexOf('/'));
		
		if(this.file == this.host) this.file = '';
	}
	else{
		this.file=this.url;
	}
	
	if(this.file.indexOf('?')>=0) this.file=this.file.substring(0, this.file.indexOf('?'));

	var refSepIndex=this.url.indexOf('#');
	if(refSepIndex>=0){
		this.file=this.file.substring(0,refSepIndex);
		this.reference=this.url.substring(refSepIndex+1);
	}

	this.path=this.file;
	if(this.query.length>0) this.file+='?'+this.query;
	if(this.query.length > 0)	this.formatArguments();

	var sid = cookie.read('zbx_sessionid');
	if(!is_null(sid)) this.setArgument('sid', sid.substring(16));
},


formatQuery: function(){
	if(this.args.lenght < 1) return;
	
	var query = '';
	for(var key in this.args){
		if((typeof(this.args[key]) != 'undefined') && !is_null(this.args[key])){
			query+=key+'='+this.args[key]+'&';
		}
	}
	this.query = query.substring(0,query.length-1);
},

formatArguments: function(){
	var args=this.query.split('&');
	var keyval='';

	if(args.length<1) return;
	
	for(i=0; i<args.length; i++){
		keyval = args[i].split('=');
		this.args[keyval[0]] = (keyval.length>1)?keyval[1]:'';
	}
},

setArgument: function(key,value){
	this.args[key] = value;
	this.formatQuery();
},

unsetArgument: function(key){
	delete(this.args[key]);
	this.formatQuery();
},

getArgument: function(key){
	if(typeof(this.args[key]) != 'undefined') return this.args[key];
	else return null;
},

getArguments: function(){
	return this.args;
},

getUrl: function(){
	this.formatQuery();
 
	var url = (this.protocol.length > 0)?(this.protocol+'://'):'';
	url +=  encodeURI((this.username.length > 0)?(this.username):'');
	url +=  encodeURI((this.password.length > 0)?(':'+this.password):'');
	url +=  (this.host.length > 0)?(this.host):'';
	url +=  (this.port.length > 0)?(':'+this.port):'';
	url +=  encodeURI((this.path.length > 0)?(this.path):'');
	url +=  encodeURI((this.query.length > 0)?('?'+this.query):'');
	url +=  encodeURI((this.reference.length > 0)?('#'+this.reference):'');
//alert(url);
return url;
},

setPort: function(port){
	this.port = port;
},

getPort: function(){ 
	return this.port;
},

setQuery: function(query){ 
	this.query = query;
	if(this.query.indexOf('?')>=0){
		this.query= this.query.substring(this.query.indexOf('?')+1);
	}
	
	this.formatArguments();
	
	var sid = cookie.read('zbx_sessionid');
	this.setArgument('sid', sid.substring(16));
},

getQuery: function(){ 
	this.formatQuery();
	return this.query;
},

/* Returns the protocol of this URL, i.e. 'http' in the url 'http://server/' */
getProtocol: function(){
	return this.protocol;
},

setProtocol: function(protocol){
	this.protocol = protocol;
},
/* Returns the host name of this URL, i.e. 'server.com' in the url 'http://server.com/' */
getHost: function(){
	return this.host;
},

setHost: function(host){
	this.host = host;
},

/* Returns the user name part of this URL, i.e. 'joe' in the url 'http://joe@server.com/' */
getUserName: function(){
	return this.username;
},

setUserName: function(username){
	this.username = username;
},

/* Returns the password part of this url, i.e. 'secret' in the url 'http://joe:secret@server.com/' */
getPassword: function(){
	return this.password;
},

setPassword: function(password){
	this.password = password;
},

/* Returns the file part of this url, i.e. everything after the host name. */
getFile: function(){
	return this.file;
},

/* Returns the reference of this url, i.e. 'bookmark' in the url 'http://server/file.html#bookmark' */
getReference: function(){
	return this.reference;
},

setReference: function(reference){
	this.reference = reference;
},

/* Returns the file path of this url, i.e. '/dir/file.html' in the url 'http://server/dir/file.html' */
getPath: function(){
	return this.path;
},

setPath: function(path){
	this.path = path;
}
}