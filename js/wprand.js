var n = 0;
var max = 37;
var v = [];

// jsfromhell indeed
for (var a = 0; a < max; a++) v[a] = a + 1;
for (var j,x,i = v.length; i;
	j = parseInt(Math.random() * i),
	x = v[--i], v[i] = v[j], v[j] = x);

function setbg()
{
	setTimeout('setbg()',45000);
	var url = '/css/egg2/'+(1+v[n++])+'.jpg';
	$("html")
		.css("background","#222 url('"+url+"')")
		.css("background-size","cover");
	if (n>=max) n=0;
}
setbg();
