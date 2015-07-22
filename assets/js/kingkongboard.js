jQuery(document).ready(function(){

	kingkongboard_comment_reply_enable();

    jQuery('.file-upload .text').val('첨부할 파일을 선택하세요.');
    
    jQuery('.file-upload .file').change(function(){
        var i = jQuery(this).val();
        jQuery(this).parent().parent().find('.text').val(i);
    });

    jQuery(".added-file-box").find(".added-file-remove").click(function(){
    	jQuery(this).parent().parent().animate({'opacity' : 0}, {duration:500, complete:function(){
    		jQuery(this).remove();
    	}});
    });

	jQuery(".popular-tags").find(".btn-popular-tags").toggle(function(){
		jQuery(".popular-tag-list").show();
		jQuery(".popular-tag-list").stop().animate({'height' : '60px'}, {duration:400});
		jQuery(this).html(jQuery(this).attr('data-fold'));
	}, function(){
		jQuery(".popular-tag-list").stop().animate({'height' : '0px'}, {duration:400, complete:function(){
			jQuery(".popular-tag-list").hide();
		}});
		jQuery(this).html(jQuery(this).attr('data-open'));
	});

	jQuery(".each-tag").find(".check-each-tag").change(function(){
		if(jQuery(this).prop("checked") == true){
			var entry_tags = jQuery(".write-span").find("[name=entry_tags]").val();
			if(entry_tags.indexOf(jQuery(this).val()) == -1){
				if(entry_tags.charAt(entry_tags.length-1) == ',' || !entry_tags.charAt(entry_tags.length-1)){
					jQuery(".write-span").find("[name=entry_tags]").val(entry_tags+jQuery(this).val());
				} else {
					jQuery(".write-span").find("[name=entry_tags]").val(entry_tags+','+jQuery(this).val());
				}
			}
		} else {
			var entry_tags = jQuery(".write-span").find("[name=entry_tags]").val();
			if(entry_tags.indexOf(jQuery(this).val()) != -1){
				if(entry_tags.indexOf(jQuery(this).val()+',') != -1){
					entry_tags = entry_tags.replace(jQuery(this).val()+',', '');
				} else {
					if(entry_tags.indexOf(','+jQuery(this).val()) != -1){
						entry_tags = entry_tags.replace(','+jQuery(this).val(), '');
					} else {
						entry_tags = entry_tags.replace(jQuery(this).val(), '');
					}
				}

				jQuery(".write-span").find("[name=entry_tags]").val(entry_tags);
			}
		}
	});

	jQuery(".kkb-check-comment-delete").click(function(){
		var cid = jQuery(this).attr("data-id");
		var data = {
			'action' 	: 'kkb_comment_delete',
			'cid'		: cid
		}
		jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
			if(response.status == 'check'){
				location.href = response.url;
			} else if (response.status == 'success'){
				location.reload();
			} else {
				alert(response.message);
			}
		});
	});

	jQuery(".comment-checker-input").find(".kkb-comment-delete").click(function(){
		var inputpwd = jQuery(this).parent().find("[name=inputpwd]").val();
		var cid 	 = jQuery(this).attr("data-cid");
		if(!inputpwd){
			alert('비밀번호를 입력 해 주시기 바랍니다.');
			return false;
		} else {
			var data = {
				'action' 	: 'kkb_comment_delete',
				'cid'		: cid,
				'password'	: inputpwd
			}
			jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
				if (response.status == 'success'){
					location.href = response.url;
				} else {
					alert(response.message);
				}
			});
		}
	});

	jQuery(".btn-kkb-comment-reply").click(function(){
		var origin = jQuery(this).parent().parent().parent();
		origin.find(".comment-reply").show();
		origin.find(".comment-reply").animate({'opacity' : 1}, 300);
		origin.find(".comment-reply").find("textarea").focus();
	});

	jQuery(".btn-kkb-comment-reply-close").click(function(){
		jQuery(this).parent().parent().parent().stop().animate({'opacity' : 0}, {duration:500, complete:function(){
			jQuery(this).hide();
		}});
	});

	jQuery(".list-wrapper").find(".each-comment").hover(function(){
		jQuery(this).find(".comment-controller").stop().animate({'opacity' : 1}, 100);
	}, function(){
		jQuery(this).find(".comment-controller").stop().animate({'opacity' : 0}, 100);
	});

	jQuery(".comment-editor-text").find(".comment-editor-textarea").toggle(function(){
		var origin = jQuery(this).parent().parent().parent().find(".comment-editor-content-input");
		jQuery(this).parent().parent().parent().find(".comment-editor-content-input").show();
		jQuery(this).parent().parent().parent().find(".comment-editor-content-input").stop().animate({'height' : '32px'}, {duration:300, easing:'easeOutBack', complete:function(){
			origin.css("overflow-y", "visible");
		}});
	}, function(){
		var origin = jQuery(this).parent().parent().parent().find(".comment-editor-content-input");
		origin.css("overflow-y", "hidden");
		origin.stop().animate( {'height' : '0px'}, {duration:100, complete:function(){
			origin.hide();
		}});
	});

	jQuery(".kingkongboard-search").find(".btn-kkb-search").click(function(){
		jQuery(this).find("label").hide();
		jQuery(this).find(".kkblc-search").css("position", "absolute");
		jQuery(this).find(".kkblc-search").css("right", "10px");
		jQuery(this).find(".kkblc-search").css("top", "6px");
		jQuery(this).parent().find(".kkb-section-span").find("select").show();
		jQuery(this).animate({'width' : '100px'}, {duration:1000, easing:'easeOutBack'});
	});
 
	/* entry delete */
	jQuery(".kkb-entry-delete").click(function(){
		var board_id = jQuery(this).attr("data-board");
		var entry_id = jQuery(this).attr("data-id");
		if(entry_id){
			var data = {
				'action' 	: 'kkb_entry_delete',
				'board_id'	: board_id,
				'entry_id'	: entry_id
			}
			jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
				if(response.status == 'success'){
					location.href = response.url;
				} else {
					alert(response.message);
				}
			});
		} else {
			return false;
		}
	});

	/* delete validation */
	jQuery(".kkb-delete-validation").click(function(){
		var pwd 	 = jQuery("[name=validate_pwd]").val();
		var board_id = jQuery(this).attr("data-board");
		var entry_id = jQuery(this).attr("data-id");
		alert(pwd+board_id+entry_id);
		var data = {
			'action' 	: 'kkb_entry_delete_before_validation',
			'board_id'	: board_id,
			'entry_id'	: entry_id,
			'pwd'		: pwd
		}
		jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
			if(response.status == 'success'){
				location.href = response.url;
			} else {
				alert(response.message);
			}
		});		
	});

	/* ajax reload */
	jQuery("#kingkongboard-wrapper").find(".comment-delete-button").click(function(event){
		event.stopPropagation();
		var message = jQuery(this).attr("message");
		var commentID = jQuery(this).attr("data");
		var entry_id  = jQuery(this).attr("data-entry");
		if(confirm(message) == true){
			var data = {
				'action' 	: 'kkb_comment_delete',
				'comment_id': commentID
			}
			jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
				if(response.status == 'success'){
					kingkongboard_comment_list(entry_id);
					var total = jQuery(".comments-count").find(".total-comments-count").html();
						total = total * 1;
						total = total - 1;

					jQuery(".comments-count").find(".total-comments-count").html(total);
				} else {
					alert(response.message);
				}
			});			
		}
	});

	/* page refresh */
	jQuery(".button-kkb-comment-remove").click(function(){
		var message   = jQuery(this).attr("message");
		var commentID = jQuery(this).attr("comment-id");
		if(confirm(message) == true){
			var data = {
				'action' 	: 'kkb_comment_delete',
				'comment_id': commentID
			}

			jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
				if(response.status == 'success'){
					location.reload();
				} else {
					alert(response.message);
				}
			});
		} else {
			return false;
		}
	});

	jQuery("#kingkongboard-wrapper").find(".button-entry-delete").click(function(){
		location.href = jQuery(this).attr("data");
	});

	jQuery("#kingkongboard-wrapper").find(".button-save").click(function(){
		var origin = jQuery(this);
		origin.hide();
		jQuery(".write-save-loading").css("opacity", 1);
		switch(jQuery("#kingkongboard-wrapper").find("[name=editor_style]").val()){
			case "wp_editor" :
				tinyMCE.triggerSave();
			break;

			case "se2" :
				oEditors.getById["entry_content"].exec("UPDATE_CONTENTS_FIELD", []);
			break;
		}
 
		var title 		= jQuery("#kingkongboard-wrapper").find("[name=entry_title]").val();
		var secret 		= jQuery("#kingkongboard-wrapper").find("[name=entry_secret]").prop("checked");
		var writer 		= jQuery("#kingkongboard-wrapper").find("[name=entry_writer]").val();
		var content 	= jQuery("#kingkongboard-wrapper").find("[name=entry_content]").val();
		var board_id 	= jQuery("#kingkongboard-wrapper").find("[name=board_id]").val();
		var user_status = jQuery("#kingkongboard-wrapper").find("[name=user_status]").val();
		var pwd  		= jQuery("#kingkongboard-wrapper").find("[name=entry_password]").val();
		var entry_id 	= jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val();
		var board_id 	= jQuery("#kingkongboard-wrapper").find("[name=board_id]").val();
		var thumbnail 	= jQuery("#kingkongboard-wrapper").find(".thumbnail-tr").find("input").val();

		if(entry_id == undefined){
			entry_id = null;
		}
		if(pwd == undefined){
			pwd = null;
		}
 
		var data = {
			'action' 		: 'kingkongboard_entry_validation',
			'data'			: jQuery("#writeForm").serialize(),
			'thumbnail'		: thumbnail,
			'entry_id'		: entry_id
		};
		jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {

			if(response.status == "failed" || response.status == undefined){
				alert(response.message);
				jQuery(".write-save-loading").css("opacity", 0);
				origin.show();
			} else {
				jQuery(".write-save-loading").css("opacity", 1);
				if(!jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val()){
					jQuery("#writeForm").submit();
				} else {
					var data = {
						'action' 		: 'kingkongboard_entry_password_check',
						'entry_id'		: jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val(),
						'entry_pwd' 	: pwd
					};
						
					jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
						if(response.status == "success"){
							jQuery("#writeForm").submit();
						} else {
							alert(response.message);
							jQuery(".write-save-loading").css("opacity", 0);
							origin.show();
						}
					});
				}
			}
		});
	});
 
	jQuery(".rows-more").find(".btn-attach-more").click(function(){
		var limit = jQuery(this).attr("data-limit");
		var error = jQuery(this).attr("data-error");
		if(jQuery(".kkb-write-wrapper").find(".attach-box").length < limit){
			jQuery(".kkb-write-wrapper").find(".attach-box-1").clone().insertBefore(".attach-more");
			attach_box_class_changer();
		} else {
			alert(error);
			return false;
		}
	});

});

function attach_box_class_changer(){
	jQuery(".kkb-write-wrapper").find(".attach-box").each(function(i){
		jQuery(this).attr("class", "write-box attach-box attach-box-"+(i+1));
		jQuery(this).find("label").find("span").html(jQuery(this).find("label").find("span").attr("data-title")+' '+(i+1));
	});
}

function kingkongboard_save_comment(origin, parent){
	var user_status = jQuery("#kingkongboard-wrapper").find("[name=user_status]").val();
	var writer 		= jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_writer]").val();
	var email 		= jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_email]").val();
	var content 	= jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_content]").val();
	var entry_id 	= jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val();
	var data = {
		'action' 		 : 'kingkongboard_comment_save',
		'writer'		 : writer,
		'email' 		 : email,
		'content'		 : content,
		'entry_id'		 : entry_id,
		'comment_parent' : parent,
		'comment_origin' : origin
	}

	jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
		kingkongboard_comment_list(entry_id);
		var originCount = jQuery("#kingkongboard-wrapper").find(".total-comments-count").html();
		var originCount = (originCount*1) + 1;
		jQuery("#kingkongboard-wrapper").find(".total-comments-count").html(originCount);
		jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_content]").val("");
	});

}

function kingkongboard_comment_list(entry_id){

	var data = {
		'action' 	: 'kingkongboard_comment_list',
		'entry_id'	: entry_id,
	}

	jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
		jQuery("#kingkongboard-wrapper").find(".comments-list").html(response);
		kingkongboard_comment_reply_enable();
	});	
}


function kingkongboard_comment_reply_enable(){
	var user_status = jQuery("#kingkongboard-wrapper").find("[name=user_status]").val();
	var plugins_url = jQuery("#kingkongboard-wrapper").find(".plugins_url").val();
	var default_input;
	var guest_input;
	if(user_status == 0){
		var writer_text = jQuery(".comments-add-ul").find(".comments-add-writer").html();
		var email_text 	= jQuery(".comments-add-ul").find(".comments-add-email").html();
		guest_input = '<ul class="comments-reply-ul">';
		guest_input += '<li>'+writer_text+'</li>';
		guest_input += '<li><input class="kkb-comment-input kkb-comment-writer" type="text" name="kkb_comment_writer"></li>';
		guest_input += '<li>'+email_text+'</li>';
		guest_input += '<li><input class="kkb-comment-input kkb-comment-email" type="text" name="kkb_comment_email"></li>';
		guest_input += '</ul>';
	} else {
		guest_input = '';
	}

		default_input = '<img src="'+plugins_url+'/assets/images/icon-comment-reply.png" class="kkb_comment_reply_icon">'+guest_input;
	  	default_input += '<ul class="kkb-comment-input-ul">';
	  	default_input += '<li class="kkb-comment-content-li"><table class="kkb-comment-content-table"><tr><td class="kkb-comment-content-td"><textarea class="kkb-comment-input kkb-comment-content" height="30px" name="kkb_comment_content"></textarea></td><td class="kkb-comment-button-td"><input type="image" src="'+plugins_url+'/assets/images/button-ok.png" class="button-comment-reply" style="border:0;margin-left:6px"></td></tr></table></li>';
	  	default_input += '</ul>';

	jQuery("#kingkongboard-wrapper").find(".comment-reply-button").click(function(){
		var parent_id = jQuery(this).attr("data");
		var origin_id = jQuery(this).attr("data-origin");
		jQuery(".each-comment-reply").remove();
		jQuery(this).parent().parent().parent().parent().parent().parent().after('<div class="each-comment-reply">'+default_input+'<input type="hidden" name="comment_parent" value="'+parent_id+'"><input type="hidden" name="comment_origin" value="'+origin_id+'"></div>');
		kingkongboard_comment_reply_save();
	});

	
}

function kingkongboard_comment_reply_save(){
	jQuery("#kingkongboard-wrapper").find(".button-comment-reply").click(function(){
		var user_status = jQuery("#kingkongboard-wrapper").find("[name=user_status]").val();
		var writer 		= jQuery(".each-comment-reply").find("[name=kkb_comment_writer]").val();
		var email 		= jQuery(".each-comment-reply").find("[name=kkb_comment_email]").val();
		var content 	= jQuery(".each-comment-reply").find("[name=kkb_comment_content]").val();
		var parent 		= jQuery(".each-comment-reply").find("[name=comment_parent]").val();
		var origin 		= jQuery(".each-comment-reply").find("[name=comment_origin]").val();
		if(writer == undefined){
			writer = null;
		}
		if(email == undefined){
			email = null;
		}
		var data = {
			'action' 		: 'kingkongboard_entry_comment_validation',
			'writer'		: writer,
			'email'			: email,
			'content' 		: content
		};

		jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
			if(response.status == "failed"){
				alert(response.message);
			} else {
				kingkongboard_save_comment(origin, parent);
			}
		});
	});	
}

function kkb_comment_submit(){
	var content = jQuery(".comment-editor-text").find(".comment-editor-textarea").val();
	var writer 	= jQuery(".comment-editor-content-input").find("[name=writer]").val();
	var pwd 	= jQuery(".comment-editor-content-input").find("[name=password]").val();
	var email  	= jQuery(".comment-editor-content-input").find("[name=email]").val();
	var status  = jQuery("[name=user_status]").val();

	if(!content){
		alert('내용을 입력하시기 바랍니다.');
		return false;
	}
	if(status == 0){
		if(!writer){
			alert('작성자를 기입하시기 바랍니다.');
			return false;
		}
		if(!pwd){
			alert('비밀번호를 기입하시기 바랍니다.');
			return false;
		}
		if(!email){
			alert('이메일을 기입하시기 바랍니다.');
			return false;
		}
	}
	return true;
}

function kkb_comment_reply_submit(cid){

	var content = jQuery(".comment-reply-"+cid).find("[name=kkb_comment_content]").val();
	var writer 	= jQuery(".comment-reply-"+cid).find("[name=writer]").val();
	var pwd 	= jQuery(".comment-reply-"+cid).find("[name=password]").val();
	var email  	= jQuery(".comment-reply-"+cid).find("[name=email]").val();
	var status  = jQuery(".comment-reply-"+cid).find("[name=user_status]").val();

	if(!content){
		alert('내용을 입력하시기 바랍니다.');
		return false;
	}
	if(status == 0){
		if(!writer){
			alert('작성자를 기입하시기 바랍니다.');
			return false;
		}
		if(!pwd){
			alert('비밀번호를 기입하시기 바랍니다.');
			return false;
		}
		if(!email){
			alert('이메일을 기입하시기 바랍니다.');
			return false;
		}
	}
	return true;
}



