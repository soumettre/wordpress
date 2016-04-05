<?php namespace Soumettre;


/**
 * Class SoumettreApi Vous devez étendre cette classe et implémenter les 4 dernières méthodes à votre façon
 */
class SoumettreApi extends SoumettreApiClient implements SoumettreServices
{
    protected $mode;
    protected $available_services = array(
        'check_added',
        'categories',
        'post',
        'delete',
    );

    public function __construct($mode = 'prod', $api_email = SOUMETTRE_API_EMAIL, $api_key = SOUMETTRE_API_KEY, $api_secret = SOUMETTRE_API_SECRET)
    {
        parent::__construct($api_email, $api_key, $api_secret);

        $this->mode = $mode;
        $this->check_request();
    }

    protected function check_request()
    {
        $service = str_replace(SOUMETTRE_API_URL, '', strtok($_SERVER["REQUEST_URI"], '?'));
        $params = ($this->mode == 'test') ? $_GET : $_POST;

        if (!in_array($service, $this->available_services)) {
            throw new \Exception("Service inconnu");
        }

        $this->service = $service;
        $this->params = $params;

        $this->check_signature($service, $params);

        $this->$service($params);
    }


    public function check_added($params)
    {
        $url = $params['url'];

        // ici, vérifier si le site est dans votre base ou pas
        /*
         * 0 = not found
         * 1 = en attente
         * 2 = déjà validé
         */
        $is_found = rand(0, 2);

        if ($is_found == 0) {
            $this->response(array('status' => 'not_found'));
        } elseif ($is_found == 1) {
            $this->response(array('status' => 'waiting'));
        } elseif ($is_found == 2) {
            $this->response(array('status' => 'found', 'url' => 'http://www.monsite.com/url-de-la-fiche.html'));
        }
    }

    public function categories($params)
    {
        $categories = array(
            array('id' => 1, 'text' => 'Véhicules', 'parent' => 0),
            array('id' => 2, 'text' => 'Automobile', 'parent' => 1),
            array('id' => 3, 'text' => 'Moto', 'parent' => 1),
            array('id' => 4, 'text' => 'Vélo', 'parent' => 1),
            array('id' => 5, 'text' => 'Maison', 'parent' => 0),
            array('id' => 6, 'text' => 'Cuisine', 'parent' => 5),
            array('id' => 7, 'text' => 'Chambre', 'parent' => 5),
        );

        $this->response($categories);
    }

    public function post($params)
    {

        $title = $params['title'];
        $content = $params['content'];
        $category = $params['category'];
        $url = $params['url'];

        // crééz la fiche ici et récupérez son url et son ID dans votre base

        $is_posted = rand(0, 1);
        if ($is_posted == 0) {
            $this->response(array('status' => 'error', 'message' => 'Le titre est trop long'));
        } elseif ($is_posted == 1) {
            $this->response(array('status' => 'added', 'url' => 'http://www.monsite.com/url-de-la-fiche.html', 'id' => 1234));
        }
    }

    public function delete($params)
    {
        $id = $params['id'];

        // supprimez la fiche

        $id_deleted = rand(0, 1);
        if ($id_deleted == 0) {
            $this->response(array('status' => 'error', 'message' => 'Identifiant introuvable'));
        } else {
            $this->response(array('status' => 'deleted'));
        }
    }
}