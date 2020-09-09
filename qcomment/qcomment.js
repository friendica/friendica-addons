function qCommentInsert(obj, id)
{
	let $textarea = $('#comment-edit-text-' + id);

	if ($textarea.val() === '') {
		$textarea.addClass('comment-edit-text-full');
		$textarea.removeClass('comment-edit-text-empty');
		openMenu('comment-edit-submit-wrapper-' + id);
	}

	var ins = $(obj).val();
	ins = ins.replace('&lt;', '<');
	ins = ins.replace('&gt;', '>');
	ins = ins.replace('&amp;', '&');
	ins = ins.replace('&quot;', '"');
	$textarea.val($textarea.val() + ins);
	$(obj).val('');
}
