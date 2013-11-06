var bear;
var sx, sy;
var cx, cy;
var flipped = false;
function anii()
{
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
				'url("http://r-a-d.io/css/ed/dongcopter1.png")');
		}
		if (cx > sx && !flipped)
		{
			flipped = true;
			bear.css('background-image',
				'url("http://r-a-d.io/css/ed/dongcopter2.png")');
		}
	}
	sx += Math.cos(atan)*5;
	sy += Math.sin(atan)*5;

	bear.css({
		'left'	: sx + 'px',
		'top'	: sy + 'px'
	});
	setTimeout('anii();', 50);
}

function run_bear() {
	bear = $('<img id="bear">');
	bear.appendTo('#page-p-home');
	bear.css({
		'background': 'url("http://r-a-d.io/css/ed/dongcopter2.png") top left no-repeat',
		'position'	: 'fixed',
		'z-index'	: '9001',
		'left'		: '64px',
		'top'		: '64px',
		'width'		: '175px',
		'height'	: '111px'
	});
	sx = sy = cx = cy = 64;
	anii();

	$('body').mousemove(function(e) {
		cx = parseInt(e.clientX) + 8;
		cy = parseInt(e.clientY) - 8;
	});
}
