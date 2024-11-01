<?php
/**
* Template Name: WSKR Template
*
* @link       https://wskr.ie/
* @since      1.0.0
*
* @package    WSKR
* @subpackage WSKR/admin
*
* @author     WSKR Limited <support@wskr.ie>
*/
get_wskr_header('wskr'); 
?>
<div id="wskr-template-wrapper" class="wskr-template-wrapper">
    <main id="wskr-main" class="wskr-site-main" role="wskr-main">
        <?php
	        if ( have_posts() ) {
	        	while ( have_posts() ) {
	            		the_post();
	            		the_content();
	       		}
	       		wp_reset_postdata();
	       	}
        ?>
    </main>
</div>
<?php get_wskr_footer('wskr'); ?>