function clearViewDB() {
	$("[name='fullsearch']").val("");
	$("[name='artist']").val("");
	$("[name='title']").val("");
	$("[name='album']").val("");
	$("[name='tags']").val("");
	$("[name='green']").val(""); // wtf ed
	$("[name='replace']").val('2');
	$("[name='usability']").val('2');
}

function finishTrack(responseText, statusText, form) {
	$(".accepted-row, .declined-row").fadeOut(500, function () {
		$(this).remove();
	});
}
function acceptSubmit(formData, jqForm, options) {
	jqForm.addClass("submitted-form");
	jqForm.parent().addClass("accepted-row");
}
function declineSubmit(formData, jqForm, options) {
	jqForm.addClass("submitted-form");
	jqForm.parent().addClass("declined-row");
}
$(function () {
	$("#jPlayer").jPlayer({
		ready: function () {
			$(".play").click(function (event) {
				event.preventDefault();
				$("#jPlayer").jPlayer("setMedia", {
					mp3: $(this).attr("value")
				}).jPlayer("play");
			});
		},
		swfPath: "/js/Jplayer.swf",
		supplied: "mp3",
		preload: "auto",
		emulateHtml: true,
		wmode: "window"
	})
	$("input[value='Accept']").click(function () {
		jqForm = $(this).parent().first().prevUntil("form").prev().last()
		$("<input>").attr({
			'type': 'hidden',
			'name': 'subbut'
		}).val('Accept').appendTo(jqForm);
		jqForm.ajaxSubmit({success: finishTrack,
				beforeSubmit: acceptSubmit});
		return false;
	});
	$("input[value='Decline']").click(function () {
		jqForm = $(this).parent().first().prevUntil("form").prev().last()
		$("<input>").attr({
			'type': 'hidden',
			'name': 'subbut'
		}).val('Decline').appendTo(jqForm);
		jqForm.ajaxSubmit({success: finishTrack,
				beforeSubmit: declineSubmit});
		return false;
	});
});