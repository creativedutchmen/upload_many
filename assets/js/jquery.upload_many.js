(function($) {

	var location = window.location + "";
	var section_handle = location.substring(Symphony.WEBSITE.length).split("/")[3];
	var uploadUrl = Symphony.WEBSITE + '/symphony/extension/upload_many/create/new/'+section_handle+"/?action=true";
	

	var upload_many = {
	
		fieldName: null,
		
		setUp: function(){
			//javascript is available, and because the html4 fallback is used, it should always be right.
			
			upload_many.fieldName = $(".field-upload_many input[type=file]").attr("name");
			
			
			$(".field-upload_many input").hide();
			$(".field-upload_many label>span").append("<a href='#' class='button' id='browse'>Upload Files</a>");
			
			uploader.init();
			
			$('<span id="list"></span>').appendTo($('.field-upload_many label.file > span'));			
			
			$("input[type=submit]").click(function(e){
			
				uploader.settings.multipart_params = upload_many.processFields();
				uploader.settings.multipart_params['fieldName'] = upload_many.fieldName;
				
				uploader.start();
				e.preventDefault();
				//create all entries
			});
			
			uploader.bind('FilesAdded', function(up, files) {
				$.each(files, function(i, file) {
					$('<span id="' + file.id + '"><b>'+file.name+'</b><em class="button">Remove file</em><span class="upload-progress"></span></span>').appendTo("#list").find("em").each(function(){
						$(this).click(function(){
							upload_many.removeFile($(this).parent().attr("id"));
						});
					});
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
		},
		
		removeFile: function(id){
			uploader.removeFile(uploader.getFile(id));
			$("span #"+id).remove();
		}
		
		
	
	};

	var uploader = new plupload.Uploader({
		runtimes : 'flash,gears,silverlight',
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

