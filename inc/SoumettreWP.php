<?php
require_once(realpath(__DIR__ . '/../sdk-api-php/src/SoumettreServices.php'));
require_once(realpath(__DIR__ . '/../sdk-api-php/src/SoumettreApiClient.php'));
require_once(realpath(__DIR__ . '/../sdk-api-php/src/SoumettreApi.php'));

class SoumettreWP extends \Soumettre\SoumettreApiClient
{
    protected $mode = 'prod';
    protected $available_services = array(
        'check_api',
        'check_added',
        'categories',
        'post',
        'delete'
    );

    function __construct()
    {
        parent::__construct();

        $this->endpoint = 'https://soumettre.fr/api/';
        if ($_SERVER['SERVER_ADDR'] == '10.0.2.15') {
            $this->endpoint = 'http://soumettre.app/api/';
        }

        $this->wp_set_credentials();
        $this->check_request();
    }

    /**
     * Charge les credentials depuis la base de données (utilisé par Wordpress)
     *
     * @param string $prefix Préfixe SQL pour les options
     */
    public function wp_set_credentials($prefix = 'soum_sour_')
    {
        $this->email = get_option($prefix . 'email');
        $this->api_key = get_option($prefix . 'api_key');
        $this->api_secret = get_option($prefix . 'api_secret');
        $this->author = get_option($prefix . 'author');
    }

    protected function check_api()
    {
        $default_headers = array(
            'Name' => 'Plugin Name',
            'PluginURI' => 'Plugin URI',
            'Version' => 'Version',
            'Description' => 'Description',
            'Author' => 'Author',
            'AuthorURI' => 'Author URI',
            'TextDomain' => 'Text Domain',
            'DomainPath' => 'Domain Path',
            'Network' => 'Network',
            '_sitewide' => 'Site Wide Only',
        );

        $plugin_data = get_file_data('../soumettre_source.php', $default_headers, 'plugin');

        echo json_encode(array('status' => 'ok', 'version' => $plugin_data['Version']));
    }

    protected function check_request()
    {
        if (!isset($_GET['method'])) {
            return true;
        }

        $service = $_GET['method'];
        $params = ($this->mode == 'test') ? $_GET : $_POST;

        if (!in_array($service, $this->available_services)) {
            throw new \Exception("Service inconnu");
        }

        $this->service = $service;
        $this->params = $params;

        $this->check_signature($service, $params);

        $this->$service($params);
    }

    public function request($service, $post_params = array())
    {
        $endpoint = $this->endpoint . $service;
        $post_params = $this->sign($service, $post_params);

        $res = wp_remote_post($endpoint, array('body' => $post_params));
        if (is_object($res) && get_class($res) == 'WP_Error') {
            return array('status' => 'error', 'message' => $res->get_error_message());
        }

        return json_decode($res['body']);
    }

    public function site_add($url = null, $cms = null)
    {
        $params = array(
            'url' => get_home_url(),
            'cms' => 'WordPress',
            'is_partenaire' => get_option('soum_sour_is_partenaire'),
            'prefer_drafts' => get_option('soum_sour_prefer_drafts')
        );

        $res = $this->request('site_register', $params);

        echo json_encode($res);
        die();
    }

    public function check_added($params)
    {
        $args = array(
            'post__not_in' => get_option("sticky_posts"),
            'ignore_sticky_posts' => 1,
            'meta_query' => array(
                array(
                    'key' => get_option('soum_sour_url_field', true),
                    'value' => $params['url'],
                    'compare' => '=',
                )
            )
        );

        $meta_query = new WP_Query($args);
        if ($meta_query->have_posts()) {
            while ($meta_query->have_posts()) {
                $meta_query->the_post();
                echo json_encode(array('status' => 'found', 'url' => get_permalink()));
                die();
            }
        } else {
            echo json_encode(array('status' => 'not_found'));
            die();
        }
    }

    public function categories($params)
    {
        $ret = array();
        $categories = get_categories(array('taxonomy' => 'category'));
        foreach ($categories as $category) {
            $parent = $category->parent != 0 ? $category->parent : '#';
            $ret[] = array('id' => $category->term_id, 'text' => $category->name, 'parent' => $parent);
        }
        echo json_encode($ret);
    }

    public function test()
    {
        $res = $this->request('test');

        echo json_encode($res);
        die();
    }

    public function post($params)
    {

        $meta_input = array();

        $meta_input[get_option('soum_sour_url_field')] = $params['url'];
        foreach ($params as $p_key => $p_val) {
            if (strpos($p_key, 'custom_') === 0) {
                $key = str_replace('custom_', '', $p_key);
                $meta_input[$key] = strip_tags($p_val);
            }
        }

        if (isset($params['draft'])) {
            $post_status = 'draft';
        } else {
            $post_status = 'publish';
        }

        $post_arr = array(
            'post_status' => $post_status,
            'post_title' => $params['title'],
            'post_content' => $params['content'],
            'post_category' => array($params['category']),
            'post_author' => get_option('soum_sour_author'),
            'meta_input' => $meta_input
        );

        $post_ID = wp_insert_post($post_arr);

        if (is_numeric($post_ID) && $post_ID > 0) {
            if (isset($params['image'])) {
                $this->set_featured_image($params['image'], $post_ID);
                $this->set_directorypress_image_fields($params['image'], $post_ID);
            }

            echo json_encode(array('status' => 'ok', 'id' => $post_ID, 'url' => get_permalink($post_ID)));
        } else {

            echo json_encode(array('status' => 'error', 'post_arr' => $post_arr));
        }
    }

    protected function set_directorypress_image_fields($image_url, $post_id)
    {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        file_put_contents($file, $image_data);

        if (wp_mkdir_p($upload_dir['path'])) {
            $image_url = get_home_url() . $upload_dir['path'] . '/' . $filename;
        } else {
            $image_url = get_home_url() . $upload_dir['basedir'] . '/' . $filename;
        }

        add_post_meta($post_id, 'image', $image_url, true);
    }

    protected function set_featured_image($image_url, $post_id)
    {
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);
        $filename = basename($image_url);
        if (wp_mkdir_p($upload_dir['path'])) {
            $file = $upload_dir['path'] . '/' . $filename;
        } else {
            $file = $upload_dir['basedir'] . '/' . $filename;
        }
        file_put_contents($file, $image_data);

        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        $attach_id = wp_insert_attachment($attachment, $file, $post_id);
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attach_data = wp_generate_attachment_metadata($attach_id, $file);
        $res1 = wp_update_attachment_metadata($attach_id, $attach_data);
        $res2 = set_post_thumbnail($post_id, $attach_id);
    }


}
