<?php
$api_key = get_option($this->prefix . 'api_key');
$api_secret = get_option($this->prefix . 'api_secret');
$email = get_option($this->prefix . 'email');
?>

    <div class="wrap">

        <h2> <?= __('Soumettre Source : options', 'soumettre_source'); ?></h2>

        <form name="form1" method="post" action="">

            <p>
                Email :
                <input type="text" name="email" value="<?php echo $email; ?>" size="20">
                Renseignez l'email avec lequel vous avez créé votre compte sur <a target="_blank"
                                                                                  href="https://soumettre.fr/">Soumettre.fr</a>.
                Si vous n'avez pas encore de compte, vous devrez en créer un.
            </p>

            <p>
                API Key :
                <input type="text" name="api_key" value="<?php echo $api_key; ?>" size="20">
            </p>

            <p>
                API Secret :
                <input type="text" name="api_secret" value="<?php echo $api_secret; ?>" size="20">
            </p>


            <p class="submit">
                <input type="submit" name="soumettre_source_submit" class="button-primary"
                       value="<?php esc_attr_e('Save Changes') ?>"/>
            </p>

            <hr/>

            <?php if ($email && $api_key && $api_secret) { ?>
                <table>
                    <tr>
                        <td><button id="soumettre_source_test_api" class="button-primary">Tester la connexion API</button></td>
                        <td><span id="test_api_res"></span></td>
                    </tr>
                    <tr>
                        <td><button id="soumettre_source_site_add" class="button-primary">Ajouter mon site en tant que Source</button></td>
                        <td><span id="site_add_res"></span></td>
                    </tr>
                </table>


            <?php } else { ?>
                <button disabled="disabled">Tester la connexion API</button>
            <?php } ?>

        </form>

<?php