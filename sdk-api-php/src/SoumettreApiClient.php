<?php namespace Soumettre;

class SoumettreApiClient
{
    protected $endpoint = 'https://soumettre.fr/api/';
    protected $email;
    protected $api_key;
    protected $api_secret;

    /**
     * Si les paramètres ne sont pas fournis au constructeur, vous DEVEZ utiliser une fonction  *_load_credentials
     *
     * @param string $email
     * @param string $api_key
     * @param string $api_secret
     */
    function __construct($email = null, $api_key = null, $api_secret = null)
    {
        $this->email = $email;
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }

    /**
     * Charge les credentials depuis la base de données (utilisé par Wordpress)
     *
     * @param string $prefix Préfixe SQL pour les options
     */
    public function wp_set_credentials($prefix = 'soum_sour_')
    {
        $this->email = get_option($prefix . 'email');
        $this->api_key = get_option($prefix . 'api_key');
        $this->api_secret = get_option($prefix . 'api_secret');
    }

    /**
     * Change le endpoint de l'API
     * @param string $url URL where to find API
     */
    public function set_endpoint($url)
    {
        $this->endpoint = $url;
    }

    /**
     * Teste la connexion à l'API via une requête signée
     */
    public function test()
    {
        return $this->request('test');
    }

    /**
     * Enregistre votre site dans la base de Soumettre.fr. pour Wordpress, passer get_home_url() en paramètre
     *
     * @param string $url URL de la homepage du site.
     */
    public function site_add($url)
    {
        return $this->request('site/register', array('site' => $url));
    }

    /**
     * Envoie une requête signée
     *
     * @param string $endpoint Service à appeler (ex: site/register)
     * @param array $params
     * @return object Réponse en JSON
     */
    public function request($service, $post_params = array())
    {
        $endpoint = $this->endpoint . $service;
        $post_params = $this->sign($service, $post_params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $output = curl_exec($ch);
        curl_close($ch);

        return json_encode($output);
    }

    /**
     * Ajoute les paramètres de signature à une requête (utilisée par $this->request)
     *
     * @param string $endpoint
     * @param array $post_params
     * @return array
     */
    public function sign($endpoint, $post_params)
    {
        $time = time();

        $signature = md5(sprintf("%s-%s-%d-%s-%s",
            $this->api_key,
            $this->api_secret,
            $time,
            $endpoint,
            serialize($post_params)
        ));

        $post_params['user'] = $this->email;
        $post_params['api_key'] = $this->api_key;
        $post_params['time'] = $time;
        $post_params['sign'] = $signature;

        return $post_params;
    }

    /**
     * Lors du traitement d'une requête, vérifie la signature fournie
     *
     * @param string $endpoint
     * @param array $params
     * @return bool
     * @throws \Exception Signature invalide
     */
    protected function check_signature($endpoint, $params)
    {

        $signature = $params['sign'];
        $time = $params['time'];

        unset($params['user']);
        unset($params['api_key']);
        unset($params['time']);
        unset($params['sign']);
        unset($params['method']);

        if (isset($params['title'])) {
            $params['title'] = stripslashes($params['title']);
        }
        if (isset($params['content'])) {
            $params['content'] = stripslashes($params['content']);
        }
        
        $check = sprintf("%s-%s-%d-%s-%s",
            $this->api_key,
            $this->api_secret,
            $time,
            $endpoint,
            serialize($params)
        );

        if ($signature != md5($check)) {
            die("Signature invalide");
        }

        return true;
    }

    protected function response($array)
    {
        echo json_encode($array);
        die();
    }
}