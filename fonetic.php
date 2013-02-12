<?php

/**
 * @package fonetic
 * @version 1.0.1
 */

/*
   Plugin Name: Fonetic
   Plugin URI: http://wordpress.org/extend/plugins/fonetic/
   Description: Fonetic is a web call feature for your website that allows your visitors to be called back for free. Get a real leverage for your online conversions !
   Version: 1.0
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
define('FONETIC_PATH', ABSPATH.PLUGINDIR."/fonetic/");
define('FONETIC_URL', WP_PLUGIN_URL."/fonetic/");

//==============================================================================
// Admin page Routes and Callbacks
//==============================================================================
class Fonetic_Admin {

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
		add_action( 'wp_enqueue_scripts', array(&$this, 'front_js'));
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
			<a href="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-1.jpg" target="_BLANK"><img src="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-1.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-2.jpg" target="_BLANK"><img src="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-2.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-3.jpg" target="_BLANK"><img src="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-3.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-4.jpg" target="_BLANK"><img src="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-4.jpg" style="width:200px;" /></a>
			<a href="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-5.jpg" target="_BLANK"><img src="'.FONETIC_URL.'Fonetic-Wordpress-Screenshot-5.jpg" style="width:200px;" /></a>
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
		$sql = $this->wpdb->prepare("SELECT * FROM ".$this->table_name." LIMIT 1");
		$data = $this->wpdb->get_results($sql);

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

		$sql = $this->wpdb->prepare("SELECT * FROM ".$this->table_name." LIMIT 1");
		$data = $this->wpdb->get_results($sql);

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

			$this->wpdb->insert($this->table_name, array(
			    'javascript' => '',
			));
		}
	}

	//==============================================================================
	// Register Uninstall
	//==============================================================================
	public function uninstall() {

		if($this->wpdb->get_var("SHOW TABLES LIKE '".$this->table_name."'") == $this->table_name) {

			$query = "DROP TABLE `".$this->table_name."`";
			$this->wpdb->query($query);
		}
	}
}

$fonetic = &new Fonetic_Admin();

?>
