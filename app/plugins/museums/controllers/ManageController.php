<?php
/* ----------------------------------------------------------------------
 * app/plugins/museumAdmin/controllers/ManageController.php :
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

require_once(__CA_APP_DIR__.'/plugins/museums/modules/museums.php');

class ManageController extends ActionController {
	# -------------------------------------------------------
	private $pt_museum;
	private $opo_app_plugin_manager;
	# -------------------------------------------------------
	#
	# -------------------------------------------------------
	public function __construct(&$po_request, &$po_response, $pa_view_paths=null) {

		// Set view path for plugin views directory
		if (!is_array($pa_view_paths)) { $pa_view_paths = array(); }
		$pa_view_paths[] = __CA_APP_DIR__."/plugins/museums/themes/default/views";

		parent::__construct($po_request, $po_response, $pa_view_paths);


	   
		
	}
	# -------------------------------------------------------
	public function Edit() {
		AssetLoadManager::register("bundleableEditor");
		$t_museum = $this->getMuseumObject();
	   
	   $va_profile_prefs = $t_museum->getValidPreferences('profile');
		if (is_array($va_profile_prefs) && sizeof($va_profile_prefs)) {
			$va_elements = array();
		   foreach($va_profile_prefs as $vs_pref) {
			   $va_pref_info = $t_museum->getPreferenceInfo($vs_pref);
			   $va_elements[$vs_pref] = array('element' => $t_museum->preferenceHtmlFormElement($vs_pref), 'info' => $va_pref_info, 'label' => $va_pref_info['label']);
		   }
		   
		   $this->view->setVar("profile_settings", $va_elements);
	   }
		
		$this->render('museum_edit_html.php');
	}
	# -------------------------------------------------------
	public function Save() {
		AssetLoadManager::register('tableList');
		
		$t_museum = $this->getmuseumObject();
		
		//$this->opo_app_plugin_manager->hookBeforemuseumSaveData(array('museum_id' => $t_museum->getPrimaryKey(), 'instance' => $t_museum));
		
		$vb_send_activation_email = false;
		if($t_museum->get("museum_id") && $this->request->config->get("email_museum_when_account_activated") && ($_REQUEST["active"] != $t_museum->get("active"))){
			$vb_send_activation_email = true;
		}
		$t_museum->setMode(ACCESS_WRITE);
		foreach($t_museum->getFormFields() as $vs_f => $va_field_info) {

			$t_museum->set($vs_f, $_REQUEST[$vs_f]);
			if ($t_museum->numErrors()) {
				$this->request->addActionErrors($t_museum->errors(), 'field_'.$vs_f);
			}
		}
		
		if ($this->request->getParameter('entity_id', pInteger) == 0) {
			$t_museum->set('entity_id', null);
		}

	   if(AuthenticationManager::supports(__CA_AUTH_ADAPTER_FEATURE_UPDATE_PASSWORDS__)) {
		   if ($this->request->getParameter('password', pString) != $this->request->getParameter('password_confirm', pString)) {
			   $this->request->addActionError(new ApplicationError(1050, _t("Password does not match confirmation. Please try again."), "plugins/museumAdmin/controllers/museumController->Save()", '', false, false), 'field_password');
		   }
	   }
		
		AppNavigation::clearMenuBarCache($this->request);	// clear menu bar cache since changes may affect content
		
		if($this->request->numActionErrors() == 0) {
		   if (!$t_museum->getPrimaryKey()) {
			   $t_museum->insert();
			   $vs_message = _t("Added museum");
		   } else {
			   $t_museum->update();
			   $vs_message = _t("Saved changes to museum");
		   }
		   
			//$this->opo_app_plugin_manager->hookAftermuseumSaveData(array('museum_id' => $t_museum->getPrimaryKey(), 'instance' => $t_museum));
		
		   if ($t_museum->numErrors()) {
			   foreach ($t_museum->errors() as $o_e) {
				   $this->request->addActionError($o_e, 'general');
				   
				   $this->notification->addNotification($o_e->getErrorDescription(), __NOTIFICATION_TYPE_ERROR__);
			   }
		   } else {
			   // Save profile prefs
			   $va_profile_prefs = $t_museum->getValidPreferences('profile');
			   if (is_array($va_profile_prefs) && sizeof($va_profile_prefs)) {
				   
					//$this->opo_app_plugin_manager->hookBeforemuseumSavePrefs(array('museum_id' => $t_museum->getPrimaryKey(), 'instance' => $t_museum));
					
					$va_changed_prefs = array();
				   foreach($va_profile_prefs as $vs_pref) {
					   if ($this->request->getParameter('pref_'.$vs_pref, pString) != $t_museum->getPreference($vs_pref)) {
						   $va_changed_prefs[$vs_pref] = true;
					   }
					   $t_museum->setPreference($vs_pref, $this->request->getParameter('pref_'.$vs_pref, pString));
				   }
				   
				   $t_museum->update();
				   
					//$this->opo_app_plugin_manager->hookAftermuseumSavePrefs(array('museum_id' => $t_museum->getPrimaryKey(), 'instance' => $t_museum, 'modified_prefs' => $va_changed_prefs));
			   }
			   
			   if($vb_send_activation_email){
				   # --- send email confirmation
				   $o_view = new View($this->request, array($this->request->getViewsDirectoryPath()));
   
				   # -- generate email subject line from template
				   $vs_subject_line = $o_view->render("mailTemplates/account_activation_subject.tpl");
   
				   # -- generate mail text from template - get both the text and the html versions
				   $vs_mail_message_text = $o_view->render("mailTemplates/account_activation.tpl");
				   $vs_mail_message_html = $o_view->render("mailTemplates/account_activation_html.tpl");
				   caSendmail($t_museum->get('email'), $this->request->config->get("ca_admin_email"), $vs_subject_line, $vs_mail_message_text, $vs_mail_message_html, null, null, null, ['source' => 'Account activation']);						
			   }

			   $this->notification->addNotification($vs_message, __NOTIFICATION_TYPE_INFO__);
		   }
	   } else {
		   $this->notification->addNotification(_t("Your entry has errors. See below for details."), __NOTIFICATION_TYPE_ERROR__);
	   }

	   if ($this->request->numActionErrors()) {
		   $this->render('museum_edit_html.php');
	   } else {
		   $this->ListMuseums();
		}
	}
	# -------------------------------------------------------
	public function ListMuseums() {
		if (!$this->request->user->canDoAction('can_manage_museums')) { return; }

		AssetLoadManager::register('tableList');
		/*if (!strlen($vn_museumclass = $this->request->getParameter('museumclass', pString))) {
			$vn_museumclass = $this->request->museum->getVar('ca_museums_default_museumclass');
		} else {
			$vn_museumclass = (int)$vn_museumclass;
			$this->request->museum->setVar('ca_museums_default_museumclass', $vn_museumclass);
		}
		*/if ((!$vn_museumclass) || ($vn_museumclass < 0) || ($vn_museumclass > 255)) { $vn_museumclass = 0; }
		$t_museum = $this->getMuseumObject();
		/*$this->view->setVar('museumclass', $vn_museumclass);
		$this->view->setVar('museumclass_displayname', $t_museum->getChoiceListValue('museumclass', $vn_museumclass));
		*/
		$vs_sort_field = $this->request->getParameter('sort', pString);
		$this->view->setVar('museum_list', $t_museum->getMuseumList(array('sort' => $vs_sort_field, 'sort_direction' => 'asc')));

		$this->render('museum_list_html.php');
	}
	# -------------------------------------------------------
	public function Delete() {
		$t_museum = $this->getMuseumObject();
		if ($this->request->getParameter('confirm', pInteger)) {
			$t_museum->setMode(ACCESS_WRITE);
			$t_museum->delete(false);

			if ($t_museum->numErrors()) {
				foreach ($t_museum->errors() as $o_e) {
				   $this->request->addActionError($o_e, 'general');
			   }
			} else {
				$this->notification->addNotification(_t("Deleted museum"), __NOTIFICATION_TYPE_INFO__);
			}
			$this->ListMuseums();
			return;
		} else {
			$this->render('museum_delete_html.php');
		}
	}
	# -------------------------------------------------------
	public function DownloadmuseumReport() {
		$vs_download_format = $this->request->getParameter("download_format", pString);
		if(!$vs_download_format){
			$vs_download_format = "tab";
		}
		$this->view->setVar("download_format", $vs_download_format);
		switch($vs_download_format){
			default:
			case "tab":
				$this->view->setVar("file_extension", "txt");
				$this->view->setVar("mimetype", "text/plain");
				$vs_delimiter_col = "\t";
				$vs_delimiter_row = "\n";
			break;
			# -----------------------------------
			case "csv":
				$this->view->setVar("file_extension", "txt");
				$this->view->setVar("mimetype", "text/plain");
				$vs_delimiter_col = ",";
				$vs_delimiter_row = "\n";
			break;
			# -----------------------------------
		}
		
		$o_db = new Db();
		$t_museum = new museums();
		$va_fields = array("lname", "fname", "email", "museum_name", "museumclass", "active");
		$va_profile_prefs = $t_museum->getValidPreferences('profile');
		$va_profile_prefs_labels = array();
		foreach($va_profile_prefs as $vs_pref) {
		   $va_pref_info = $t_museum->getPreferenceInfo($vs_pref);
		   $va_profile_prefs_labels[$vs_pref] = $va_pref_info["label"];
	   }
		$qr_museums = $o_db->query("
			SELECT * 
			FROM mana_museums u
			ORDER BY u.museum_id DESC
		");
		if($qr_museums->numRows()){
			$va_rows = array();
			# --- headings
			$va_row = array();
			# --- headings for field values
			foreach($va_fields as $vs_field){
				switch($vs_field){
					# --------------------
					case "roles":
						$va_row[] = _t("Roles");
						break;
					# --------------------
					case "groups":
						$va_row[] = _t("Groups");
						break;
					# --------------------
					case "last_login":
						$va_row[] = _t("Last login");
						break;
					# --------------------
					default:
						$va_row[] = $t_museum->getDisplayLabel("mana_museums.{$vs_field}");
						break;
					# --------------------
				}
			}
			# --- headings for profile prefs
			foreach($va_profile_prefs_labels as $vs_pref => $vs_pref_label){
				$va_row[] = $vs_pref_label;
			}
			$va_rows[] = join($vs_delimiter_col, $va_row);
			reset($va_fields);
			reset($va_profile_prefs_labels);
			$o_tep = new TimeExpressionParser();
			while($qr_museums->nextRow()){
				$va_row = array();
				# --- fields
				foreach($va_fields as $vs_field){
				   switch($vs_field){
					   case "museumclass":
						   $va_row[] = $t_museum->getChoiceListValue($vs_field, $qr_museums->get("mana_museums.".$vs_field));
						   break;
					   # -----------------------
					   case "active":
						   $va_row[] = ($qr_museums->get("mana_museums.{$vs_field}") == 1) ? _t("active") : _t("not active");
						   break;
					   # -----------------------
					   default:
						   if($vs_download_format == "csv"){
							   $va_row[] = str_replace(",", "-", $qr_museums->get("mana_museums.".$vs_field));
						   }else{
							   $va_row[] = $qr_museums->get("mana_museums.".$vs_field);
						   }
						   break;
					   # -----------------------	
				   }
			   }
			   # --- profile prefs
			   foreach($va_profile_prefs_labels as $vs_pref => $vs_pref_label){
				   $t_museum->load($qr_museums->get("mana_museums.museum_id"));
				   $va_row[] = $t_museum->getPreference($vs_pref);
			   }
			   $va_rows[] = join($vs_delimiter_col, $va_row);
			}
			$vs_file_contents = join($vs_delimiter_row, $va_rows);
			$this->view->setVar("file_contents", $vs_file_contents);
			return $this->render('museum_report.php');
		}else{
			$this->notification->addNotification(_t("There are no museums"), __NOTIFICATION_TYPE_INFO__);
			$this->ListMuseums();
			return;
		}
	}
	# -------------------------------------------------------
	public function Approve() {
		
		$va_errors = array();
		$pa_museum_ids = $this->request->getParameter('museum_id', pArray);
		$ps_mode = $this->request->getParameter('mode', pString);
		if(is_array($pa_museum_ids) && (sizeof($pa_museum_ids) > 0)){
		   $t_museum = new museums();
		   $vb_send_activation_email = false;
		   if($this->request->config->get("email_museum_when_account_activated")){
			   $vb_send_activation_email = true;
		   }
	   
		   foreach($pa_museum_ids as $vn_museum_id){
			   $t_museum->load($vn_museum_id);
			   
			   if (!$t_museum->getPrimaryKey()) {
				   $va_errors[] = _t("The museum does not exist");
			   }
		   
			   $t_museum->setMode(ACCESS_WRITE);
			   $t_museum->set("active", 1);
			   if($t_museum->numErrors()){
				   $va_errors[] = join("; ", $t_museum->getErrors());
			   }else{
				   $t_museum->update();
				   if($t_museum->numErrors()){
					   $va_errors[] = join("; ", $t_museum->getErrors());
				   }else{
					   # --- does a notification email need to be sent to the museum to let them know account is active?
					   if($vb_send_activation_email){
						   # --- send email confirmation
						   $o_view = new View($this->request, array($this->request->getViewsDirectoryPath()));
   
						   # -- generate email subject line from template
						   $vs_subject_line = $o_view->render("mailTemplates/account_activation_subject.tpl");
   
						   # -- generate mail text from template - get both the text and the html versions
						   $vs_mail_message_text = $o_view->render("mailTemplates/account_activation.tpl");
						   $vs_mail_message_html = $o_view->render("mailTemplates/account_activation_html.tpl");
						   caSendmail($t_museum->get('email'), $this->request->config->get("ca_admin_email"), $vs_subject_line, $vs_mail_message_text, $vs_mail_message_html, null, null, null, ['source' => 'Account activation']);						
					   }
					   
				   }
			   }
		   
		   }
		   if(sizeof($va_errors) > 0){
			   $this->notification->addNotification(implode("; ", $va_errors), __NOTIFICATION_TYPE_ERROR__);
		   }else{
			   $this->notification->addNotification(_t("The registrations have been approved"), __NOTIFICATION_TYPE_INFO__);
		   }
	   }else{
		   $this->notification->addNotification(_t("Please use the checkboxes to select registrations for approval"), __NOTIFICATION_TYPE_WARNING__);
	   }
		switch($ps_mode){
			case "dashboard":
				$this->response->setRedirect(caNavUrl($this->request, "", "Dashboard", "Index"));
			break;
			# -----------------------
			default:
				$this->ListMuseums();
			break;
			# -----------------------
		}
	}
	# -------------------------------------------------------
	# Utilities
	# -------------------------------------------------------
	private function getMuseumObject($pb_set_view_vars=true, $pn_museum_id=null) {
		if (!($t_museum = $this->pt_museum)) {
		   if (!($vn_museum_id = $this->request->getParameter('museum_id', pInteger))) {
			   $vn_museum_id = $pn_museum_id;
		   }
		   $t_museum = new museums($vn_museum_id);
	   }
		if ($pb_set_view_vars){
			$this->view->setVar('museum_id', $vn_museum_id);
			$this->view->setVar('t_museum', $t_museum);
		}
		$this->pt_museum = $t_museum;
		return $t_museum;
	}
	# -------------------------------------------------------
}
