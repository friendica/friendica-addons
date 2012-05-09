// Hangman Javascript
// copyright 10th June 2005, 8th December 2005 by Stephen Chapman
// permission to use this Javascript on your web page is granted
// provided that all of the code in this script (including these
// comments) is used without any alteration

var page = self.location.toString(); page = page.substr(0,page.indexOf('?'));
var qsParm = new Array();function qs() {var query = window.location.search.substring(1);var parms = query.split('&');for (var i=0; i<parms.length; i++) {var pos = parms[i].indexOf('=');if (pos > 0) {var key = parms[i].substring(0,pos);var val = parms[i].substring(pos+1);qsParm[key] = val;}}}
qsParm['opt'] = null;qsParm['al'] = null;qsParm['w'] = null;qs();
var win = 0;if (qsParm['win']) win = parseInt(qsParm['win']);
var opt = -1;if (qsParm['opt']) opt = qsParm['opt']%71; else opt = Math.floor(Math.random()*opts.length);
var al = '--------------------------';if (qsParm['al']) al = qsParm['al'];
var wr = 'xhwdarqpnez';var dc = '7!3@4#1$^5*~:6 +8=`<2-0>_/?9';
var wx = 0; if (qsParm['w']) wx = wr.indexOf(qsParm['w']);
var answer = trans2(opts[opt]);
function trans2(op) {var opn = '';for (var i = 0; i < op.length; i++) {var ch = op.substr(i,1);if (ch == dc.substr(27,1)) break; if (ch == dc.substr(0,1)) opn += ' '; else opn += String.fromCharCode(dc.indexOf(ch)+64);} return opn;}
function selectLetter(s) {s = parseInt(s); var ch = String.fromCharCode(s+65); if (answer.indexOf(ch) == -1) wx++; al = al.substring(0,s) + ch + al.substring(s+1,al.length); var opty = Math.floor(Math.random()*165)*71 + opt; top.location = page + '?opt='+opty+'&al='+al+'&w=' +wr.charAt(wx); return false;}
function availLetter() {document.write('<div class="hangt">'); for (var i = 0; i < al.length; i++) {
var ltr = String.fromCharCode(i+65); if (al.charAt(i) == ltr) document.write('  '); else document.write('<a href="#" onclick="selectLetter(\''+i+'\')">'+ ltr+'<\/a> '); if (i == 12) document.write('<br \/>');} document.write('<\/div>');}
function displayAnswer() {var correct = ''; document.write('<div class="hanga"> <br \/>'); for (var i = 0; i < answer.length; i++) {if (answer.substr(i,1) == ' ') {document.write('  '); correct += ' ';} else {var ltr = ''; if (win == 0) ltr = al.charAt(answer.charCodeAt(i)-65); else ltr = answer.substr(i,1); document.write(ltr + ' '); correct += ltr;}} document.write('<\/div>'); if (win == 0) {var opty = Math.floor(Math.random()*165)*71 + opt; if (wr.charAt(wx) == 'z') top.location = page + '?win=1&opt='+opty+'&w='+wr.charAt(wx); if (correct == answer.toUpperCase()) top.location = page + '?win=4&opt='+ opty+'&w='+wr.charAt(wx);}}
document.write('<div class="hangb">'); if (win == 0) availLetter(); else if (win == 4)  document.write('<div class="hangt">YOU WIN<br \/><a href="'+page+'">Try Again<\/a><\/div>'); else document.write('<div class="hangt">YOU LOSE<br \/><a href="'+page+'">Try Again<\/a><\/div>'); document.write('<div align="center"> <br \/><img src="'+img+'hang'+wx+'.gif" width="100" height="100" alt="hangman image '+wx+'" \/><br \/> <\/div>'); displayAnswer(); document.write('<\/div>');
                  
