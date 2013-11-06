var ed8stop = false;
var ed8can, ed8ctx;
var ed8body, ed8div;
var ed8im = [];
var ed8ip = ['base','base2','carpet','wc1','wc2','srbd','srb','bit1','bit2','bit3','bit4'];
var ed8killer = 1;
function drewImage(a,b,c)
{
	//ed8ctx.drawImage(a,b,c);
}
function ed8kill()
{
	ed8killer -= 0.1;
	if (ed8killer <= 0)
	{
		ed8body.removeChild(ed8div);
		run_bear();
		return;
	}
	ed8div.style.opacity = ed8killer;
	setTimeout('ed8kill();', 20);
}
function ed8init()
{
	ed8body = document.getElementsByTagName('body')[0];
	ed8div = document.createElement('div');
	ed8div.setAttribute('id', 'ed8div');
	ed8div.onclick = function(e) {
		ed8stop = true;
		ed8kill();
	};
	ed8body.appendChild(ed8div);
	var ed8stl = window.getComputedStyle(ed8div, null);
	var ed8dw = parseInt(ed8stl.getPropertyValue('width'));
	var ed8dh = parseInt(ed8stl.getPropertyValue('height'));
	ed8can = document.createElement('canvas');
	ed8can.setAttribute('id', 'ed8can');
	ed8can.setAttribute('width', '768');
	ed8can.setAttribute('height', '720');
	ed8can.style.left = ((ed8dw-768)/2)+'px';
	ed8can.style.top = ((ed8dh-720)/2)+'px';
	ed8div.appendChild(ed8can);
	ed8ctx = ed8can.getContext('2d');

	ed8can.style.background = "#111 url('/css/ed/pac.gif') center no-repeat";
	var ed8mute = $('<div id="ed8mute">');
	ed8mute.appendTo('#page-p-home');
	ed8mute.css({
		'background': 'url("/css/ed/mute.png") top left no-repeat',
		'position'	: 'fixed',
		'z-index'	: '9601',
		'right'		: '.5em',
		'top'		: '2.5em',
		'width'		: '272px',
		'height'	: '183px',
	});
	var audur = 0;
	ed8mute.click(function(){
		audur.pause();
		$('#ed8mute').css('display','none');
	});
	if ((new Audio()).canPlayType("audio/ogg; codecs=vorbis"))
	{
		audur = new Audio("/css/ed/azuki.ogg");
		audur.play();
	}
	else
	{
		audur = new Audio("/css/ed/azuki.mp3");
		audur.play();
	}

	for (var a = 0; a < ed8ip.length; a++)
	{
		ed8im[a] = new Image();
		ed8im[a].onLoad = ed8loader();
/*		if (a == 1 || a == 0)
		{
			ed8im[a].src = '/css/ed/ana3.jpg';
		}
		else if (a < 5)
		{
*/			var ih = ed8ip[a];
			if (ih.indexOf('.')<0)
			{
				ih = ih + '.png';
			}
			ih = '/css/ed/' + ih;
			ed8im[a].src = ih;
/*		}
		else
		{
			ed8im[a].src = '/css/ed/scanlines.png';
		}
		//ed8im[a].src = ed8ip[a]+'.png';
*/	}
}
var ed8remain = ed8ip.length;
function ed8loader()
{
	ed8remain--;
	if (ed8remain <= 0)
	{
		ed8sc0();
	}
}
var ed8sc0n = 6;
function ed8sc0()
{
	if (ed8sc0n-- < 0)
	{
		setTimeout('ed8sc1();', 100);
		return;
	}
	drewImage(ed8im[0], 0, 0);
	drewImage(ed8im[2], 0, 0);
	setTimeout('ed8sc0();', 100);
}
var ed8sc1y = 576;
function ed8sc1()
{
	if (ed8stop) return;
	ed8sc1y-=5;
	if (ed8sc1y < 0)
	{
		setTimeout('ed8sc2();', 300);
		return;
	}
	drewImage(ed8im[0], 0, 0);
	drewImage(ed8im[2], 0, ed8sc1y-576);
	setTimeout('ed8sc1();', 10);
}
var ed8sc2x = 768;
function ed8sc2()
{
	if (ed8stop) return;
//	ed8sc3x +=
//		ed8sc3x < 400 ? 24 :
//		ed8sc3x < 460 ? 3 : 18;
	ed8sc2x -=
		ed8sc2x > 200 ? ed8sc2x/16 :
		ed8sc2x > 170 ? 2 : 1;
	if (ed8sc2x < 130)
	{
		ed8sc3();
		return;
	}
	drewImage(ed8im[0], 0, 0);
	drewImage(ed8im[3], ed8sc2x, 280);
	drewImage(ed8im[4], 568-ed8sc2x, 340);
	setTimeout('ed8sc2();', 20);
}
var ed8sc3v = 0;
function ed8sc3()
{
	if (ed8stop) return;
	ed8sc3v += 0.1;
	if (ed8sc3v >= 0.9)
	{
		ed8ctx.globalAlpha = 1;
		ed8sc4();
		return;
	}
	ed8ctx.globalAlpha = ed8sc3v;
	drewImage(ed8im[0], 0, 0);
	setTimeout('ed8sc3();', 50);
}
var ed8sc4y = -324;
function ed8sc4()
{
	if (ed8stop) return;
	ed8sc4y += 9;
	if (ed8sc4y >= 96)
	{
		ed8sc5();
		return;
	}
	drewImage(ed8im[0], 0, 0);
	drewImage(ed8im[5], 138, ed8sc4y);
	setTimeout('ed8sc4();', 10);
}
var ed8sc5n = 48; //34
function ed8sc5()
{
	if (ed8stop) return;
	if (ed8sc5n-- < 0)
	{
		ed8sc6();
		return;
	}
	drewImage(ed8im[0], 0, 0);
	drewImage(ed8im[5], 138, ed8sc4y +
		((ed8sc5n%2==0) ? 6 : 0)
	);
	setTimeout('ed8sc5();', 10);
}
var ed8sc6v = 0;
function ed8sc6()
{
	if (ed8stop) return;
	ed8sc6v += 0.04;
	if (ed8sc6v >= 0.5)
	{
		ed8ctx.globalAlpha = 1;
		drewImage(ed8im[1], 0, 0);
		ed8sc7();
		return;
	}
	ed8ctx.globalAlpha = ed8sc6v;
	drewImage(ed8im[1], 0, 0);
	//drewImage(ed8im[6], 138, 96);
	drewImage(ed8im[7], 336,312);
	setTimeout('ed8sc6();', 50);
}
var ed8sc7v = 0;
function ed8sc7()
{
	if (ed8stop) return;
	ed8sc7v++;
	// 5 5 5 10 10 5
	var i = 7;
	     if (ed8sc7v <  5) i = 7;
	else if (ed8sc7v < 10) i = 8;
	else if (ed8sc7v < 15) i = 9;
	else if (ed8sc7v < 25) i = 10;
	else if (ed8sc7v < 35) i = 9;
	else if (ed8sc7v < 40) i = 8;
	else ed8sc7v = 1;
	drewImage(ed8im[i], 336, 312);
	drewImage(ed8im[6], 138, 96);
	setTimeout('ed8sc7();', 15);
}
ed8init();








var bear;
var sx, sy;
var cx, cy;
var dongperiod;
var flipped = false;
var undickit = false;
function anii()
{
	if (undickit)
	{
		bear.css({'display':'none'});
		return;
	}
	var dx = cx-sx;
	var dy = cy-sy;
	var dist = Math.pow(dx,2) + Math.pow(dy,2);
	dist = Math.sqrt(dist);
	var atan = 0;
	if (dy != 0)
	{
		atan = Math.atan(dy*1.0/dx);
	}
	if (dx<0) atan+=3.14;
	if (dist > 50)
	{
		if (cx < sx && flipped)
		{
			flipped = false;
			bear.css('background-image',
				'url("//r-a-d.io/css/ed/ghost1.png")');
		}
		if (cx > sx && !flipped)
		{
			flipped = true;
			bear.css('background-image',
				'url("//r-a-d.io/css/ed/ghost2.png")');
		}
	}
	sx += Math.cos(atan)*dist/40;
	sy += Math.sin(atan)*dist/40;

	dongperiod += 0.01;
	if (dongperiod > 1)
	{
		dongperiod = 0;
	}
	var perx = Math.cos(dongperiod*Math.PI*2) * 120 - 60;
	var pery = Math.sin(dongperiod*Math.PI*4) *  60 - 30;
	var fact = Math.min(1,1/(dist/200));
	var rx = sx + perx * fact;
	var ry = sy + pery * fact;

	var deg = Math.log(dist);
	deg = dist / 10;
	deg = deg < 90 ? deg : 90;
	deg = flipped ? deg : -deg;
	deg = deg + 'deg)';
	bear.css({
		'left'	: rx + 'px',
		'top'	: ry + 'px',
		'-moz-transform' : 'rotate(' + deg,
		'-webkit-transform' : 'rotate(' + deg,
		'-o-transform' : 'rotate(' + deg
	});
	//document.title = dist;
	setTimeout('anii();', 25);
}

function run_bear() {
	dongperiod = 0;
	bear = $('<img id="bear">');
	bear.appendTo('#page-p-home');
	bear.css({
		'background': 'url("/css/ed/ghost2.png") top left no-repeat',
		'position'	: 'fixed',
		'z-index'	: '9001',
		'left'		: '64px',
		'top'		: '64px',
		'width'		: '105px',
		'height'	: '200px',
		'rotation-point' : '50% 50%'
	});
	bear.click(function(){
		undickit = true;
	});
	sx = sy = cx = cy = 192;
	anii();

	$('body').mousemove(function(e) {
		cx = parseInt(e.clientX) + 8;
		cy = parseInt(e.clientY) - 8;
	});
}
