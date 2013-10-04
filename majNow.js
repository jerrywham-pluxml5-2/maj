/**
 * Plugin Maj
 *
 * @package	PLX
 * @version	1.5
 * @date	07/08/2012
 * @author	Cyril MAGUIRE
 **/
function majNow(delta) {
	var d = new Date();
    // convert to msec, add local time zone offset
    // get UTC time in msec
    var utc = d.getTime() + (d.getTimezoneOffset() * 60000);
    // create new Date object for different city using supplied offset
    var now = new Date(utc + (1000*delta));
	var y = now.getFullYear();
	var m = now.getMonth();
	var d = now.getDate();
	var h = now.getHours();
	var i = now.getMinutes();
	if(i <= 9){i = '0'+i;}
	if(h <= 9){h = '0'+h;}
	if(d <= 9){d = '0'+d;}
	m = m+1;
	if(m <= 9){m = '0'+m;}
	document.getElementsByName('day_maj')['0'].value = d;
	document.getElementsByName('time_maj')['0'].value = h+":"+i;
	document.getElementsByName('month_maj')['0'].value = m;
	document.getElementsByName('year_maj')['0'].value = y;
}
function delMaj() {
	document.getElementsByName('day_maj')['0'].value = '';
	document.getElementsByName('time_maj')['0'].value = '';
	document.getElementsByName('month_maj')['0'].value = '';
	document.getElementsByName('year_maj')['0'].value = '';
}