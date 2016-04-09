<?php
require_once('../sdk-api-php/src/SoumettreServices.php');
require_once('../sdk-api-php/src/SoumettreApiClient.php');
require_once('../sdk-api-php/src/SoumettreApi.php');
require_once('../inc/SoumettreWP.php');

error_reporting(0);
ini_set('error_reporting', 0);
require_once('../../../../wp-load.php');

$api = new SoumettreWP();

