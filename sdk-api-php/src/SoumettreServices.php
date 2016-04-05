<?php namespace Soumettre;

interface SoumettreServices {
    public function check_added($params);
    public function categories($params);
    public function post($params);
    public function delete($params);
}
