<?php
	/**
	* Get the name and the version of the plugin - Needed
	*/
	function plugin_version_myassets() {
		return array(
			'name'           => "My Assets",
			'version'        => '1.5',
			'author'         => 'Hwasung Seo(Mars)',
			'license'        => 'GPLv2+',
			'homepage'       => '',
			'minGlpiVersion' => '0.91'
		);
	};

	/**
	 *  Check if the config is ok - Needed
	 */
	function plugin_myassets_check_config() {
		return true;
	}

	/**
	 * Check if the prerequisites of the plugin are satisfied - Needed
	 */
	function plugin_myassets_check_prerequisites() {

		// Check that the GLPI version is compatible
		if (version_compare(GLPI_VERSION, '0.91', 'lt')) {
			echo "This plugin Requires GLPI >= 0.91";
			return false;
		}

		return true;
	}

	function plugin_init_myassets() {

		global $PLUGIN_HOOKS;
		Plugin::registerClass('PluginMyassetsAssets');
		$PLUGIN_HOOKS['csrf_compliant']['myassets'] = true;
		$PLUGIN_HOOKS['display_central']['myassets'] = 'plugin_myassets_display_central';
		$PLUGIN_HOOKS['add_css']['myassets']="myassets.css";

	}
	function plugin_myassets_display_central() {

		$arrayOfAssets = new PluginMyassetsAssets();
		$arrayOfAssets->showAssets();
	}
?>
