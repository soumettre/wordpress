<?php
error_reporting(0);
ini_set('error_reporting', 0);

header('Access-Control-Allow-Origin: https://soumettre.fr');
header('Access-Control-Allow-Origin: http://soumettre.app:8000');

require_once('../../../wp-load.php');
require_once('inc/SoumettreApiClient.php');

$url = $_GET['url'];

if (get_option('soum_sour_api_key', true) != $_GET['api_key']) {
    die('wrong_credentials');
}

// check if URL is valid
if (!$url_data = parse_url($url)) {
    die('invalid_url');
}

$args = array(
    'post__not_in' => get_option("sticky_posts"),
    'meta_query' => array(
        array(
            'key' => get_option('soum_sour_url_field', true),
            'value' => $url,
            'compare' => '=',
        )
    )
);
$meta_query = new WP_Query($args);
if ( $meta_query->have_posts() ) {
    while ( $meta_query->have_posts() ) {
        $meta_query->the_post();
        echo json_encode(array('url' => get_permalink()));
    }
} else {
    die('not_found');
}
