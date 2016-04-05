<?php

class SoumettreWP extends \Soumettre\SoumettreApiClient
{
    protected $mode = 'test';
    protected $available_services = array(
        'check_added',
        'categories',
        'post',
        'delete',
    );
    protected $endpoint = 'https://soumettre.fr/api/';

    function __construct()
    {
        parent::__construct();

        $this->wp_set_credentials();
        $this->check_request();
    }

    protected function check_request()
    {
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
                die('{status: "found", url: "' . get_permalink() . '"}');
            }
        } else {
            die('{status: "not_found"}');
        }

    }

    public function categories($params)
    {
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
            'post_status' => 'post',
            'post_title' => $params['title'],
            'post_content' => $params['content'],
            'post_category' => array($params['category']),
            'post_author' => 1,
            'meta_input' => array(
                'url' => $params['url']
            )
        );

        $res = wp_insert_post($post_arr);
        if (is_numeric($res)) {
            echo json_encode(array('status' => 'ok', 'url' => get_permalink($res)));
        } else {
            echo json_encode(array('status' => 'error'));
        }
    }
}
