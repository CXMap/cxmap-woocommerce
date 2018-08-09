<?php
/*
Plugin Name: CXMap
Description: Social share buttons with counters likes.
Version: 1.0
Author: CXMap
Author URI: http://joomline.ru
*/
$enable  =  $cxmap_plgn_options['only_product_id']? ' checked="checked"' : '';
$disable = !$cxmap_plgn_options['only_product_id']? ' checked="checked"' : '';
?>
<div class="wrap">
  <div class="icon32" id="icon-options-general"></div>
  <h2><?php echo __("CXMap Settings", 'cxmap'); ?></h2>

  <div id="message"
   class="updated fade" <?php if (!isset($_REQUEST['cxmap_plgn_form_submit']) || $message == "") echo "style=\"display:none\""; ?>>
  <p><?php echo $message; ?></p>
  </div>

  <div class="error" <?php if ("" == $error) echo "style=\"display:none\""; ?>>
  <p>
    <strong><?php echo $error; ?></strong>
  </p>
  </div>

  <div>
  <form name="form1" method="post" action="admin.php?page=cxmap" enctype="multipart/form-data">

    <table class="form-table">
    <tr valign="top">
      <th scope="row"><?php echo __("APP Key", 'cxmap'); ?></th>
      <td>
      <input
        class="regular-text code"
        name='cxmap_key'
        type='text'
        value='<?php echo $cxmap_plgn_options['cxmap_key']; ?>'
        />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php echo __("Cluster", 'cxmap'); ?></th>
      <td>
      <input
        class="regular-text code"
        name='cxmap_cluster'
        type='text'
        value='<?php echo $cxmap_plgn_options['cxmap_cluster']; ?>'
        />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><?php echo __("Submit only product_id (not variation_id)", 'cxmap'); ?></th>
      <td>
      <label for="only_product_id_1"><?php echo __("Yes", 'cxmap'); ?></label>
      <input
        id="only_product_id_1"
        name='only_product_id'
        type='radio'
        value='1'<?php echo $enable; ?>
        />
      <label for="only_product_id_1"><?php echo __("No", 'cxmap'); ?></label>
      <input
        id="only_product_id_0"
        name='only_product_id'
        type='radio'
        value='0'<?php echo $disable; ?>
        />
      </td>
    </tr>
    </table>

    <input type="hidden" name="cxmap_plgn_form_submit" value="submit"/>
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>"/>

    <?php wp_nonce_field(plugin_basename(dirname(__DIR__)), 'cxmap_plgn_nonce_name'); ?>
  </form>
  </div>
  <br/>
  <div class="link">
  <a class="button-secondary" href="https://app.cxmap.io" target="_blank"><?php echo __("Go to CXMap account", 'cxmap'); ?></a>
  </div>
</div>
