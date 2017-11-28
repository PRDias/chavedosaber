jQuery(document).ready(function($) {
	if(!$('body').hasClass('widgets_access')){
		sllwSetupList($);
		$('.sllw-edit-item').addClass('toggled-off');
		sllwSetupHandlers($);
	}
	
	$(document).ajaxSuccess(function() {
		sllwSetupList($);
		$('.sllw-edit-item').addClass('toggled-off');
	});
});

function sllwSetupList($){
	$( ".simple-link-list" ).sortable({
		items: '.list-item',
		opacity: 0.6,
		cursor: 'n-resize',
		axis: 'y',
		handle: '.moving-handle',
		placeholder: 'sortable-placeholder',
		start: function (event, ui) {
			ui.placeholder.height(ui.helper.height());
		},
		update: function() {
			updateOrder($(this));
		}
	});
	
	$( ".simple-link-list .moving-handle" ).disableSelection();
}


// All Event handlers
function sllwSetupHandlers($){
	$("body").on('click.sllw','.sllw-delete',function() { 
		$(this).parent().parent().fadeOut(500,function(){
			var sllw = $(this).parents(".widget-content");
			$(this).remove();
			sllw.find('.order').val(sllw.find('.simple-link-list').sortable('toArray'));
			var num = sllw.find(".simple-link-list .list-item").length;
			var amount = sllw.find(".amount");
			amount.val(num);
		});
	});
	
	$("body").on('click.sllw','.sllw-add',function() { 
		var sllw = $(this).parent().parent();
		var num = sllw.find('.simple-link-list .list-item').length + 1;
		
		sllw.find('.amount').val(num);
		
		var item = sllw.find('.simple-link-list .list-item:last-child').clone();
		var item_id = item.attr('id');
		item.attr('id',increment_last_num(item_id));

		$('.toggled-off',item).removeClass('toggled-off');
		$('.number',item).html(num);
		$('.item-title',item).html('');
		
		$('label',item).each(function() {
			var for_val = $(this).attr('for');
			$(this).attr('for',increment_last_num(for_val));
		});
		
		$('input',item).each(function() {
			var id_val = $(this).attr('id');
			var name_val = $(this).attr('name');
			$(this).attr('id',increment_last_num(id_val));
			$(this).attr('name',increment_last_num(name_val));
			if($(':checked',this)){
			   $(this).removeAttr('checked');
			}
			$(this).val('');
		});
		
		sllw.find('.simple-link-list').append(item);
		sllw.find('.order').val(sllw.find('.simple-link-list').sortable('toArray'));
	});
	
	$('body').on('click.sllw','.moving-handle', function() {
		$(this).parent().find('.sllw-edit-item').slideToggle(200);
	} );
}

function increment_last_num(v) {
    return v.replace(/[0-9]+(?!.*[0-9])/, function(match) {
        return parseInt(match, 10)+1;
    });
}

function updateOrder(self){
	var sllw = self.parents(".widget-content");
	sllw.find('.order').val(sllw.find('.simple-link-list').sortable('toArray'));
}