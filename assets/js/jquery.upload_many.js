var upload_many;

(function($) {
	$.fn.upload_many = function(options, callback) {	
			
		options = $.extend({
		}, options);
				
		return $(this).each(function(){
			
			var wrapper = $(this);
			upload_many.wrapper = wrapper;
						
			//single or multi?
			var location = window.location + "";
			var many = location.substring(location.length-5);
			
			if(many == 'many/'){
				options.multiple = true;
			}
			
			//replace standard remove file action with my own
			wrapper.find('span em').unbind('click').click(function(){
				//place new remove file handler here
			});
			
			//insert the upload_many upload field
			//TODO: languages
			wrapper.find('input[type=file]').each(function(){
				upload_many.fieldName = $(this).attr("name");
				//wrapper.find("label").attr("name",upload_many.fieldName);
			}).remove();			
			
			$('<a class="button"><b>Select Files</b></a>').appendTo(wrapper.find('span').not(':has(input[type="hidden"])')).append('<span id="flash_holder">&nbsp;</span>');
			var dwidth = wrapper.find('a.button').outerWidth();
			var dheight = wrapper.find('a.button').outerHeight();
			
			var section_handle = location.substring(Symphony.WEBSITE.length).split("/")[3];
		
			var flashvars = {
				filterDesc: "Allowed Files",
				filterFiles:'*.jpg;*.png;*.gif',
				url: Symphony.WEBSITE + '/symphony/extension/upload_many/create/new/'+section_handle+"/?action=true&"+document.cookie,
			};
			var params = {
				wmode:'transparent',
			};
			var attributes = {
				wmode:'transparent',
			};

			swfobject.embedSWF(Symphony.WEBSITE + "/extensions/upload_many/assets/flash/uploader.swf?"+document.cookie, "flash_holder", dwidth, dheight, "9.0.0",Symphony.WEBSITE + "/extensions/upload_many/assets/flash/expressInstall.swf", flashvars, params, attributes);
			
			wrapper.find('label.field-upload_many:has(a.button) em').remove();
			
			$('<span id="list"></span>').appendTo(wrapper.find('label.file > span'));
			
			$('input[type=submit]').click(function(){
				upload_many.startUpload();
				return false;
			});
			
		});
		
		
	}
	
	upload_many = {
		
		fieldName: null,
		
		oldFile: null,
		
		fileList: [],
		
		wrapper: null,
		
		uploadTotal:0,
		uploadSuccess:0,
		uploadFailed:0,
		
		renderList: function(){
		},
		
		addFile: function(id,filename){
			this.fileList.push(filename);
			$('#list').append('<span id="file-'+id+'" name="'+filename+'"><b>'+filename+'</b><em class="button">Remove file</em><span class="upload-progress"></span></span>');
			$('#list i').remove();
			$('#list em').click(function(){
				var size = $(this).parent().attr('id').substr(5);
				var filename = $(this).parent().attr('name');
				$("#flash_holder").get(0).remove(size,filename);
				$(this).parent().remove();
			});
			this.uploadTotal++;
		},
		
		resetList: function(e){
			this.fileList.length = 0;
			$("#list").empty();
			this.uploadSuccess = 0;
			this.uploadFailed = 0;
			this.uploadTotal = 0;
		},
		
		uploadError: function(filename, error){
			$("#list span").addClass('invalid');
		},
		
		getFields: function(){
			var returns = [];
			$("form input, form textarea").each(function(){
				returns.push($(this).attr("name")+'='+$(this).val());
			});
			return returns.join('&');
		},
		
		setStatus: function(filename, totalSize, completeSize){
			var id = 'file-'+totalSize;
			$('#list #'+id).each(function(){
				$(this).find(".upload-progress").width((100*(completeSize/totalSize))+"%");
			});
		},
		
		setErrorField: function(fieldName, errorText){
			$('*[name=fields['+fieldName+']]').parents('div.field:not(.invalid)').append('<p class="errorText">'+errorText+'</p>').addClass("invalid");
		},
		
		processErrorFields: function(jsonObject, fileName, fileSize){
			if(jsonObject.error != ""){
				$('#file-'+fileSize+"[name="+fileName+"]").addClass("error").find(".upload-progress").css("background-color","#DD4422");
				this.uploadFailed++;
				//$('#file-'+fileSize+"[name="+fileName+"]").find("em").show();
			}
			else{
				this.uploadSuccess++;
			}
			for(var i in jsonObject.error){
				this.setErrorField(jsonObject.error[i].fieldName, jsonObject.error[i].error);
			}
			if(this.uploadSuccess+this.uploadFailed == this.uploadTotal){
				$('form').prepend("<p id='notice' class='"+((this.uploadFailed == 0)?"success":"error")+"'>From a total of "+this.uploadTotal+" entries, "+this.uploadSuccess+" succeeded, and "+this.uploadFailed+" failed.");
			}
		},
		
		startUpload: function(){
			this.uploadFailed = 0;
			this.uploadSuccess = 0;
			$("#flash_holder").get(0).upload();
			//files can not be deleted in the middle of the uploading process
			this.wrapper.find("span span#list em").hide();
			$("div.invalid").removeClass("invalid").find("p.errorText").remove();
			$(".upload-progress").css("background-color","#81B934");
			$('form').find("p#notice").remove();
		}
	}
	
	$(document).ready(function() {
		$('div.field-upload_many').upload_many();
	});
})(jQuery);

