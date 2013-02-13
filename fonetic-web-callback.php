<?php

/**
 * @package Fonetic Web Callback
 * @version 1.0.8
 */

/*
   Plugin Name: Fonetic Web Callback
   Plugin URI: http://wordpress.org/extend/plugins/fonetic/
   Description: Fonetic is a web call feature for your website that allows your visitors to be called back for free. Get a real leverage for your online conversions !
   Version: 1.0.8
   Author: <a href="http://www.fonetic.fr/">Fonetic</a>, <a href="http://www.netiva.fr/">Netiva</a>
   Author URI: http://fonetic.fr/
*/

/*
   Copyright (C) 2012 NETIVA SARL
   @author Netiva - Gregory Darche

   This program is free software: you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation, either version 3 of the License, or
   (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

defined('WP_PLUGIN_URL') or die('Restricted access');

define('FONETIC_HOME', "http://fonetic.fr/");
define('FONETIC_PATH', ABSPATH.PLUGINDIR."/fonetic-web-callback/");
define('FONETIC_URL', WP_PLUGIN_URL."/fonetic-web-callback/");

//==============================================================================
// Admin page Routes and Callbacks
//==============================================================================
//if (!class_exists('Fonetic_Web_Callback_Admin')) {
class Fonetic_Web_Callback_Admin {

	private $wpdb = null;
	private $table_name = 'fonetic';
	private $title = 'Fonetic';

	public function __construct() {

		global $wpdb;

		// Database
		$this->wpdb = &$wpdb;
		$this->table_name = $wpdb->prefix.$this->table_name;

		// Languages
		load_plugin_textdomain('fonetic', false, dirname( plugin_basename( __FILE__ )).'/lang/');

		// Install / Uninstall
		register_activation_hook(__FILE__, array(&$this, 'install'));
		register_deactivation_hook(__FILE__, array(&$this, 'uninstall'));

		// Admin
		add_action('admin_init', array(&$this, 'admin_css'));
		add_action('admin_menu', array(&$this, 'admin_menu'));
		add_action('admin_notices', array(&$this, 'admin_notices'));

		// Front
		add_action( 'wp_footer', array(&$this, 'front_js'));
	}

	//==============================================================================
	// Register CSS for Admin Pages
	//==============================================================================
	public function admin_css(){

		wp_enqueue_style('fonetic-admin', FONETIC_URL.'css/admin.css');
	}

	//==============================================================================
	// Register notices
	//==============================================================================
	public function admin_notices() {

		echo '<p id="admin_notices_fonetic">
			<a href="admin.php?page=fonetic-plugin">
				<img src="'.FONETIC_URL.'images/fonetic-logo-widget.png" alt="fonetic" />
			</a>
		</p>';
	}

	//==============================================================================
	// Register menu
	//==============================================================================
	public function admin_menu() {

		add_menu_page($this->title.' - Configuration', 'Fonetic', 'administrator', 'fonetic-plugin', array(&$this, 'admin_menu_overview'));
		add_submenu_page('fonetic-plugin', $this->title.' - '.__('Configuration', 'fonetic'), __('Configuration', 'fonetic'), 'administrator', 'fonetic-configuration', array(&$this, 'admin_menu_configuration'));
	}

	public function admin_menu_overview() {

		echo '<h1>'.$this->title.' - '.__('Widget de mise en relation', 'fonetic').'</h1>
		<hr />
		<img style="float:right;" src="'.FONETIC_URL.'images/webcallback.png" />
		'.__('Description', 'fonetic').'
		<p>
			<a href="'.FONETIC_URL.'screenshot-1.jpg" target="_BLANK"><img src="'.FONETIC_URL.'screenshot-1.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'screenshot-2.jpg" target="_BLANK"><img src="'.FONETIC_URL.'screenshot-2.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'screenshot-3.jpg" target="_BLANK"><img src="'.FONETIC_URL.'screenshot-3.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'screenshot-4.jpg" target="_BLANK"><img src="'.FONETIC_URL.'screenshot-4.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'screenshot-5.jpg" target="_BLANK"><img src="'.FONETIC_URL.'screenshot-5.jpg" style="width:200px;" /></a>
		</p>';
	}

	public function admin_menu_configuration() {

		// enregistrement des informations en base de données
		if (isset($_POST['save_options'])) {

			$this->wpdb->query("
				UPDATE ".$this->table_name."
				SET javascript = '".$_POST['javascript']."'
				LIMIT 1
			");
		}

		// récupération des information en base de données
		$data = $this->wpdb->get_results("SELECT * FROM ".$this->table_name." LIMIT 1");

		echo '<h1>'.$this->title.' - '.__('Configuration', 'fonetic').'</h1>
		<hr />
		'.__('Legende', 'fonetic').'
		<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">
		<textarea name="javascript" class="large-text code" rows="30">'.$data[0]->javascript.'</textarea>
		<input class="button-primary" class="left" type="submit" name="save_options" value="'.__('Enregistrer le widget', 'fonetic').'" />
		<a href="https://groups.google.com/a/fonetic.fr/forum/embed/?place=forum/noreply">'.__('Documentation', 'fonetic').'</a>
		<div class="clear"></div>
		</form>';

		// affichage de la confirmation du POST
		if (isset($_POST['save_options'])) {

			echo '<div class="updated"><p><strong>'.__("Widget enregistré !", "fonetic").'</strong></p></div>';
		}
	}

	//==============================================================================
	// Register Javascript for Front Pages
	//==============================================================================
	function front_js(){
		//wp_enqueue_script('tb-main', FONETIC_URL.'fonetic.js', array(), false, true);
		$data = $this->wpdb->get_results("SELECT * FROM ".$this->table_name." LIMIT 1");

		echo $data[0]->javascript."\r\n";
	}

	//==============================================================================
	// Register Install
	//==============================================================================
	public function install() {

		if($this->wpdb->get_var("SHOW TABLES LIKE '".$this->table_name."'") != $this->table_name) {

			$query  = "
			CREATE TABLE IF NOT EXISTS `".$this->table_name."` (
				`javascript` text NOT NULL
			);
			";

			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			dbDelta($query);

			$this->wpdb->insert($this->table_name, array('javascript' => "
<!--Fonetic Widget-->
<script type='text/javascript'>
var widgetOptions = {
	'key': '8f00879506a81621b16bbc544d45b23b', // votre cle fonetic (short)
	'tab': {
		'enabled': true, // activer le bouton
		'animated': true, // animation du bouton
		'inverted': true, // inversion des couleurs
		'label': 'Fonetic Express Wordpress',	// label du bouton
		'color': 'FF6633', // couleur du bouton
		'position': 'bottom-right', // position du bouton
		'font': 'OpenSans-Regular' // police du texte
	},
	'overlay': {
		'background_color': '333333', // couleur de l'overlay
		'background_opacity': '70', // opacite de l'overlay
		'border_color': '333333', // couleur de la bordure
		'border_size': '4' // couleur de la bordure
	}
};
(function() {
	var el = document.createElement('script');
	el.type = 'text/javascript';
	el.src = ('https:' == document.location.protocol ? 'https://' : 'http://' ) + 'widget.fonetic.fr/widget.js';
	var s = document.getElementsByTagName('script')[0];
	s.parentNode.insertBefore(el, s);
})();
</script>
<!--/Fonetic Widget-->
"
			));
		}

	}

	//==============================================================================
	// Register Uninstall
	//==============================================================================
	public function uninstall() {

		if($this->wpdb->get_var("SHOW TABLES LIKE '".$this->table_name."'") == $this->table_name) {

			$this->wpdb->query("DROP TABLE `".$this->table_name);
		}
	}
}
//}

$Fonetic_Web_Callback = &new Fonetic_Web_Callback_Admin();

?>
