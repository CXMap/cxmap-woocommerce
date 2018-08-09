<?php
/**
 * User: CXMap
 * Date: 09.08.2018
 * Time: 16:00
 */
class CXMap
{
  private static
    $initiated = false,
    $user_id,
    $userFirstName,
    $userLastName,
    $userEmail,
    $userPhone,
    $userDateOfBirth,
    $userGender
  ;

  public static function init()
  {
    if(!self::$initiated )
    {
      self::init_hooks();
      self::$initiated = true;
      load_plugin_textdomain('cxmap', false, 'cxmap/languages');
    }
  }

  /**
   * Initializes WordPress hooks
   */
  private static function init_hooks()
  {
    // Calling a function add administrative menu.
    add_action( 'admin_menu', array('CXMap', 'plgn_add_pages') );

    if(!is_admin())
    {
      add_action('wp_head', array('CXMap', 'cxmap_main') );
    }

    add_action( 'woocommerce_checkout_order_processed', array('CXMap', 'submitOrder'));

    register_uninstall_hook( __FILE__, array('CXMap', 'delete_options') );
  }

  // Function for delete options
  public static function delete_options()
  {
    delete_option('cxmap_plgn_options');
  }

  public static function plgn_add_pages()
  {
    add_submenu_page(
      'plugins.php',
      __( 'CXMap', 'cxmap' ),
      __( 'CXMap', 'cxmap' ),
      'manage_options',
      "cxmap",
      array('CXMap', 'plgn_settings_page')
    );
    // Call register settings function
    add_action( 'admin_init', array('CXMap', 'plgn_settings') );
  }

  public static function plgn_options_default()
  {
    return array(
      'cxmap_key' => '',
      'cxmap_cluster' => 'us-1',
      'only_product_id' => '1',
    );
  }

  public static function plgn_settings()
  {
    $plgn_options_default = self::plgn_options_default();

    if(!get_option('cxmap_plgn_options'))
    {
      add_option('cxmap_plgn_options', $plgn_options_default, '', 'yes');
    }

    $plgn_options = get_option('cxmap_plgn_options');
    $plgn_options = array_merge($plgn_options_default, $plgn_options);

    update_option('cxmap_plgn_options', $plgn_options);
  }

  // Function formed content of the plugin's admin page.
  public static function plgn_settings_page()
  {
    $cxmap_plgn_options = self::get_params();
    $cxmap_plgn_options_default = self::plgn_options_default();
    $message = "";
    $error = "";

    if(isset($_REQUEST['cxmap_plgn_form_submit'])
      && check_admin_referer(plugin_basename(dirname(__DIR__)), 'cxmap_plgn_nonce_name'))
    {
      foreach($cxmap_plgn_options_default as $k => $v)
      {
        $cxmap_plgn_options[$k] = trim(self::request($k, $v));
      }

      update_option('cxmap_plgn_options', $cxmap_plgn_options);

      $message = __("Settings saved", 'cxmap');
    }

    $options = array(
      'cxmap_plgn_options' => $cxmap_plgn_options,
      'message' => $message,
      'error' => $error,
    );

    echo self::loadTPL('adminform', $options);
  }

  private static function loadTPL($name, $options)
  {
    $tmpl = ( CXMAP_PLUGIN_DIR .'tmpl/' . $name . '.php');

    if(!is_file($tmpl))
      return __('Error Load Template', 'cxmap');

    extract($options, EXTR_PREFIX_SAME, "cxmap");

    ob_start();

    include $tmpl;

    return ob_get_clean();
  }

  private static function request($name, $default=null)
  {
    return (isset($_REQUEST[$name])) ? $_REQUEST[$name] : $default;
  }

  public static function cxmap_main()
  {
    $cxmap_plgn_options = self::get_params();
    if(!empty($cxmap_plgn_options['cxmap_key']))
    {
      $current_user = wp_get_current_user();
      $user_id = $current_user->ID;

      if($user_id > 0)
      {
        self::updateUserInfo();

        $person_info = array();
        if(!empty(self::$userFirstName)) $person_info['first_name'] = self::$userFirstName;
        if(!empty(self::$userLastName)) $person_info['last_name'] = self::$userLastName;
        if(!empty(self::$userEmail)) $person_info['email'] = self::$userEmail;
        if(!empty(self::$userPhone)) $person_info['phone'] = self::$userPhone;
        if(!empty(self::$userDateOfBirth)) $person_info['date_of_birth'] = self::$userDateOfBirth;
        if(!empty(self::$userGender)) $person_info['gender'] = self::$userGender;
        $person_info['client_id'] = $user_id;
        do_action( 'cxmap_person_info', $person_info );
      }
?>

<script type='text/javascript'>
(function(w,d,c,h){w[c]=w[c]||function(){(w[c].q=w[c].q||[]).push(arguments)};var s = d.createElement('script');s.type = 'text/javascript';s.async = true;s.charset = 'utf-8';s.src = h;var x = d.getElementsByTagName('script')[0];x.parentNode.insertBefore(s, x);})(window,document,'cxm','https://js.cxmap.io/<?php echo $cxmap_plgn_options['cxmap_cluster']; ?>/cxm.js');

cxm('endpoint', 'tracker-<?php echo $cxmap_plgn_options['cxmap_cluster']; ?>.cxmap.io');
cxm('appKey', '<?php echo $cxmap_plgn_options['cxmap_key']; ?>');

<?php if ($person_info): ?>
cxm('setPersonInfo', <?php echo json_encode($person_info); ?>);
<?php endif; ?>

cxm('trackPageView');
</script>

<?php
    }
  }

  /** Submit order to cxmap
   * @param $order_id
   */
  public static function submitOrder($order_id)
  {
    $cxmap_plgn_options = self::get_params();
    if(!empty($cxmap_plgn_options['cxmap_key']))
    { 
      require_once ( CXMAP_PLUGIN_DIR . 'lib/cxm.php');

      self::updateUserInfo();

      $url = $_SERVER["HTTP_HOST"];

      if (function_exists('wc_get_order')) $order = wc_get_order( $order_id );
      else $order = new WC_Order( $order_id );

      $person_info = array();
      $first_name = self::getValue($order->get_billing_first_name(), self::$userFirstName);
      if($first_name !== false){
        $person_info['first_name'] = $first_name;
      }

      $last_name = self::getValue($order->get_billing_last_name(), self::$userLastName);
      if($last_name !== false){
        $person_info['last_name'] = $last_name;
      }

      $email = self::getValue($order->get_billing_email(), self::$userEmail);
      if($email !== false){
        $person_info['email'] = $email;
      }

      $phone = self::getValue($order->get_billing_phone(), self::$userPhone);
      if($phone !== false){
        $person_info['phone'] = $phone;
      }

      if(!empty(self::$userDateOfBirth)){
        $person_info['date_of_birth'] = self::$userDateOfBirth;
      }
      if(!empty(self::$userGender)){
        $person_info['gender'] = self::$userGender;
      }
      if(self::$user_id){
        $person_info['client_id'] = self::$user_id;
      }

      do_action( 'cxmap_person_info', $person_info );
      
      if(is_admin_bar_showing()){
        $guestUID = false;
        self::$user_id = $order->customer_user ? $order->customer_user : false;
      }
      
      $cxm = new Cxm(
        $cxmap_plgn_options['cxmap_key'],
        isset($_COOKIE['_cxm']) ? $_COOKIE['_cxm'] : null
      );
      $cxm->endpoint('tracker-'.$cxmap_plgn_options['cxmap_cluster'].'.cxmap.io');
      $cxm->setPersonInfo($person_info);

      $items = array();

      $line_items = $order->get_items( apply_filters( 'woocommerce_admin_order_item_types', 'line_item' ) );

      $total = $order->get_total();
      $shipping = (method_exists($order, 'get_total_shipping')) ? $order->get_total_shipping() : 0;
      $order_total = $total - $shipping;

      if(is_array($line_items) && count($line_items))
      {
        foreach ($line_items as $item_id => $item)
        {
          $pid = (!empty($item['variation_id']) && !$cxmap_plgn_options['only_product_id'])
            ? $item['variation_id'] : $item['product_id'];

          $price = $item['line_subtotal'] / (float)$item['qty'];

          $items[] = array(
            'sku' => (int)$pid,
            'name' => $item['name'],
            'price' => $price,
            'qnt' => (float)$item['qty']
          );
        }
      }

      $cxm->track('transaction', array(
        'order_id' => $order_id,
        'total' => $order_total,
        'currency_iso' => get_option('woocommerce_currency'),
        'items' => $items
      ));
    }
  }

  private static function getValue($value, $default)
  {
    if(empty($value) && empty($default)){
      return false;
    }
    return (!empty($value)) ? $value : $default;
  }

  private static function updateUserInfo()
  {
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;
    $user_data = get_user_meta( $user_id );

    self::$user_id = $user_id;

    if(!empty($user_data['first_name'][0]))
      self::$userFirstName = $user_data['first_name'][0];
    if(!empty($user_data['last_name'][0]))
      self::$userLastName = $user_data['last_name'][0];
    if(!empty($current_user->data->user_email))
      self::$userEmail = $current_user->data->user_email;
    if(!empty($user_data['billing_phone'][0]))
      self::$userPhone = $user_data['billing_phone'][0];
  }

  private static function get_params()
  {
    static $params;
    if(empty($params))
    {
      $params = get_option('cxmap_plgn_options');
    }
    return $params;
  }
}