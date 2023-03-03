<?php
/**
 * Plugin Name: eBay API Plugin
 * Plugin URI: https://www.example.com/ebay-api-plugin/
 * Description: A plugin that retrieves data from eBay using the Trading API.
 * Version: 1.0.0
 * Author: Your Name
 * Author URI: https://www.example.com/
 * License: GPL2+
 * License URI: https://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ebay-api-plugin
 */



function ebay_api_plugin_settings_page() {
  if (!current_user_can('manage_options')) {
    wp_die('You do not have sufficient permissions to access this page.');
  }

  if (isset($_POST['submit'])) {
    update_option('ebay_api_dev_id', sanitize_text_field($_POST['dev_id']));
    update_option('ebay_api_app_id', sanitize_text_field($_POST['app_id']));
    update_option('ebay_api_cert_id', sanitize_text_field($_POST['cert_id']));
    update_option('ebay_api_client_id', sanitize_text_field($_POST['client_id']));
    update_option('ebay_api_auth_token', sanitize_text_field($_POST['auth_token']));
  }

  $dev_id = get_option('ebay_api_dev_id');
  $app_id = get_option('ebay_api_app_id');
  $cert_id = get_option('ebay_api_cert_id');
  $client_id = get_option('ebay_api_client_id');
  $auth_token = get_option('ebay_api_auth_token');

  ?>

  <div class="wrap">
    <h1>eBay API Plugin Settings</h1>

    <form method="post">
      <table class="form-table">
        <tr>
          <th><label for="dev_id">Dev ID</label></th>
          <td><input type="text" name="dev_id" id="dev_id" value="<?php echo esc_attr($dev_id); ?>" class="regular-text" /></td>
        </tr>
        <tr>
          <th><label for="app_id">App ID</label></th>
          <td><input type="text" name="app_id" id="app_id" value="<?php echo esc_attr($app_id); ?>" class="regular-text" /></td>
        </tr>
        <tr>
          <th><label for="cert_id">Cert ID</label></th>
          <td><input type="text" name="cert_id" id="cert_id" value="<?php echo esc_attr($cert_id); ?>" class="regular-text" /></td>
        </tr>
        <tr>
          <th><label for="client_id">Client ID</label></th>
          <td><input type="text" name="client_id" id="client_id" value="<?php echo esc_attr($client_id); ?>" class="regular-text" /></td>
        </tr>
        <tr>
          <th><label for="auth_token">Auth Token</label></th>
          <td><input type="text" name="auth_token" id="auth_token" value="<?php echo esc_attr($auth_token); ?>" class="regular-text" /></td>
        </tr>
      </table>

      <p class="submit"><input type="submit" name="submit" value="Save Changes" class="button-primary" /></p>
    </form>
  </div>

  <?php
}

function ebay_api_plugin_get_item($item_id) {
  $dev_id = get_option('ebay_api_plugin_dev_id');
  $app_id = get_option('ebay_api_plugin_app_id');
$cert_id = get_option('ebay_api_plugin_cert_id');
$auth_token = get_option('ebay_api_plugin_auth_token');
$site_id = get_option('ebay_api_plugin_site_id');

$call_name = 'GetItem';
$compatibility_level = 967;
// Create request XML
$request_xml = '<?xml version="1.0" encoding="utf-8"?>
<GetItemRequest xmlns="urn:ebay:apis:eBLBaseComponents">
<RequesterCredentials>
<eBayAuthToken>'.$auth_token.'</eBayAuthToken>
</RequesterCredentials>
<ItemID>'.$item_id.'</ItemID>
<IncludeItemSpecifics>true</IncludeItemSpecifics>
</GetItemRequest>';
// Send request to eBay API using cURL
$curl = curl_init();
curl_setopt_array($curl, array(
CURLOPT_URL => "https://api.ebay.com/ws/api.dll",
CURLOPT_RETURNTRANSFER => true,
CURLOPT_ENCODING => "",
CURLOPT_MAXREDIRS => 10,
CURLOPT_TIMEOUT => 30,
CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
CURLOPT_CUSTOMREQUEST => "POST",
CURLOPT_POSTFIELDS => $request_xml,
CURLOPT_HTTPHEADER => array(
"Content-Type: text/xml",
"X-EBAY-API-COMPATIBILITY-LEVEL: ".$compatibility_level,
"X-EBAY-API-DEV-NAME: ".$dev_id,
"X-EBAY-API-APP-NAME: ".$app_id,
"X-EBAY-API-CERT-NAME: ".$cert_id,
"X-EBAY-API-SITEID: ".$site_id
),
));

// Execute cURL request and retrieve response
$response = curl_exec($curl);

// Check for errors
if (curl_errno($curl)) {
echo 'Error: '.curl_error($curl);
curl_close($curl);
return;
}

// Close cURL
curl_close($curl);

// Parse response XML and retrieve relevant data
$response_xml = simplexml_load_string($response);

$ns = $response_xml->getDocNamespaces();
$response_xml->registerXPathNamespace('e', $ns['eBay']);

$item = $response_xml->xpath('//e:GetItemResponse/e:Item')[0];

$drive_side = $item->ItemSpecifics->NameValueList[0]->Value;
$seats = $item->ItemSpecifics->NameValueList[1]->Value;
$power = $item->ItemSpecifics->NameValueList[2]->Value;
$engine_size = $item->ItemSpecifics->NameValueList[3]->Value;
$fuel = $item->ItemSpecifics->NameValueList[4]->Value;
$transmission = $item->ItemSpecifics->NameValueList[5]->Value;
$mot_expiry = $item->ItemSpecifics->NameValueList[6]->Value;

// Display data to user
echo '<h2>Vehicle Information</h2>';
echo '<ul>';
echo '<li>Drive Side: '.$drive_side.'</li>';
echo '<li>Seats: '.$seats.'</li>';
echo '<li>Power: '.$power.'</li>';
echo '<li>Engine size: '.$engine_size.'</li>';
echo '<li>Fuel: '.$fuel.'</li>';
echo '<li>Transmission: '.$transmission.'</li>';
echo '<li>MOT Expiry: '.$mot_expiry.'</li>';
echo '</ul>';
// Close the cURL session
curl_close($curl);
}
// Add a menu item for the plugin settings page
function ebay_api_plugin_menu() {
    add_options_page('eBay API Plugin Settings', 'eBay API', 'manage_options', 'ebay_api_settings', 'ebay_api_settings_page');
    }
    
    // Display the plugin settings page
    function ebay_api_settings_page() {
    ?>
    <div class="wrap">
    <h1>eBay API Plugin Settings</h1>
    <form method="post" action="options.php">
    <?php settings_fields('ebay_api_options'); ?>
    <?php do_settings_sections('ebay_api_settings'); ?>
    <?php submit_button(); ?>
    </form>
    </div>
    <?php
    }

    // Register the plugin settings
function ebay_api_register_settings() {
    register_setting('ebay_api_options', 'ebay_api_options', 'ebay_api_sanitize_options');
    add_settings_section('ebay_api_credentials_section', 'API Credentials', 'ebay_api_credentials_section_callback', 'ebay_api_settings');
    add_settings_field('ebay_api_dev_id', 'Developer ID', 'ebay_api_dev_id_callback', 'ebay_api_settings', 'ebay_api_credentials_section');
    add_settings_field('ebay_api_app_id', 'Application ID', 'ebay_api_app_id_callback', 'ebay_api_settings', 'ebay_api_credentials_section');
    add_settings_field('ebay_api_cert_id', 'Certificate ID', 'ebay_api_cert_id_callback', 'ebay_api_settings', 'ebay_api_credentials_section');
    add_settings_field('ebay_api_user_token', 'User Token', 'ebay_api_user_token_callback', 'ebay_api_settings', 'ebay_api_credentials_section');
    }
    // Sanitize the plugin settings
function ebay_api_sanitize_options($input) {
    $output = array();
    if (isset($input['dev_id'])) {
    $output['dev_id'] = sanitize_text_field($input['dev_id']);
    }
    if (isset($input['app_id'])) {
    $output['app_id'] = sanitize_text_field($input['app_id']);
    }
    if (isset($input['cert_id'])) {
    $output['cert_id'] = sanitize_text_field($input['cert_id']);
    }
    if (isset($input['user_token'])) {
    $output['user_token'] = sanitize_text_field($input['user_token']);
    }
    return $output;
    }
    // Display the credentials section
function ebay_api_credentials_section_callback() {
    echo '<p>Please enter your eBay API credentials below.</p>';
    }
    
    // Display the Developer ID field
    function ebay_api_dev_id_callback() {
    $options = get_option('ebay_api_options');
    echo '<input type="text" name="ebay_api_options[dev_id]" value="'.esc_attr($options['dev_id']).'" />';
    }
    // Display the Application ID field
function ebay_api_app_id_callback() {
    $options = get_option('ebay_api_options');
    echo '<input type="text" name="ebay_api_options[app_id]" value="'.esc_attr($options['app_id']).'" />';
    }
    
    // Display the Certificate ID field
    function ebay_api_cert_id_callback() {
    $options = get_option('ebay_api_options');
    echo '<input type="text" name="ebay_api_options[cert_id]" value="'.esc_attr($options['cert_id']).'" />';
    }
    // Display the User Token field
function ebay_api_user_token_callback() {
    $options = get_option('ebay_api_plugin_settings');
    ?>
    <div class="wrap">
<h2> eBay API Plugin Settings </h2>
<form method="post" action="options.php">
<?php settings_fields('ebay_api_plugin_settings_group'); ?>
<table class="form-table">
<tbody>
<tr>
<th scope="row">
<label for="dev_id">Dev ID</label>
</th>
<td>
<input type="text" name="ebay_api_plugin_settings[dev_id]" id="dev_id" value="<?php echo $options['dev_id']; ?>" />
</td>
</tr>
<tr>
<th scope="row">
<label for="app_id">App ID</label>
</th>
<td>
<input type="text" name="ebay_api_plugin_settings[app_id]" id="app_id" value="<?php echo $options['app_id']; ?>" />
</td>
</tr>
<tr>
<th scope="row">
<label for="cert_id">Cert ID</label>
</th>
<td>
<input type="text" name="ebay_api_plugin_settings[cert_id]" id="cert_id" value="<?php echo $options['cert_id']; ?>" />
</td>
</tr>
<tr>
<th scope="row">
<label for="client_id">Client ID</label>
</th>
<td>
<input type="text" name="ebay_api_plugin_settings[client_id]" id="client_id" value="<?php echo $options['client_id']; ?>" />
</td>
</tr>
</tbody>
</table>
<?php submit_button(); ?>
</form>
</div>

  <?php
}
function ebay_api_plugin_add_menu() {
    add_options_page('eBay API Plugin Settings', 'eBay API Plugin', 'manage_options', 'ebay_api_plugin', 'ebay_api_plugin_settings_page');
  }
  
  add_action('admin_menu', 'ebay_api_plugin_add_menu');





