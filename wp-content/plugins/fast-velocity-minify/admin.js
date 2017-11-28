(function($){
	
	$(function(){
		
		// make some checkboxes behave like radio boxes
		$('input.jsprocessor').on('change', function() {
			$('input.jsprocessor').not(this).prop('checked', false);  
		});
		
		
		
		// disable collapse
		$('.postbox h3, .postbox .handlediv').unbind('click.postboxes');
		
		// variables
		var $fastvelocity_min_processed = $('#fastvelocity_min_processed'),
		$fastvelocity_min_jsprocessed = $('#fastvelocity_min_jsprocessed',$fastvelocity_min_processed),
		$fastvelocity_min_jsprocessed_ul = $('ul',$fastvelocity_min_jsprocessed),
		$fastvelocity_min_cssprocessed = $('#fastvelocity_min_cssprocessed',$fastvelocity_min_processed),
		$fastvelocity_min_cssprocessed_ul = $('ul',$fastvelocity_min_cssprocessed),
		$fastvelocity_min_noprocessed = $('#fastvelocity_min_noprocessed'),
		timeout = null,
		stamp = null;
		
		$($fastvelocity_min_processed).on('click','.log',function(e){
			e.preventDefault();
			$(this).parent().nextAll('pre').slideToggle();
		});
		
		$($fastvelocity_min_processed).on('click','.purge',function(e){
			e.preventDefault();
			
			getFiles({purge:$(this).attr('href').substr(1)});
			
			$(this).parent().parent().remove();
		});
		
		function getFiles(extra) {
			stamp = new Date().getTime();
			var data = {
				'action': 'fastvelocity_min_files',
				'stamp': stamp
			};
			if(extra) {
				for (var attrname in extra) { data[attrname] = extra[attrname]; }
			}
	
			
			$.post(ajaxurl, data, function(response) {

				if(stamp == response.stamp) {
					if(response.js.length > 0) { 
						$fastvelocity_min_jsprocessed.show();
						
						$(response.js).each(function(){
							var $li = $fastvelocity_min_jsprocessed_ul.find('li.'+this.hash);
							if($li.length > 0) {
								if($li.find('pre').html() != this.log) {
									$li.find('pre').html(this.log);
								}
							} else {
								$fastvelocity_min_jsprocessed_ul.append('<li class="'+this.hash+'"><span class="filename">'+this.filename+'</span> <span class="actions"><a href="#" class="log button button-primary">View Log</a> <a href="#'+this.hash+'" class="button button-secondary purge">Delete</a></span><pre>'+this.log+'</pre></li><div class="clear"></div>');
							}
						});
						
					}
					if(response.css.length > 0) {
																		
						$(response.css).each(function(){
							var $li = $fastvelocity_min_cssprocessed_ul.find('li.'+this.hash);
							if($li.length > 0) {
								if($li.find('pre').html() != this.log) {
									$li.find('pre').html(this.log);
								}
							} else {
								$fastvelocity_min_cssprocessed_ul.append('<li class="'+this.hash+'"><span class="filename">'+this.filename+'</span> <span class="actions"><a href="#" class="log button button-primary">View Log</a> <a href="#'+this.hash+'" class="button button-secondary purge">Delete</a></span><pre>'+this.log+'</pre></li><div class="clear"></div>');
							}
						});
					}
					
					// check for new files
					timeout = setTimeout(getFiles, 2500);
				}
			});
		}
		
		getFiles();
		
	});

})(jQuery);