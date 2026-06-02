<?php
/**
 * Plugin Name:       hkilumets.plugin
 * Description:       Kuvab veebilehe ülaosas kohandatava teateriba koos peitmise ja sulgemise funktsioonidega.
 * Version:           1.0.0
 * Author:            Sinu Nimi
 * License:           GPL-2.0+
 */

if (!defined('ABSPATH')) {
    exit; // Turvalisus: blokeeri otsene juurdepääs failile
}

/**
 * 1. ADMIN MENÜÜ JA SEADETE LEHE LOOMINE
 */
function teateriba_lisa_admin_menuu() {
    // Muuda siin "Sinu Nimi" tekstiks oma nimi
    add_menu_page(
        'hkilumets.plugin Teateriba Seaded',
        'hkilumets.plugin Teateriba', // Admin menüü link
        'manage_options',
        'hkilumets.plugin-teateriba',
        'teateriba_seadete_leht_html',
        'dashicons-megaphone',
        '80'
    );
}
add_action('admin_menu', 'teateriba_lisa_admin_menuu');

/**
 * 2. SEADETE REGISTREERIMINE ANDMEBAASIS
 */
function teateriba_registreeri_seaded() {
    register_setting('teateriba_seadete_grupp', 'teateriba_tekst');
    register_setting('teateriba_seadete_grupp', 'teateriba_taustavarv');
    register_setting('teateriba_seadete_grupp', 'teateriba_tekstivarv');
    register_setting('teateriba_seadete_grupp', 'teateriba_lubatud');
    register_setting('teateriba_seadete_grupp', 'teateriba_ainult_avalehel');
}
add_action('admin_init', 'teateriba_registreeri_seaded');

/**
 * 3. ADMIN VORMI HTML (Kujundus ja valikud)
 */
function teateriba_seadete_leht_html() {
    ?>
    <div class="wrap">
        <h1>Teateriba seaded</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('teateriba_seadete_grupp');
            do_settings_sections('teateriba_seadete_grupp');
            
            // Vaheväärtuste laadimine (koos vaikimisi väärtustega)
            $tekst = get_option('teateriba_tekst', 'Tere tulemast meie lehele!');
            $taust = get_option('teateriba_taustavarv', '#ffcc00');
            $tekstivarv = get_option('teateriba_tekstivarv', '#000000');
            $lubatud = get_option('teateriba_lubatud', '1');
            $ainult_avalehel = get_option('teateriba_ainult_avalehel', '0');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Kuva teateriba (Enable/Disable)</th>
                    <td>
                        <input type="checkbox" name="teateriba_lubatud" value="1" <?php checked('1', $lubatud); ?> />
                        <span class="description">Lülita teateriba sisse või välja</span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Kuva ainult avalehel</th>
                    <td>
                        <input type="checkbox" name="teateriba_ainult_avalehel" value="1" <?php checked('1', $ainult_avalehel); ?> />
                        <span class="description">Kui valitud, siis siselehtedel riba ei näidata</span>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Teate tekst</th>
                    <td>
                        <input type="text" name="teateriba_tekst" value="<?php echo esc_attr($tekst); ?>" class="large-text" required />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Taustavärv</th>
                    <td>
                        <input type="color" name="teateriba_taustavarv" value="<?php echo esc_attr($taust); ?>" />
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">Teksti värv</th>
                    <td>
                        <input type="color" name="teateriba_tekstivarv" value="<?php echo esc_attr($tekstivarv); ?>" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

/**
 * 4. TEATERIBA KUVAMINE, KUJUNDUS (CSS) JA SULGEMINE (JS)
 */
function teateriba_kuva_riba() {
    // Kontrolli, kas riba on admin paneelis sisse lülitatud
    if (get_option('teateriba_lubatud', '1') !== '1') {
        return;
    }

    // Lisapunkt: Kontrolli, kas kuvamine on piiratud ainult avalehega
    if (get_option('teateriba_ainult_avalehel', '0') === '1' && !is_front_page()) {
        return;
    }

    $tekst = get_option('teateriba_tekst', 'Tere tulemast meie lehele!');
    $taust = get_option('teateriba_taustavarv', '#ffcc00');
    $tekstivarv = get_option('teateriba_tekstivarv', '#000000');
    ?>
    
    <!-- Teateriba HTML element -->
    <div id="wp-custom-teateriba" style="background-color: <?php echo esc_attr($taust); ?>; color: <?php echo esc_attr($tekstivarv); ?>;">
        <span class="teateriba-tekst"><?php echo esc_html($tekst); ?></span>
        <!-- Lisapunkt: X nupp sulgemiseks -->
        <button id="sulge-teateriba" style="color: <?php echo esc_attr($tekstivarv); ?>;">&times;</button>
    </div>

    <!-- Kujundus (CSS) täislaiuses riba jaoks -->
    <style>
        #wp-custom-teateriba {
            width: 100%;
            position: relative;
            z-index: 99999;
            text-align: center;
            padding: 12px 40px 12px 20px;
            font-family: sans-serif;
            font-size: 15px;
            font-weight: bold;
            box-sizing: border-box;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        #sulge-teateriba {
            position: absolute;
            right: 20px;
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            line-height: 1;
            padding: 0;
        }
        /* Korrigeerib WordPressi admin riba, kui see on sisse logitud kasutajal nähtav */
        .admin-bar #wp-custom-teateriba {
            top: 0;
        }
    </style>

    <!-- Lisapunkt: JavaScript (JS) sulgemisnupu tööks koos SessionStorage-ga -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var riba = document.getElementById('wp-custom-teateriba');
            var nupp = document.getElementById('sulge-teateriba');
            
            // Kui kasutaja on juba selle sessiooni jooksul riba sulgenud, peidame selle kohe
            if (sessionStorage.getItem('teateriba_suletud') === 'true') {
                if (riba) riba.style.display = 'none';
            }

            if (nupp && riba) {
                nupp.addEventListener('click', function() {
                    riba.style.display = 'none';
                    // Salvestab oleku, et lehte värskendades ei ilmuks riba uuesti
                    sessionStorage.setItem('teateriba_suletud', 'true');
                });
            }
        });
    </script>
    <?php
}
// Kuvab riba kohe pärast <body> tag-i algust enamikes modernsetes teemades
add_action('wp_body_open', 'teateriba_kuva_riba');
// Varulahendus juhuks, kui teema wp_body_open-it ei toeta (kuvab päises)
add_action('wp_head', 'teateriba_kuva_riba');
