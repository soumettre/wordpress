<?php

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
    protected $endpoint = 'https://soumettre.fr/api/';

    function __construct()
    {
        parent::__construct();

        $this->wp_set_credentials();
        $this->check_request();
    }

    protected function check_api() {
//        $data = get_plugin_data();

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
            // Site Wide Only is deprecated in favor of Network.
            '_sitewide' => 'Site Wide Only',
        );

        $plugin_data = get_file_data( '../soumettre_source.php', $default_headers, 'plugin' );

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
        return json_decode($res['body']);
    }

    public function site_add($url = null)
    {
        return $this->request('site_register', array('url' => get_home_url()));
    }

    public function check_added($params)
    {
        $args = array(
            'post__not_in' => get_option("sticky_posts"),
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

    public function post($params)
    {
        $post_arr = array(
            'post_title' => $params['title'],
            'post_content' => $params['content'],
            'post_category' => array($params['category']),
            'post_author' => 1,
            'meta_input' => array(
                get_option('soum_sour_url_field') => $params['url']
            )
        );

        $post_ID = wp_insert_post($post_arr);

        if (is_numeric($post_ID)) {
            if (isset($params['image'])) {
                $this->set_featured_image($params['image'], $post_ID);
                $this->set_directorypress_image_fields($params['image'], $post_ID);
            }

            echo json_encode(array('status' => 'ok', 'id' => $post_ID, 'url' => get_permalink($post_ID)));
        } else {
            echo json_encode(array('status' => 'error'));
        }
    }

    protected function set_directorypress_image_fields($image_url, $post_id) {
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
            $image_url = get_home_url(). $upload_dir['path'] . '/' . $filename;
        } else {
            $image_url = get_home_url(). $upload_dir['basedir'] . '/' . $filename;
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
