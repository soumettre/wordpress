<?php
error_reporting(0);
ini_set('error_reporting', 0);

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
    'meta_query' => array(
        array(
            'key' => 'url',
            'value' => $url,
            'compare' => '=',
        )
    )
);
$query = new WP_Query($args);
if ( $query->have_posts() ) {
    while ( $query->have_posts() ) {
        $query->the_post();
        echo json_encode(array('url' => get_permalink()));
    }
} else {
    die('not_found');
}
