<?php
error_reporting(0);
ini_set('error_reporting', 0);

header('Access-Control-Allow-Origin: https://soumettre.fr');
header('Access-Control-Allow-Origin: http://soumettre.app:8000');

require_once('../../../wp-load.php');
require_once('inc/SoumettreApiClient.php');

if (get_option('soum_sour_api_key', true) != $_GET['api_key']) {
    die('wrong_credentials');
}

$post_arr = array(
    'post_status' => 'post',
    'post_title' => $_POST['title'],
    'post_content' => $_POST['content'],
    'post_category' => array($_POST['category']),
    'post_author' => 1,
    'meta_input' => array(
        'url' => $_POST['url']
    )
);

$res = wp_insert_post($post_arr);
if (is_numeric($res)) {
    echo json_encode(array('status' => 'ok', 'url' => get_permalink($res)));
} else {
    echo json_encode(array('status' => 'error'));
}
