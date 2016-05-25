(function ( $ ) {
	"use strict";

	$(function () {

		var isAjaxRunning = false,			
			pluginUrl = numixslider_ajax_vars.pluginurl,			
			saveText = numixslider_ajax_vars.saveText,
			createText = numixslider_ajax_vars.createText,			
			deleteDialogText = numixslider_ajax_vars.deleteDialogText,
			savingText = numixslider_ajax_vars.savingText,
			savedText = numixslider_ajax_vars.savedText,
			unsavedText = numixslider_ajax_vars.unsavedText,
			autoText = numixslider_ajax_vars.autoText,			
			emptyTaxonomiesText = numixslider_ajax_vars.emptyTaxonomiesText;

		var dropDownEnabled = false;
		var optionsContainer = $("#numixlisder-options");
		var saveButton = $("#save-slider");
		var saveProgressButton = $('#save-progress');
		var isUnsaved = false;
		

		var tableTc = $('table.numix-table');
		var deleteBtn = $('.delete-nslider-btn').click(function(e) {
			e.preventDefault();	
			if( confirm(deleteDialogText) ) {
				window.location = $(this).attr('data-protected-href');			
			} 
		});			
		if(tableTc && tableTc.length > 0) {			
			return;
		}	
		
		$('input').bind('click', function(e) {
			unsaved();
		
		});
		
		$( document ).ready(function() {
			
			updateTaxonomies();
		    $('#autoplay').trigger('change');
		    $('#arrows_nav').trigger('change');
		    $('#infinite').trigger('change');

		});

		$('#autoplay').change(function(){
			if($(this).prop('checked'))
			{
				$('#autoplay_options').show();
			}
			else
			{
				$('#autoplay_options').hide();	
			}

		});
		$('#arrows_nav').change(function(){
			if($(this).prop('checked'))
			{
				$('#arrows_option').show();
			}
			else
			{
				$('#arrows_option').hide();	
			}

		});
		$('#infinite').change(function(){
			if($(this).prop('checked'))
			{
				$('#loop_option').hide();
			}
			else
			{
				$('#loop_option').show();	
			}

		});
		

		function unsaved() {
			if(!isUnsaved) {
				saveProgressButton.addClass('unsaved');
				saveProgressButton.html(unsavedText);
				isUnsaved = true;
			}			
		}
		var tooltipDefault = {
			content: {
				attr: 'data-help'
			},
			position: {
				at: 'center left', 
				my: 'center right'
			},
			style: {
				classes: 'qtip-rounded qtip-shadow qtip-dark'
			}
		};
		optionsContainer.find('label').each( function( ) {			
			var help = $(this).attr( 'data-help' );
			if ( help != undefined && help != '' ) {
				$(this).qtip(tooltipDefault);
            }
		});
		if(saveButton) {
			if(!(sliderID >= 0)) {
				unsaved();
			}
			saveButton.click(function(e) {
				e.preventDefault();
				saveSlider();
			});
		}
		
		$('#numixlisder-options').delegate(".postbox h3, .postbox .handlediv","click.postboxes", function () {
			$('#numixlisder-options').find('.postbox').addClass('closed').removeClass('open');
            self.lastOpen = $(this).parent(".postbox").addClass("open").removeClass('closed');
        });
        
		function generateSliderJSOptions() {
			
			var opts = form2js('numixlisder-options');	
            return opts;
		}

        function saveSlider() {
			
			if (!isAjaxRunning) {
				isAjaxRunning = true;
			
				/*var sHTMLStr = generateSliderHTML();
				sHTMLStr = $('<div>').append(sHTMLStr.clone()).remove().html();*/
				
				
				var jsonOpts = JSON.stringify(generateSliderJSOptions());
				
				
				saveProgressButton.removeClass('ajax-saved').html('');			
			
				saveButton.html(savingText);	
				
				
				var post_categories = $("#post_categories_select :selected");
				var opt_parent;
				var taxonomies_obj = {};
				var insert_index = 0;

				

				$.each(post_categories, function(index, value) {
					opt_parent = $(value).parent().attr('id');

					if(!taxonomies_obj[opt_parent]) {
						taxonomies_obj[opt_parent] = [];
					}
					taxonomies_obj[opt_parent].push($(value).attr('value'));
					
				});


				$.ajax({
					url: numixslider_ajax_vars.ajaxurl,
					type: 'post',
					data: {
						action : 'numix_slider_save',						
						id : sliderID,
						name : $('#slider_name').val(),
						width : $('#width').val(),
						height: $('#height').val(),						
						max_posts : $('#max_posts_include').val(),
						post_type : "post",
						post_categories : JSON.stringify(taxonomies_obj),
						post_orderby : $('.radio-buttons input[name=post-order-radio]:checked').val(),
						post_order : $('#post_order').val(),
						js_settings : jsonOpts,						
						post_relation : $('#post_taxonomy_relation').val(),
						arrows_auto_hide : $('#arrows_auto_hide').prop("checked"),
						activate_on_click : !$('#activate_on_click').prop("checked"),
						display_post_title : $('#display_post_title').prop("checked"),
						hide_posts : $('#hide_posts').prop("checked"),
						slider_bottom_nav_type : $('#slider_bottom_nav_type').val(),
						numixslider_ajax_nonce : numixslider_ajax_vars.numixslider_ajax_nonce
					},
					complete: function(data) {	
						
						if(!(sliderID >= 0)) {
							if(parseInt(data.responseText, 10) > -1) {
								sliderID = parseInt(data.responseText, 10);							
							} 
							window.location.href = (numixslider_ajax_vars.admin_edit_url + sliderID);
							
						} else {
							if(parseInt(data.responseText, 10) > -1) {
								sliderID = parseInt(data.responseText, 10);							
							}
						}
						
						saveButton.html(saveText);
						isUnsaved = false;
						saveProgressButton.html(savedText).addClass('ajax-saved').removeClass('unsaved');					
						
						isAjaxRunning = false;
					},
				    error: function(jqXHR, textStatus, errorThrown) { isAjaxRunning = false; alert(textStatus); alert(errorThrown); }
				});
			}
		}
		if(sliderSettings) {
			populate($('#numixlisder-options'), jQuery.parseJSON(sliderSettings));		
		}
		function populate(frm, data) {
			var input;
			$.each(data, function(key, value){
				input =  $('[name='+key+']', frm);
				if(input.is(':checkbox')) {
					if(key === 'activateOnClick') {
						if(value === true)
							input.attr('checked', false);
						else
							input.attr('checked', true);
					} else {
						input.attr('checked', value);
					}
				} else {
					input.val(value);
				}
			});
		}
		updateTaxonomies();
		function updateTaxonomies() {
			if (!isAjaxRunning) {
				isAjaxRunning = true;
				
				$('#post_types_select').attr('disabled', 'disabled');
				if(dropDownEnabled) {
					$("#post_categories_select").dropdownchecklist('disable');
				}
				

				
				$.ajax({
					url: numixslider_ajax_vars.ajaxurl,
					type: 'post',
					data: {
						action : 'numix_slider_display_taxonomies',
						id : sliderID,
						post_type : 'post',
						numixslider_ajax_nonce : numixslider_ajax_vars.numixslider_ajax_nonce
					},
					complete: function(data) {		

						if(dropDownEnabled) {
							$("#post_categories_select").dropdownchecklist('destroy');
						}
						$("#post_categories_select").empty();
						var newData = data.responseText;
						
						if(newData) {
							$("#post_categories_select").html(newData);
							$("#post_categories_select").dropdownchecklist({emptyText: autoText, width: 300, onItemClick:function() { unsaved(); }});
							$("#post_taxonomy_relation").show();
						} else {
							$("#post_categories_select").dropdownchecklist({emptyText: emptyTaxonomiesText, width: 300, onItemClick:function() { unsaved(); }});
							$("#post_categories_select").dropdownchecklist('disable');
							$("#post_taxonomy_relation").hide();
						}
						dropDownEnabled = true;
						
						
						isAjaxRunning = false;
					},
				    error: function(jqXHR, textStatus, errorThrown) { isAjaxRunning = false; alert(textStatus); alert(errorThrown); }
				});
				
			}
		
		}

	});

}(jQuery));