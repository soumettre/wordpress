<?php
/**
 * @link              https://soumettre.fr/
 * @since             1.0.0
 * @package           SoumettreSource
 *
 * @wordpress-plugin
 * Plugin Name:       SoumettreSource
 * Plugin URI:        https://soumettre.fr/plugin/
 * Description:       Adds your site on Soumettre.fr
 * Version:           0.1
 * Author:            Didier Sampaolo
 * Author URI:        https://didcode.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       soumettre-source
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

define('SOUMETTRE_API_URL', get_home_url().'/wp-content/plugins/soumettre_source/api/');

if (is_admin()) {
    add_action('init', 'call_SoumettreSource_Admin');
}

function call_SoumettreSource_Admin()
{
    new SoumettreSource_Admin();
}

class SoumettreSource_Admin
{
    protected $prefix = 'soum_sour_';

    public function __construct()
    {
        // check updates
        require 'plugin-updates/plugin-update-checker.php';
        $MyUpdateChecker = PucFactory::buildUpdateChecker(
            'https://soumettre.fr/plugins/wordpress/metadata.json',
            __FILE__,
            'soumettre-source'
        );

        add_action('admin_menu', array($this, 'admin_menu'));

        // ajax
        add_action('admin_footer', array($this, 'ajax') );
        add_action( 'wp_ajax_test_api', array($this, 'ajax_test_api' ));
        add_action( 'wp_ajax_site_add', array($this, 'ajax_site_add' ));
    }

    public function admin_menu()
    {
        add_options_page(__('Soumettre Source', 'soumettre-source-menu'), __('Soumettre Source', 'menu-test'), 'manage_options', 'soumettre-source', array($this, 'admin_page'));
    }

    public function admin_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (isset($_POST['soumettre_source_submit'])) {
            $message = $this->save_options();

            if ($message) {
                echo '<div class="updated"><p><strong>Options enregistrées.</strong></p></div>';
            }
        }

        include('inc/admin/options.php');
    }

    protected function save_options()
    {
        $fields = array('api_key', 'api_secret', 'email', 'url_field');

        foreach ($fields as $field) {
            $opt_name = $this->prefix . $field;
            $opt_val = $_POST[$field];

            update_option($opt_name, $opt_val);
        }

        return true;
    }

    public function ajax() { ?>
        <script type="text/javascript" >
            jQuery(document).ready(function($) {

                jQuery('#soumettre_source_test_api').click(function(event) {
                    event.preventDefault();

                    var data = {
                        'action': 'test_api'
                    };
                    jQuery.post(ajaxurl, data, function(response) {
                        json = jQuery.parseJSON(response);
                        $('#test_api_res').html(json.message);
                    });
                });

                jQuery('#soumettre_source_site_add').click(function(event) {
                    event.preventDefault();
                    var data = {
                        'action': 'site_add'
                    };
                    jQuery.post(ajaxurl, data, function(response) {
                        json = jQuery.parseJSON(response);
                        $('#site_add_res').html(json.message);
                    });
                });
            });
        </script> <?php
    }

    public function ajax_test_api() {
        require_once('inc/SoumettreWP.php');

        $api = new SoumettreWP();
        $api->test();
    }

    public function ajax_site_add() {
        require_once('inc/SoumettreWP.php');

        $api = new SoumettreWP();
        $api->site_add();
    }

}
