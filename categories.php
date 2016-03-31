<?php
error_reporting(0);
ini_set('error_reporting', 0);

require_once('../../../wp-load.php');
require_once('inc/SoumettreApiClient.php');

if (get_option('soum_sour_api_key', true) != $_GET['api_key']) {
    die('wrong_credentials');
}

$categories = get_categories();
echo json_encode($categories);