var steps = 50;
var step = steps;
var col = []; // 1HSL 2HSL
var o = document.getElementsByTagName('a');
for (var a=0; a<o.length; a++)
{
	col[a] = [0,0,0,0,0,0];
}
function pick()
{
	step = 0;
	for (var a=0; a<o.length; a++)
	{
		col[a][0] = col[a][3];
		col[a][1] = col[a][4];
		col[a][2] = col[a][5];
		var h = Math.random()*360;
		var s = Math.random()*50 + 50;
		var l = Math.random()*10 + 25;
		col[a][3] = Math.round(h*10)/10;
		col[a][4] = Math.round(s*10)/10;
		col[a][5] = Math.round(l*10)/10;
	}
}
function slide()
{
	if (++step > steps)
	{
		pick();
	}
	for (var a=0; a<o.length; a++)
	{
		var h = col[a][0] + (col[a][3] - col[a][0]) * (step/steps);
		var s = col[a][1] + (col[a][4] - col[a][1]) * (step/steps);
		var l = col[a][2] + (col[a][5] - col[a][2]) * (step/steps);
		o[a].style.background = 'hsl('+h+','+s+'%,'+l+'%)';
	}
	setTimeout('slide();',10);
}
slide();
