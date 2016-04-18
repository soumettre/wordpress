<?php
$api_key = get_option($this->prefix . 'api_key');
$api_secret = get_option($this->prefix . 'api_secret');
$email = get_option($this->prefix . 'email');
$url_field = get_option($this->prefix . 'url_field');
?>

<div class="wrap">

    <h1>Soumettre / Options</h1>

    <p>
    Si vous n'avez pas encore de compte sur <a target="_blank" href="https://soumettre.fr/">Soumettre.fr</a>, vous devez en créer un.<br />
        Vos identifiants API sont disponibles ici : <a target="_blank" href="https://soumettre.fr/user/api">Récupérer mes identifiants API</a>.
    </p>

    <form name="form1" method="post" action="">
        <table class="form-table">
            <tr valign="top">
                <th scope="row">Email</th>
                <td>
                    <input type="text" name="email" value="<?= esc_attr($email); ?>" class="regular-text ltr" />

                </td>
            </tr>
            <tr valign="top">
                <th scope="row">API Key</th>
                <td>
                    <input type="text" name="api_key" value="<?= esc_attr($api_key); ?>" class="regular-text ltr" />
                </td>
                <td></td>
            </tr>
            <tr valign="top">
                <th scope="row">API Secret</th>
                <td>
                    <input type="text" name="api_secret" value="<?= esc_attr($api_secret); ?>" class="regular-text ltr" />
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    Custom field pour l'URL
                </th>
                <td>
                    <input type="text" name="url_field" value="<?= $url_field; ?>" class="regular-text ltr" />
                    <p class="description">
                        Si votre site contient des fiches (ex: annuaire), entrez ici le nom du custom field où est stockée leur URL (Pour DirectoryPress, c'est "url").<br />
                        Si ce champ est vide, votre site sera traité comme un site de CP (avec un lien intext).
                    </p>
                </td>
            </tr>

            <tr valign="top">
                <th scope="row">
                    Auteur
                </th>
                <td>
                    <?php wp_dropdown_users(array('name' => 'author', 'selected' => get_option('soum_sour_author'))); ?>
                    <p class="description">Quel Auteur associer aux posts de Soumettre ?</p>
                </td>
            </tr>
        </table>

        <p class="submit">
            <input type="submit" name="soumettre_source_submit" class="button-primary"
                   value="<?php esc_attr_e('Save Changes') ?>"/>
        </p>

        <hr/>

        <?php if ($email && $api_key && $api_secret) { ?>
            <table>
                <tr>
                    <td>
                        <button id="soumettre_source_test_api" class="button-primary">Tester la connexion API</button>
                    </td>
                    <td><span id="test_api_res"></span></td>
                </tr>
                <tr>
                    <td>
                        <button id="soumettre_source_site_add" class="button-primary">Ajouter mon site en tant que
                            Source
                        </button>
                    </td>
                    <td><span id="site_add_res"></span></td>
                </tr>
            </table>
        <?php } else { ?>
            <button disabled="disabled">Tester la connexion API</button>
        <?php } ?>

    </form>
</div>