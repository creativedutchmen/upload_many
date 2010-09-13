(function($) {

	var location = window.location + "";
	var section_handle = location.substring(Symphony.WEBSITE.length).split("/")[3];
	
	

	var upload_many = {
	
		fieldName: null,
		uploadUrl: Symphony.WEBSITE + '/symphony/extension/upload_many/create/upload/'+section_handle+"/?action=true",
		saveUrl: Symphony.WEBSITE + '/symphony/extension/upload_many/create/new/'+section_handle+"/?action=true",
		
		setUp: function(){
			//javascript is available, and because the html4 fallback is used, it should always be right.
			
			upload_many.fieldName = $(".field-upload_many input[type=file]").attr("name");
			
			
			$(".field-upload_many input").hide();
			$(".field-upload_many label>span").append("<a href='#' class='button' id='browse'>Upload Files</a>");
			
			uploader.init();
			
			$('<span id="list"></span>').appendTo($('.field-upload_many label.file > span'));			
			
			$("input[type=submit]").click(function(e){
			
				uploader.start();
				
				$("span span#list em").hide();
				
				e.preventDefault();
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
			
			uploader.bind('UploadProgress', function(up, file){
			
				$("#"+file.id).find(".upload-progress").width(file.percent+"%");
				
			});
			
			uploader.bind('FileUploaded', function(Up, File, Response) {
				//TODO: add javascript submission.
				var result = jQuery.parseJSON(Response.response);
				
				var postData = upload_many.processFields();
				postData[upload_many.fieldName] = result.filename;
				
				$.post(upload_many.saveUrl, postData, function(data){
					var results = jQuery.parseJSON(data);
					upload_many.processErrorFields(results,File);
				});
				
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
		},
		
		setErrorField: function(fieldName, errorText){
			$('*[name=fields['+fieldName+']]').parents('div.field:not(.invalid)').append('<p class="errorText">'+errorText+'</p>').addClass("invalid");
		},
		
		processErrorFields: function(jsonObject, file){
			console.log(jsonObject);
			if(jsonObject.error != ""){
				$("#"+file.id).addClass("error").find(".upload-progress").css("background-color","#DD4422");
				this.uploadFailed++;
				//$('#file-'+fileSize+"[name="+fileName+"]").find("em").show();
			}
			else{
				this.uploadSuccess++;
				$("#"+file.id).append('<input type="hidden" value="'+file.name+'" name="previously_uploaded_files[]" />')
			}
			for(var i in jsonObject.error){
				this.setErrorField(jsonObject.error[i].fieldName, jsonObject.error[i].error);
			}
			if(this.uploadSuccess+this.uploadFailed == this.uploadTotal){
				$('form').prepend("<p id='notice' class='"+((this.uploadFailed == 0)?"success":"error")+"'>From a total of "+this.uploadTotal+" entries, "+this.uploadSuccess+" succeeded, and "+this.uploadFailed+" failed.");
			}
		},
		
		
	
	};

	var uploader = new plupload.Uploader({
		runtimes : 'html5',
		browse_button : 'browse',
		max_file_size : '10mb',
		url:upload_many.uploadUrl,
		multipart:true,
		chunk_size:'1mb',
		
		//TODO: make settting.
		resize : {width : 100, height:100, quality : 90},
		
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

