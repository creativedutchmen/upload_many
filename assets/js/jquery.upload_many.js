(function($) {

	var location = window.location + "";
	var section_handle = location.substring(Symphony.WEBSITE.length).split("/")[3];
	var uploadUrl = Symphony.WEBSITE + '/symphony/extension/upload_many/create/new/'+section_handle+"/?action=true";
	

	var upload_many = {
	
		fieldName: null,
		
		setUp: function(){
			//javascript is available, and because the html4 fallback is used, it should always be right.
			
			upload_many.fieldName = $(".field-upload_many input[type=file]").attr("name");
			
			
			$(".field-upload_many input").remove();
			$(".field-upload_many label>span").append("<a href='#' class='button' id='browse'>Upload Files</a>");
			
			uploader.init();
			
			
			
			$("input[type=submit]").click(function(e){
				uploader.start();
				e.preventDefault();
				//create all entries
				uploader.settings.multipart_params = upload_many.processFields();
				uploader.settings.multipart_params['fieldName'] = upload_many.fieldName;
				
			});
			
			uploader.bind('FilesAdded', function(up, files) {
				$.each(files, function(i, file) {
					$('#filelist').append(
						'<div id="' + file.id + '">' +
						file.name + ' (' + plupload.formatSize(file.size) + ') <b></b>' +
					'</div>');
				});
				up.refresh(); // Reposition Flash/Silverlight
			});
		},
		
		processFields: function(){
			var returns = {};
			var i = 0;
			$("form input, form textarea").each(function(){
				returns[$(this).attr("name")] = $(this).val();
			});
			return returns;
		}
	
	};

	var uploader = new plupload.Uploader({
		runtimes : 'html5,flash,gears,html4',
		browse_button : 'browse',
		max_file_size : '10mb',
		url:uploadUrl,
		multipart:true,
		
		//TODO: make settting.
		resize : {width : 320, height : 240, quality : 90},
		
		flash_swf_url : Symphony.WEBSITE+'/extensions/upload_many/assets/plupload/plupload.flash.swf',
		
		//TODO: make setting.
		filters : [
			{title : "Image files", extensions : "jpg,gif,png"},
		]
		
	});
	
	$(document).ready(function() {
		upload_many.setUp();
	});
	
})(jQuery);

