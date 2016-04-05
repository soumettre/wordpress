<?php
require_once('config.php');

require __DIR__ . '/vendor/autoload.php';

$mode = "test";
//$mode = "prod";

$api = new Soumettre\SoumettreApi($mode);

