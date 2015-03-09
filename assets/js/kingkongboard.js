jQuery(document).ready(function(){

	if(jQuery("#kingkongboard-wrapper").find(".comments-list").html()){
		//jQuery("#kingkongboard-wrapper").find(".comments-list").html('ok');
		var entry_id = jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val();
		if(entry_id){
			kingkongboard_comment_list(entry_id);
		}
	}

	jQuery(".button-verify-submit").click(function(){
		var pwd 		= jQuery("#kingkongboard-wrapper").find("[name=kingkongboard-verifying-pwd]").val();
		var view 		= jQuery("#kingkongboard-wrapper").find("[name=view_type]").val();
		var entry_id 	= jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val();
		var board_id 	= jQuery("#kingkongboard-wrapper").find("[name=board_id]").val();
		if(pwd){
			var data = {
				'action' 	: 'kingkongboard_password_verifying',
				'password'	: pwd,
				'view'		: view,
				'entry_id'	: entry_id,
				'board_id'	: board_id
			}

			jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
				if(response.status == "success"){
					switch(view){
						case "read" :
							jQuery("#kingkongboard_verify_form").submit();							
						break;

						case "delete" :
							location.href = response.redirect;
						break;
					}
				}
			});			
		} else {
			return false;
		}
	});

	jQuery("#kingkongboard-wrapper").find(".button-entry-delete").click(function(){
		if(confirm("삭제 하시겠습니까?") == true){
			location.href = jQuery(this).attr("data");
		}
	});

	jQuery("#kingkongboard-wrapper").find(".button-comment-save").click(function(){
		var user_status = jQuery("#kingkongboard-wrapper").find("[name=user_status]").val();
		var writer 		= jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_writer]").val();
		var email 		= jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_email]").val();
		var content 	= jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_content]").val();
		var parent 		= 0;
		if(user_status == 0){
			if(!writer){
				alert('작성자 명을 기입하시기 바랍니다.');
				return false;
			} else if (!email){
				alert('이메일을 기입하시기 바랍니다.');
				return false;
			} else if (!content) {
				alert('내용을 기입하시기 바랍니다.');
				return false;
			} else {
				kingkongboard_save_comment(parent);
			}
		} else {
			if(!content){
				alert('내용을 기입하시기 바랍니다.');
				return false;
			} else {
				kingkongboard_save_comment(parent);
			}
		}
	});

	jQuery("#kingkongboard-wrapper").find(".button-save").click(function(){

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
		var idu 		= jQuery("#kingkongboard-wrapper").find("[name=idu]").val();
		if(!idu){
			var pwd    		= jQuery("#kingkongboard-wrapper").find("[name=entry_password]").val();			
		} else {
			var pwd = null
		}

		if(!title){
			alert('제목은 반드시 기입하셔야 합니다.');
			return false;
		} else if ( user_status == 0 && !pwd ){
			alert('글 수정/삭제를 위한 비밀번호를 반드시 기입하셔야 합니다.');
			return false;
		} else if( user_status == 0 && !writer ){
			alert('작성자명을 기입하시기 바랍니다.');
			return false;
		} else if (!content){
			alert('내용은 반드시 기입하셔야 합니다.');
			return false;
		} else {
			if(!jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val()){
				kingkongboard_entry_iframe_controller('proc');
			} else {
				
				var data = {
					'action' 	: 'kingkongboard_entry_password_check',
					'entry_id'	: jQuery("#kingkongboard-wrapper").find("[name=entry_id]").val(),
					'entry_pwd' : pwd
				};
				
				jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
					if(response.status == "success"){
						kingkongboard_entry_iframe_controller('modify_proc');
					} else {
						alert(response.message);
					}
				});
			}
		}
	});

	jQuery("#kingkongboard-wrapper").find(".attachment-controller").click(function(){
		jQuery("#kingkongboard-wrapper").find(".attachment-tr").after("<tr><th>첨부파일</th><td><input type='file' name='entry_file[]'></td></tr>");
	});

	jQuery("#kingkongboard-wrapper").find(".entry_attach_remove").click(function(){
		jQuery(this).parent().css("background", "yellow");
		jQuery(this).parent().animate({ opacity : 0 }, {duration:500, complete:function(){
			jQuery(this).remove();
		}})
	});

});

function kingkongboard_save_comment(parent){
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
		'comment_parent' : parent
	}

	jQuery.post(ajax_kingkongboard.ajax_url, data, function(response) {
		kingkongboard_comment_list(entry_id);
		var originCount = jQuery("#kingkongboard-wrapper").find(".total-comments-count").html();
		var originCount = (originCount*1) + 1;
		jQuery("#kingkongboard-wrapper").find(".total-comments-count").html(originCount);
		jQuery("#kingkongboard-wrapper").find("[name=kkb_comment_content]").val("");
	});

}

function kingkongboard_entry_iframe_controller(type){

	var page_uri 	= jQuery("#kingkongboard-wrapper").find("[name=page_uri]").val();
	var prm_pfx 	= jQuery("#kingkongboard-wrapper").find("[name=pfx]").val();

	if(!jQuery("#kingkongboard-wrapper").find("#_procFrame").html()){
		jQuery("#kingkongboard-wrapper").append('<iframe name="_procFrame" id="_procFrame" width="0" height="0" style="" scrolling="no" frameborder="0" marginheight="0" marginheight="0" marginwidth="0"></iframe>');
	}
	writeForm.target = "_procFrame";
	writeForm.action = page_uri+prm_pfx+"view="+type;
	writeForm.submit();	
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
		guest_input = '<ul class="comments-reply-ul">';
		guest_input += '<li>작성자</li>';
		guest_input += '<li><input class="kkb-comment-input kkb-comment-writer" type="text" name="kkb_comment_writer"></li>';
		guest_input += '<li>이메일</li>';
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
		jQuery(".each-comment-reply").remove();
		jQuery(this).parent().parent().parent().parent().parent().parent().after('<div class="each-comment-reply">'+default_input+'<input type="hidden" name="comment_parent" value="'+parent_id+'"></div>');
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
		if(user_status == 0){
			if(!writer){
				alert('작성자 명을 기입하시기 바랍니다.');
				return false;
			} else if (!email){
				alert('이메일을 기입하시기 바랍니다.');
				return false;
			} else if (!content) {
				alert('내용을 기입하시기 바랍니다.');
				return false;
			} else {
				kingkongboard_save_comment(parent);
			}
		} else {
			if(!content){
				alert('내용을 기입하시기 바랍니다.');
				return false;
			} else {
				kingkongboard_save_comment(parent);
			}
		}
	});	
}

