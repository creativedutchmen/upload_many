jQuery(document).ready(function() {
	jQuery('table').each(function() {
		var table = jQuery(this);
		var header = table.prev('h2');
		var button = jQuery('<a class="upload-many button" href="new/many">Create Many</a>');
		button.appendTo(header);
	});
});