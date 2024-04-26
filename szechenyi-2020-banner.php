<?php
/**
 * Plugin Name:          Szécsényi 2020 Banner
 * Description:          Széchenyi 2020 logót elhelyezése a honlapon.
 * Version:              1.1
 * Author:               FrontendTanfolyam
 * Author URI:           https://frontendtanfolyam.hu/pocsik-emese-frontend-fejleszto/
 * License:              Attribution 4.0 International (CC BY 4.0)
 * License URI:          https://creativecommons.org/licenses/by/4.0/
 * Text Domain:          szechenyi-2020-banner
 * Requires at least:    5.0
 * Tested up to:         6.2.0
 * Requires PHP:         7.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('SZECHENYI_PLUGIN_URL', plugin_dir_url(__FILE__));

add_action('wp_enqueue_scripts', 'szechenyi_enqueue_public_scripts');
add_action('admin_enqueue_scripts', 'szechenyi_enqueue_admin_scripts');
add_action('admin_menu', 'szechenyi_admin_menu');
add_action('admin_init', 'szechenyi_register_settings');
add_action('wp_footer', 'szechenyi_footer');
add_action('wp_head', 'szechenyi_add_custom_css');

function szechenyi_enqueue_public_scripts() {
    wp_enqueue_style('szechenyi', SZECHENYI_PLUGIN_URL . 'szechenyi.css', [], '1.0');
    wp_enqueue_script('szechenyi', SZECHENYI_PLUGIN_URL . 'szechenyi.js', [], '1.0', true);
}

function szechenyi_enqueue_admin_scripts($hook_suffix) {
    if ('toplevel_page_szechenyi' !== $hook_suffix) {
        return;
    }
    wp_enqueue_media();
    wp_enqueue_script('szechenyi-admin-js', SZECHENYI_PLUGIN_URL . 'szechenyi-admin.js', ['jquery'], '1.0', true);
    wp_localize_script('szechenyi-admin-js', 'szechenyi_admin', ['title' => __('Select an image', 'szechenyi-2020-banner'), 'button' => __('Use this image', 'szechenyi-2020-banner')]);
}

function szechenyi_admin_menu() {
    add_menu_page(__('Széchenyi 2020 Banner', 'szechenyi-2020-banner'), __('Széchenyi 2020 Banner', 'szechenyi-2020-banner'), 'manage_options', 'szechenyi', 'szechenyi_options_page_html', 'dashicons-megaphone');
}

function szechenyi_register_settings() {
    $settings = [
        'delay' => 'absint',
        'reopen' => 'absint',
        'zindex' => 'absint',
        'linkto' => 'esc_url_raw',
        'custom_css' => 'wp_kses_post',
        'banner_image' => 'esc_url_raw'
    ];
    foreach ($settings as $setting => $sanitization) {
        register_setting('szechenyi-settings-group', $setting, ['sanitize_callback' => $sanitization]);
    }
}

function szechenyi_options_page_html() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        check_admin_referer('szechenyi_action', 'szechenyi_nonce_field');
    }
    if (isset($_GET['settings-updated']) && $_GET['settings-updated']) {
        add_settings_error('szechenyi_messages', 'szechenyi_message', __('Settings successfully saved.', 'szechenyi-2020-banner'), 'updated');
    }
    settings_errors('szechenyi_messages');
    ?>

<div class="wrap">
    <h2><?php echo esc_html_e('Szécsényi 2020 Banner', 'szechenyi'); ?></h2>
    <form method="post" action="options.php">
        <?php
            settings_fields('szechenyi-settings-group');
            do_settings_sections('szechenyi');
            ?>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><?php echo esc_html_e('Megjelenés késleltetése betöltés után (másodperc)', 'szechenyi'); ?></th>
                <td><input type='number' name="delay" value="<?php echo esc_attr(get_option('delay', '5')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo esc_html_e('Újbóli megjelenés késleltetése bezárás után (perc)', 'szechenyi'); ?></th>
                <td><input type='number' name="reopen" value="<?php echo esc_attr(get_option('reopen', '60')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo esc_html_e('Céloldal URL', 'szechenyi'); ?></th>
                <td><input type="text" name="linkto" value="<?php echo esc_attr(get_option('linkto', '')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo esc_html_e('Z-index (vizuális prioritás)', 'szechenyi'); ?></th>
                <td><input type="number" name="zindex" value="<?php echo esc_attr(get_option('zindex', '1000')); ?>" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo esc_html_e('Banner Kép', 'szechenyi'); ?></th>
                <td>
                    <?php 
                    $banner_image = get_option('banner_image', '');
                    if (empty($banner_image)) { $banner_image = SZECHENYI_PLUGIN_URL . '/sz2020.svg'; }
                        echo '<p><img id="preview_image" width="200" src="' . esc_url($banner_image) . '"></p> <br>';

                    ?>
                    <input type="text" id="banner_image" name="banner_image" value="<?php echo esc_attr(get_option('banner_image', '')); ?>" />
                    <button class="button choose-image button-primary"><?php esc_html_e('Kép kiválasztása', 'szechenyi'); ?></button>
                    <?php  if (!empty(get_option('banner_image', ''))) { ?>
                    <button class="button  components-button  is-destructive remove-image delete" type="button"><?php esc_html_e('Kép eltávolítása', 'szechenyi'); ?></button><br />
                    <?php } ?>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><?php echo esc_html_e('Egyedi CSS', 'szechenyi'); ?></th>
                <td>
                    <textarea name="custom_css" rows="8" cols="50"><?php echo esc_textarea(get_option('custom_css', '')); ?></textarea>
                    <p class="description"><?php echo esc_html_e('Egyéni CSS szabályok. Például:', 'szechenyi'); ?></p>
                    <p class="description">
                        <code>.sz2020-close {background: #000;}</code>
                    </p>
                    <p class="description">
                        <code>.sz2020-close svg {fill: #fff;}</code>
                    </p>
                    <p class="description">
                        <code>.sz2020 img {max-width: 220px;}</code>
                    </p>
                </td>
            </tr>
        </table>
        <?php submit_button(); ?>
    </form>
</div>
<?php
}

function szechenyi_footer() {
    $delay = (int)get_option('delay', '5');
    $reopen = (int)get_option('reopen', '60');
    $linkto = esc_url(get_option('linkto', ''));
    $banner_image = esc_url(get_option('banner_image', ''));

    $popsContent = '<div class="sz2020" data-reopen-delay="' . esc_attr($reopen) . '" data-display-delay="' . esc_attr($delay) . '" style="display: none;">';
    $popsContent .= '<div class="sz2020-close">';
    $popsContent .= '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">';
    $popsContent .= '<path d="M19 5.9l-.9-.9-6.1 6.1L5.9 5l-.9.9 6.1 6.1L5 18.1l.9.9 6.1-6.1 6.1 6.1.9-.9-6.1-6.1z"></path>';
    $popsContent .= '</svg></div>';
    $popsContent .= '<a href="' . $linkto . '" title="Széchenyi 2020 program">';
    $popsContent .= '<img src="' . (!empty($banner_image) ? $banner_image : plugin_dir_url( __FILE__ ) . 'sz2020.svg') . '">';
    $popsContent .= '</a></div>';
    echo $popsContent;
}


function szechenyi_add_custom_css() {
    $zindex = (int)get_option('zindex', '1000');
    $css = get_option('custom_css', '');
    $custom_css = "
        <style type='text/css'>
            .pops-container {z-index: {$zindex};}
            " . wp_strip_all_tags($css) . "
        </style>
    ";
    echo $custom_css;
}