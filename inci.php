<?php

/**
 * INCI
 *
 * @category  Plugin
 * @package   WPinci
 * @author    natata7
 * @copyright 2023 natata7
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GPL 3
 *
 * @wordpress-plugin
 * Plugin Name:       INCI
 * Description:       A WordPress plugin to manage INCI (International Nomenclature of Cosmetic Ingredients).
 * Version:           1.0.5
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            natata7
 * Author URI:        https://github.com/natata7
 * License:           GPLv3+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       wp-inci
 * Domain Path:       /languages
 *
 * WP INCI is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 *
 * You should have received a copy of the GNU General Public License
 * along with WP INCI. If not, see https://www.gnu.org/licenses/gpl-3.0.html.
 */

define('WPINCI_BASE_PATH', plugin_dir_path(__FILE__));
define('WPINCI_BASE_URL', plugins_url());

require_once WPINCI_BASE_PATH . 'vendor/autoload.php';
if (!defined('CMB2_VERSION')) {
    require_once WPINCI_BASE_PATH . 'vendor/cmb2/cmb2/init.php';
}
require_once WPINCI_BASE_PATH . 'class-wp-inci.php';
require_once WPINCI_BASE_PATH . 'class-wp-inci-fields.php';
require_once WPINCI_BASE_PATH . 'admin/update-checker.php';

if (is_admin()) {
    include_once WPINCI_BASE_PATH . 'admin/class-wp-inci-admin.php';
    include_once WPINCI_BASE_PATH . 'admin/class-wp-inci-meta.php';
} else {
    include_once WPINCI_BASE_PATH . 'public/class-wp-inci-frontend.php';
}

foreach (glob(WPINCI_BASE_PATH . "blocks/*.php") as $filename) {
    include_once $filename;
}

if (!class_exists('Mistape_Update_Checker')) {
    include_once(WPINCI_BASE_PATH . 'admin/update-checker.php');
}

$updater = new Inci_Update_Checker(__FILE__);
