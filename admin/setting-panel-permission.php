<?php
  function KingkongBoard_Setting_Panel_Permission(){
    $roles = get_editable_roles();
    if(isset($_GET['id'])){
      $board_managers = get_post_meta(sanitize_text_field($_GET['id']), 'board_managers', true);
    } else {
      $board_managers = null;
    }
    $managers_value = '';
    if($board_managers){
      $board_managers = maybe_unserialize($board_managers);
      foreach($board_managers as $manager){
        $managers_value .= "<div class='each-manager-div'>".$manager."<div class='each-manager-remove'><img src='".KINGKONGBOARD_PLUGINS_URL."/assets/images/icon-close.png' style='width:12px; height:auto'></div><input type='hidden' name='board_manager[]' value='".$manager."'></div>";
      }
    }
    $controller = new kkbController();
?>

    <table>
      <tr>
        <th><?php echo __('관리자 추가', 'kingkongboard');?> :</th>
        <td>
          <input type="text" class="kkb-input manager-input" style="max-width:180px; width:100%">
          <button type="button" class="kkb-icon kkbblue button-add-manager"><i class="kkb-icon kkb-icon-plus" style="position:relative; top:3px"></i><?php echo __('추가하기', 'kingkongboard');?></button>
          <div class="description-container">
            <span class="description"><?php echo __('사용자 아이디를 입력하세요, 콤마(,)로 구분.', 'kingkongboard');?></span>
          </div>       
          <div class="kkb-read-role-box"><?php echo $managers_value;?></div>         
        </td>
      </tr>
      <tr>
        <td colspan="2" class="kkb-pannel-permission-td">
          <div class="pm-tab-wrapper">
            <span class="pm-tab pm-tab-entry active"><?php _e('게시글 권한', 'kingkongboard');?></span>
            <span class="pm-tab pm-tab-comment"><?php _e('댓글 권한', 'kingkongboard');?></span>
          </div>
          <div class="tab-entry">
            <table>
              <tr>
                <th><?php echo __('User Role', 'kingkongboard');?></th>
                <th><?php echo __('읽기', 'kingkongboard');?></th>
                <th><?php echo __('쓰기', 'kingkongboard');?></th>
                <th><?php echo __('수정', 'kingkongboard');?></th>
                <th><?php echo __('삭제', 'kingkongboard');?></th>
              </tr>
<?php

  if(isset($_GET['id'])){
    $board_id = sanitize_text_field($_GET['id']);
  } else {
    $board_id = null;
  }

  foreach( $roles as $role_name => $role_info ){
    $permission_read          = $controller->checkPermissionByRole($board_id, 'entry', 'read', $role_name);
    $permission_write         = $controller->checkPermissionByRole($board_id, 'entry', 'write', $role_name);
    $permission_delete        = $controller->checkPermissionByRole($board_id, 'entry', 'delete', $role_name);
    $permission_modify        = $controller->checkPermissionByRole($board_id, 'entry', 'modify', $role_name);
    ($permission_read)         ? $read_checked         = 'checked' : $read_checked         = null;
    ($permission_write)        ? $write_checked        = 'checked' : $write_checked        = null;
    ($permission_delete)       ? $delete_checked       = 'checked' : $delete_checked       = null;
    ($permission_modify)       ? $modify_checked       = 'checked' : $modify_checked       = null;

?>
              <tr>
                <td><?php echo $role_name;?></td>
                <td><input type="checkbox" name="permission_read[]" value="<?php echo $role_name;?>" <?php echo $read_checked;?>></td>
                <td><input type="checkbox" name="permission_write[]" value="<?php echo $role_name;?>" <?php echo $write_checked;?>></td>
                <td><input type="checkbox" name="permission_modify[]" value="<?php echo $role_name;?>" <?php echo $modify_checked;?>></td>
                <td><input type="checkbox" name="permission_delete[]" value="<?php echo $role_name;?>" <?php echo $delete_checked;?>></td>
              </tr>            
<?php
  }
    $guest_permission_read    = $controller->checkPermissionByRole($board_id, 'entry', 'read', 'guest');
    $guest_permission_write   = $controller->checkPermissionByRole($board_id, 'entry', 'write', 'guest');
    $guest_permission_delete  = $controller->checkPermissionByRole($board_id, 'entry', 'delete', 'guest');
    $guest_permission_modify  = $controller->checkPermissionByRole($board_id, 'entry', 'modify', 'guest');
    ($guest_permission_read)   ? $guest_read_checked   = 'checked' : $guest_read_checked   = null;
    ($guest_permission_write)  ? $guest_write_checked  = 'checked' : $guest_write_checked  = null;
    ($guest_permission_delete) ? $guest_delete_checked = 'checked' : $guest_delete_checked = null;
    ($guest_permission_modify) ? $guest_modify_checked = 'checked' : $guest_modify_checked = null;
?>
              <tr>
                <td><?php echo __('비회원', 'kingkongboard');?></td>
                <td><input type="checkbox" name="permission_read[]" value="guest" <?php echo $guest_read_checked;?>></td>
                <td><input type="checkbox" name="permission_write[]" value="guest" <?php echo $guest_write_checked;?>></td>
                <td><input type="checkbox" name="permission_modify[]" value="guest" <?php echo $guest_modify_checked;?>></td>
                <td><input type="checkbox" name="permission_delete[]" value="guest" <?php echo $guest_delete_checked;?>></td>
              </tr>              
            </table>
          </div>
          <div class="tab-comment" style="display:none">
            <table>
              <tr>
                <th><?php echo __('User Role', 'kingkongboard');?></th>
                <th><?php echo __('읽기', 'kingkongboard');?></th>
                <th><?php echo __('쓰기', 'kingkongboard');?></th>
                <th><?php echo __('수정', 'kingkongboard');?></th>
                <th><?php echo __('삭제', 'kingkongboard');?></th>
              </tr>
<?php
  foreach( $roles as $role_name => $role_info ){

    $permission_read          = $controller->checkPermissionByRole($board_id, 'comment', 'read', $role_name);
    $permission_write         = $controller->checkPermissionByRole($board_id, 'comment', 'write', $role_name);
    $permission_delete        = $controller->checkPermissionByRole($board_id, 'comment', 'delete', $role_name);
    $permission_modify        = $controller->checkPermissionByRole($board_id, 'comment', 'modify', $role_name);
    ($permission_read)         ? $read_checked         = 'checked' : $read_checked         = null;
    ($permission_write)        ? $write_checked        = 'checked' : $write_checked        = null;
    ($permission_delete)       ? $delete_checked       = 'checked' : $delete_checked       = null;
    ($permission_modify)       ? $modify_checked       = 'checked' : $modify_checked       = null;

?>
              <tr>
                <td><?php echo $role_name;?></td>
                <td><input type="checkbox" name="permission_comment_read[]" value="<?php echo $role_name;?>" <?php echo $read_checked;?>></td>
                <td><input type="checkbox" name="permission_comment_write[]" value="<?php echo $role_name;?>" <?php echo $write_checked;?>></td>
                <td><input type="checkbox" name="permission_comment_modify[]" value="<?php echo $role_name;?>" <?php echo $modify_checked;?>></td>
                <td><input type="checkbox" name="permission_comment_delete[]" value="<?php echo $role_name;?>" <?php echo $delete_checked;?>></td>
              </tr>
<?php
  }
    $guest_permission_read    = $controller->checkPermissionByRole($board_id, 'comment', 'read', 'guest');
    $guest_permission_write   = $controller->checkPermissionByRole($board_id, 'comment', 'write', 'guest');
    $guest_permission_delete  = $controller->checkPermissionByRole($board_id, 'comment', 'delete', 'guest');
    $guest_permission_modify  = $controller->checkPermissionByRole($board_id, 'comment', 'modify', 'guest');
    ($guest_permission_read)   ? $guest_read_checked   = 'checked' : $guest_read_checked   = null;
    ($guest_permission_write)  ? $guest_write_checked  = 'checked' : $guest_write_checked  = null;
    ($guest_permission_delete) ? $guest_delete_checked = 'checked' : $guest_delete_checked = null;
    ($guest_permission_modify) ? $guest_modify_checked = 'checked' : $guest_modify_checked = null;
?>
              <tr>
                <td><?php echo __('비회원', 'kingkongboard');?></td>
                <td><input type="checkbox" name="permission_comment_read[]" value="guest" <?php echo $guest_read_checked;?>></td>
                <td><input type="checkbox" name="permission_comment_write[]" value="guest" <?php echo $guest_write_checked;?>></td>
                <td><input type="checkbox" name="permission_comment_modify[]" value="guest" <?php echo $guest_modify_checked;?>></td>
                <td><input type="checkbox" name="permission_comment_delete[]" value="guest" <?php echo $guest_delete_checked;?>></td>
              </tr>  
            </table>
          </div>
        </td>
      </tr>
    </table>

<?php
  }
?>