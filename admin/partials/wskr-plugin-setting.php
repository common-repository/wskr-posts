<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://wskr.ie/
 * @since      1.0.0
 *
 * @package    WSKR
 * @subpackage WSKR/admin/partials
 */
/**
 * Get wskr option fields value
 */
  if(function_exists('wskr_option_fields')){
    $wskr_business_email = $wskr_business_password = $wskr_business_id = $wskr_token_price = $wskr_tag = $wskr_category = '';
    $args = wskr_option_fields();    
    if($args){
      foreach ($args as $args_val) {
        switch($args_val){
            case 'wskr_business_email':
                $wskr_business_email = !empty( get_option($args_val) ) ? get_option($args_val) : '';                
                break;
            case 'wskr_business_password':
               $wskr_business_password = !empty( get_option($args_val) ) ? get_option($args_val) : '';       
                break;
            case 'wskr_business_id':
                $wskr_business_id = !empty( get_option($args_val) ) ? get_option($args_val) : '';
                break;
            case 'wskr_token_price':
                $wskr_token_price = !empty(get_option($args_val)) ? get_option($args_val) : 100;
                break;
            case 'wskr_tag':
                $wskr_tag = !empty(get_option($args_val)) ? get_option($args_val) : 'WSKR Protected';
                break;
            case 'wskr_category':
                $wskr_category = !empty(get_option($args_val)) ? get_option($args_val) : 'WSKR Protected';
                break;
              default:
                break;
        }
      }
    }
  }
  /**
  * WSKR get all the pages
  */
  $pages = get_pages(); 
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<section class="clear WSKRForm" id="WSKRFieldForm">
	<div class="WSKRFormInside  clear p40">
  		<div class="HeadingDiv  text-left">
    		<h3><?php echo esc_html__('WSKR Posts', 'wskr'); ?></h3>
  		</div>
  		<div class="bg-white">
        <ul class="tabs">
          <li class="tab-link current" data-tab="tab-1"><strong><?php echo esc_html__('WSKR Account', 'wskr'); ?></strong></li>
          <li class="tab-link" data-tab="tab-2"><strong><?php echo esc_html__('Settings', 'wskr'); ?></strong></li>
        </ul>
        <!--Basic Setting Tab 1 Content -->
        <form id="wskr_setting_from" method="post" action="options.php" class="form-container">
          <?php
            settings_fields('wskr-settings');
            do_settings_sections('wskr-settings');
          ?>
          <div id="tab-1" class="tab-content current"> 
  	    		<div class="form-group w-50 float-left">
  	      			<label for="wskr_business_email"><?php echo esc_html__('WSKR Business account email address *', 'wskr'); ?></label>
  	      			<input type="email" name="wskr_business_email" value="<?php echo esc_attr($wskr_business_email); ?>" id="" class="form-control " placeholder="<?php echo esc_attr__('Email Address', 'wskr'); ?>">
  	    		</div>
  	    		<div class="form-group w-50 float-left">
  	      			<label for="wskr_business_password"><?php echo esc_html__('WSKR Business account password *', 'wskr') ?> </label>
  	      			<input type="password" name="wskr_business_password" value="<?php echo esc_attr($wskr_business_password); ?>" id="" class="form-control " placeholder="<?php echo esc_attr__('*********', 'wskr'); ?>">
  	    		</div>
      			<div class="form-group w-50 float-left">
        				<label for="wskr_business_id"><?php echo esc_html__('WSKR Business ID', 'wskr') ?></label>
        				<input type="text" name="wskr_business_id" value="<?php echo esc_attr($wskr_business_id); ?>" id="wskr_business_id" class="form-control" placeholder="<?php echo esc_attr__('Business ID', 'wskr'); ?>" readonly>
      			</div>
            <div class="error_note w-50 float-left">
              <p class="error_msg"><?php echo esc_html__('* Required fields.', 'wskr'); ?></p>
            </div>
      			<div class="SubmitBtn w-50 clear float-left text-center">
        				<button type="button" class="btn" style="position: relative;" onclick="authorise_business_account(this);"><?php echo esc_html__('Authorise WSKR Account', 'wskr'); ?><img src="<?php echo plugin_dir_url( __DIR__ ).'assets/icon/loder.gif'; ?>" class="Loderimg" style="display: none;"></button>
      			</div>
            <div class="response_msg2 w-50" style="visibility: hidden;"><?php echo esc_html__('Response Message', 'wskr'); ?></div>
          </div>
          <!--Other Setting Tab 2 Content -->
          <div id="tab-2" class="tab-content">
      			<div class="form-group w-50 float-left">
        				<label for="wskr_token_price"><?php echo esc_html__('Default WSKR Token price *', 'wskr') ?></label>
        				<input type="number" min="1" name="wskr_token_price" value="<?php echo esc_attr($wskr_token_price); ?>" id="" placeholder="<?php echo esc_attr__('Enter Your Token Value', 'wskr'); ?>" class="form-control input_fields" required="">
      			</div>
      			<div class="form-group w-50 float-left">
        				<label for="wskr_tag"><?php echo esc_html__('WSKR Tag (separate tags by a comma: ",") *', 'wskr'); ?></label>
        				<input type="text" name="wskr_tag" value="<?php echo esc_attr($wskr_tag); ?>" id="" placeholder="<?php echo esc_attr__('Tag List', 'wskr'); ?>" class="form-control input_fields" required="">
      			</div>
      			<div class="form-group w-50 float-left">
        				<label for="wskr_category"><?php echo esc_html__('WSKR Category (separate categories by a comma: ",") *', 'wskr'); ?></label>
        				<input type="text" name="wskr_category" value="<?php echo esc_attr($wskr_category); ?>" id="" placeholder="<?php echo esc_attr__('Category List', 'wskr'); ?>" class="form-control input_fields" required="">
      			</div>   
            <div class="error_note w-50 float-left">
              <p class="error_msg"><?php echo esc_html__('* Required fields.', 'wskr'); ?></p>
            </div>
            <div class="SubmitBtn w-50 clear text-center">
              <button type="button" id="wskr_from_submit_btn" class="btn" style="position: relative;" onclick="wskr_save_settings_ajax_script(this);"><?php echo esc_html__('Save Settings', 'wskr'); ?><img src="<?php echo plugin_dir_url( __DIR__ ).'assets/icon/loder.gif'; ?>" class="LoderSaveimg" style="display: none;"></button>
            </div>
          </div>
          <div class="response_msg w-50" style="visibility: hidden;"><?php echo esc_html__('Response Message', 'wskr'); ?></div>
        </form>
		</div>
	</div>
</section>