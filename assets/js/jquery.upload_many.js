var upload_many;

(function($) {
	$.fn.upload_many = function(options, callback) {	
			
		options = $.extend({
		}, options);
				
		return $(this).each(function(){
			
			var wrapper = $(this);
						
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
			}).remove();			
			
			$('<a class="button"><b>Select File</b></a>').appendTo(wrapper.find('span').not(':has(input[type="hidden"])')).append('<span id="flash_holder">&nbsp;</span>');
			var dwidth = wrapper.find('a.button').outerWidth();
			var dheight = wrapper.find('a.button').outerHeight();
		
			var flashvars = {
				filterDesc: "Allowed Files",
				filterFiles:'*.jpg;*.png;*.gif',
				url: $('form').attr('action'),
			};
			var params = {
				wmode:'transparent',
			};
			var attributes = {
				wmode:'transparent',
			};

			swfobject.embedSWF(Symphony.WEBSITE + "/extensions/upload_many/assets/flash/uploader.swf?"+document.cookie, "flash_holder", dwidth, dheight, "9.0.0",Symphony.WEBSITE + "/extensions/upload_many/assets/flash/expressInstall.swf", flashvars, params, attributes);
			
			wrapper.find('label.field-upload_many:has(a.button) em').remove();
			
			$('<span id="list"><i>No file selected</i></span>').appendTo(wrapper.find('label.file > span'));
			
		});
		
		
	}
	
	upload_many = {
		
		fieldName: null,
		
		oldFile: null,
		
		fileList: [],
		
		renderList: function(){
		},
		
		addFile: function(filename){
			this.fileList.push(filename);
			$('#list').append('<span id="file-'+filename+'">'+filename+'<em>Remove file</em></span>');
			$('#list i').remove();
			$("#flash_holder").get(0).upload();
		},
		
		resetList: function(e){
			this.fileList.length = 0;
			$("#list").empty();
			$("#list").append('<i>No file selected</i>');
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
	}
	
	$(document).ready(function() {
		$('div.field-upload_many').upload_many();
	});
})(jQuery);

