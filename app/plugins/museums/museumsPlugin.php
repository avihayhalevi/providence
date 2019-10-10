<?php
/* ----------------------------------------------------------------------
 * app/plugins/museumAdmin/museumAdminPlugin.php :
 * ----------------------------------------------------------------------
 * Israel Ministry of Sports and Culture 
 * 
 * Plugin for CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * For more information about Israel Ministry of Sports and Culture visit:
 * https://www.gov.il/en/Departments/ministry_of_culture_and_sport
 *
 * For more information about CollectiveAccess visit:
 * http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license.
 *
 * This plugin for CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details. 
 * ----------------------------------------------------------------------
 */

class museumsPlugin extends BaseApplicationPlugin {
	# -------------------------------------------------------
	/**
	 *
	 */
	protected $description = null;

	/**
	 *
	 */
	private $opo_config;

	/**
	 *
	 */
	private $ops_plugin_path;
	# -------------------------------------------------------
	public function __construct($ps_plugin_path) {
		$this->ops_plugin_path = $ps_plugin_path;
		$this->description = _t('Mange museums settings');

		parent::__construct();

		$this->opo_config = Configuration::load($ps_plugin_path.'/conf/museumAdmin.conf');
	}
	# -------------------------------------------------------
	/**
	 * Override checkStatus() to return true - the statisticsViewerPlugin always initializes ok... (part to complete)
	 */
	public function checkStatus() {
		return array(
			'description' => $this->getDescription(),
			'errors' => array(),
			'warnings' => array(),
			'available' => ((bool)$this->opo_config->get('enabled'))
		);
	}
	# -------------------------------------------------------
	/**
	 * Insert activity menu
	 */
	public function hookRenderMenuBar($pa_menu_bar) {
		if ($o_req = $this->getRequest()) {
			if (!$o_req->user->canDoAction('can_manage_museums')) { return $pa_menu_bar; }
			if(!(bool)$this->opo_config->get('enabled')) { return $pa_menu_bar; }

			if (isset($pa_menu_bar['manage'])) {
				$va_menu_items = $pa_menu_bar['manage']['navigation'];
				if (!is_array($va_menu_items)) { $va_menu_items = array(); }
			} else {
				$va_menu_items = array();
			}

			$va_menu_items['manage_museums'] = array(
				'displayName' => _t('manage museums'),
				"default" => array(
					'module' => 'museums',
					'controller' => 'manage',
					'action' => 'ListMuseums'
				)
			);

			if (isset($pa_menu_bar['manage'])) {
				$pa_menu_bar['manage']['navigation'] = $va_menu_items;
			} else {
				$pa_menu_bar['manage'] = array(
					'displayName' => _t('manage'),
					'navigation' => $va_menu_items
				);
			}
		}

		return $pa_menu_bar;
	}
	# -------------------------------------------------------
	/**
	 * Add plugin user actions
	 */
	static function getRoleActionList() {
		return array(
			'can_manage_museums' => array(
				'label' => _t('Can manage museums'),
				'description' => _t('User manage museums settings')
			)
		);
	}
	# -------------------------------------------------------
}
