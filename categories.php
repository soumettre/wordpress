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

$categories = get_categories(array('taxonomy' => 'category'));
foreach($categories as $category) {
    $parent = $category->parent != 0 ? $category->parent : '#';
    $ret[] = array('id' => $category->term_id, 'text' => $category->name, 'parent' => $parent);
}
echo json_encode($ret);