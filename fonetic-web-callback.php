<?php

/**
 * @package Fonetic Web Callback
 * @version 2.0.1
 */

/*
   Plugin Name: Fonetic Web Callback
   Plugin URI: http://wordpress.org/extend/plugins/fonetic/
   Description: Fonetic is a web call feature for your website that allows your visitors to be called back for free. Get a real leverage for your online conversions !
   Version: 2.0.1
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

		add_menu_page($this->title.' - Configuration', 'Fonetic Express', 'administrator', 'fonetic-plugin', array(&$this, 'admin_configuration'), ''.FONETIC_URL.'images/fonetic-menu-icon.png');
	}

	public function admin_configuration() {

		// enregistrement des informations en base de données
		if (isset($_POST['save_options'])) {
			$this->wpdb->query("
				UPDATE ".$this->table_name."
				SET enabled = '".$_POST['fonetic_enabled']."',
					short_key = '".$_POST['fonetic_short_key']."',
					label = '".$_POST['fonetic_label']."',
					color = '".$_POST['fonetic_color']."',
					inverted = '".$_POST['fonetic_inverted']."',
					animated = '".$_POST['fonetic_animated']."',
					position = '".$_POST['fonetic_position']."',
					font = '".$_POST['fonetic_font']."',
					background_color = '".$_POST['fonetic_background_color']."',
					background_opacity = '".$_POST['fonetic_background_opacity']."',
					border_color = '".$_POST['fonetic_border_color']."',
					border_size = '".$_POST['fonetic_border_size']."'
				LIMIT 1
			");
		}

		// récupération des information en base de données
		$data = $this->wpdb->get_results("SELECT * FROM ".$this->table_name." LIMIT 1");

		echo '<script type="text/javascript" src="'.FONETIC_URL.'jscolor/jscolor.js"></script>';

		echo '<img style="float:right;" src="'.FONETIC_URL.'images/webcallback.png" />
		<div id="fonetic_admin" class="wrap">
    		<div class="icon32" id="icon-options-general"></div>
    		<h2>'.$this->title.' - '.__('Réglages de votre widget', 'fonetic').'</h2>
    		'.__('Fonetic en quelques mots', 'fonetic').'
			'.__('Rassurez vos internautes et diminuez les abandons de paniers !', 'fonetic');
			if (isset($_POST['save_options'])) {
				echo '<div class="updated"><p><strong>'.__("Widget enregistré !", "fonetic").'</strong></p></div>';
			}
			echo '<br />
			<form method="POST" action="'.$_SERVER['REQUEST_URI'].'">
				<legend>'.__('Paramètres', 'fonetic').'</legend>
				<h3>'.__('Votre compte Fonetic', 'fonetic').'</h3>
				<p><label for="fonetic_enabled">'.__('Activer le widget', 'fonetic').'</label>
					<select id="fonetic_enabled" name="fonetic_enabled">
						<option value="1" '.(($data[0]->enabled == true) ? 'selected ="selected"' : '').'>'.__('Oui', 'fonetic').'</option>
						<option value="0" '.(($data[0]->enabled == false) ? 'selected ="selected"' : '').'>'.__('Non', 'fonetic').'</option>
					</select>
				</p>
				<p>
					<label for="fonetic_short_key">'.__('Votre clé Fonetic Express', 'fonetic').'</label>
					<input id="fonetic_short_key" name="fonetic_short_key" type="text" value="'.$data[0]->short_key.'" size="50" />
				</p>
				<h3>'.__('Paramètres du bouton déclencheur', 'fonetic').'</h3>
				<p>
					<label for="fonetic_label">'.__('Label', 'fonetic').'</label>
					<input id="fonetic_label" name="fonetic_label" type="text" value="'.$data[0]->label.'"  size="50" />
				</p>
				<p>
					<label for="fonetic_color">'.__('Couleur', 'fonetic').'</label>
					<input class="color" id="fonetic_color" name="fonetic_color" type="text" value="'.$data[0]->color.'" size="6" />
					<span>'.__('code couleur hexadecimal', 'fonetic').'</span>
				</p>
				<p><label for="fonetic_inverted">'.__('Inversion les couleurs', 'fonetic').'</label>
					<select id="fonetic_inverted" name="fonetic_inverted">
						<option value="1" '.(($data[0]->inverted == true) ? 'selected ="selected"' : '').'>'.__('Oui', 'fonetic').'</option>
						<option value="0" '.(($data[0]->inverted == false) ? 'selected ="selected"' : '').'>'.__('Non', 'fonetic').'</option>
					</select>
				</p>
				<p><label for="fonetic_animated">'.__('Animation', 'fonetic').'</label>
					<select id="fonetic_animated" name="fonetic_animated">
						<option value="1" '.(($data[0]->animated == true) ? 'selected ="selected"' : '').'>'.__('Oui', 'fonetic').'</option>
						<option value="0" '.(($data[0]->animated == false) ? 'selected ="selected"' : '').'>'.__('Non', 'fonetic').'</option>
					</select>
				</p>
				<p><label for="fonetic_position">'.__('Position', 'fonetic').'</label>
					<select id="fonetic_position" name="fonetic_position">';
						$position_arr = array('middle-right', 'top-right', 'top-left', 'middle-right', 'middle-left', 'bottom-right', 'bottom-left');
						foreach ($position_arr AS $k => $v) {
							$selected = ($data[0]->position == $v) ? 'selected="selected"' : '';
							echo '<option value="'.$v.'" '.$selected.'>'.$v.'</option>';
						}
					echo '</select>
				</p>
				<p><label for="fonetic_font">'.__('Police du texte', 'fonetic').'</label>
					<select id="fonetic_font" name="fonetic_font">
						<option value="OpenSans-Semibold">OpenSans-Semibold</option>
						<option value="Arial">Arial</option>
						<option value="OpenSans-Regular">OpenSans-Regular</option>
						<option value="OpenSans-Italic">OpenSans-Italic</option>
						<option value="OpenSans-LightItalic">OpenSans-LightItalic</option>
						<option value="OpenSans-Semibold">OpenSans-Semibold</option>
						<option value="OpenSans-SemiboldItalic">OpenSans-SemiboldItalic</option>
						<option value="OpenSans-Bold">OpenSans-Bold</option>
						<option value="OpenSans-BoldItalic">OpenSans-BoldItalic</option>
						<option value="OpenSans-Extrabold">OpenSans-Extrabold</option>
						<option value="OpenSans-ExtraboldItalic">OpenSans-ExtraboldItalic</option>
					</select>
				</p>
				<h3>'.__('Paramètres de la fenêtre', 'fonetic').'</h3>
				<p>
					<label for="fonetic_background_color">'.__('Couleur de fond', 'fonetic').'</label>
					<input class="color" id="fonetic_background_color" name="fonetic_background_color" type="text" value="'.$data[0]->background_color.'" size="6" />
					<span>'.__('code couleur hexadecimal', 'fonetic').'</span>
				</p>
				<p>
					<label for="fonetic_background_opacity">'.__('Transparence du fond', 'fonetic').'</label>
					<input id="fonetic_background_opacity" name="fonetic_background_opacity" type="text" value="'.$data[0]->background_opacity.'" size="6" />
					<span>'.__('valeur entre 0 et 100', 'fonetic').'</span>
				</p>
				<p>
					<label for="fonetic_border_color">'.__('Couleur de la bordure', 'fonetic').'</label>
					<input class="color" id="fonetic_border_color" name="fonetic_border_color" type="text" value="'.$data[0]->border_color.'" size="6" />
					<span>'.__('code couleur hexadecimal', 'fonetic').'</span>
				</p>
				<p>
					<label for="fonetic_border_size">'.__('Epaisseur de la bordure', 'fonetic').'</label>
					<input id="fonetic_border_size" name="fonetic_border_size" type="text" value="'.$data[0]->border_size.'" size="6" />
					<span'.__('valeur entre 0 et 25', 'fonetic').'></span>
				</p>
				<p><input class="button-primary" class="left" type="submit" name="save_options" value="'.__('Enregistrer les modifications', 'fonetic').'" /></p>
			</form>
		</div id="fonetic_admin">';

		echo __('Comment faire fonctionner Fonetic ?', 'fonetic');

		/*
		echo '
		<h1>'.$this->title.' - '.__('Widget de mise en relation', 'fonetic').'</h1>
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
		*/
	}

	//==============================================================================
	// Register Javascript for Front Pages
	//==============================================================================
	function front_js(){
		//wp_enqueue_script('tb-main', FONETIC_URL.'fonetic.js', array(), false, true);
		$data = $this->wpdb->get_results("SELECT * FROM ".$this->table_name." LIMIT 1");

$html = "<!--Fonetic Widget-->
<script type='text/javascript'>
var widgetOptions = {
	'key': '".$data[0]->short_key."', // votre cle fonetic (short)
	'tab': {
		'enabled': ".(($data[0]->enabled) ? 'true' : 'false').", // activer le bouton
		'animated': ".(($data[0]->animated) ? 'true' : 'false').", // animation du bouton
		'inverted': ".(($data[0]->inverted) ? 'true' : 'false').", // inversion des couleurs
		'label': '".addslashes($data[0]->label)."', // label du bouton
		'color': '".$data[0]->color."', // couleur du bouton
		'position': '".$data[0]->position."', // position du bouton
		'font': '".$data[0]->font."' // police du texte
	},
	'overlay': {
		'background_color': '".$data[0]->background_color."', // couleur de l'overlay
		'background_opacity': '".$data[0]->background_opacity."', // opacite de l'overlay
		'border_color': '".$data[0]->border_color."', // couleur de la bordure
		'border_size': '".$data[0]->border_size."' // couleur de la bordure
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
";

		echo $html."\r\n";
	}

	//==============================================================================
	// Register Install
	//==============================================================================
	public function install() {

		if($this->wpdb->get_var("SHOW TABLES LIKE '".$this->table_name."'") != $this->table_name) {

			$query  = "
				CREATE TABLE IF NOT EXISTS `".$this->table_name."` (
				  `enabled` tinyint(1) NOT NULL,
				  `short_key` varchar(35) NOT NULL,
				  `label` varchar(150) NOT NULL,
				  `color` varchar(6) NOT NULL,
				  `inverted` tinyint(1) NOT NULL,
				  `animated` tinyint(1) NOT NULL,
				  `position` varchar(15) NOT NULL,
				  `font` varchar(15) NOT NULL,
				  `background_color` varchar(6) NOT NULL,
				  `background_opacity` float NOT NULL,
				  `border_color` varchar(6) NOT NULL,
				  `border_size` tinyint(3) unsigned NOT NULL
				);
			";

			require_once(ABSPATH.'wp-admin/includes/upgrade.php');
			dbDelta($query);

			$this->wpdb->insert($this->table_name, array(
				'enabled' => "0",
				'short_key' => "",
				'label' => "Besoin d'information ?",
				'color' => "FF0000",
				'inverted' => "1",
				'animated' => "1",
				'position' => "middle-right",
				'font' => "OpenSans-Semiblod",
				'background_color' => "000000",
				'background_opacity' => "50",
				'border_color' => "000000",
				'border_size' => "5"
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
