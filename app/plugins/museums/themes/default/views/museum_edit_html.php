<?php
/* ----------------------------------------------------------------------
 * app/views/admin/access/museum_edit_html.php :
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2008-2016 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This source code is free and modifiable under the terms of
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 *
 * ----------------------------------------------------------------------
 */

	$t_museum = $this->getVar('t_museum');
	$vn_museum_id = $this->getVar('museum_id');
	
	$va_roles = $this->getVar('roles');
	$va_groups = $this->getVar('groups');
?>
<div class="sectionBox">
<?php
	print $vs_control_box = caFormControlBox(
		caFormSubmitButton($this->request, __CA_NAV_ICON_SAVE__, _t("Save"), 'MuseumsForm').' '.
		caFormNavButton($this->request, __CA_NAV_ICON_CANCEL__, _t("Cancel"), '', 'museums', 'manage', 'ListMuseums', array('museum_id' => 0)), 
		'', 
		($vn_museum_id > 0) ? caFormNavButton($this->request, __CA_NAV_ICON_DELETE__, _t("Delete"), '', 'museums', 'manage', 'Delete', array('museum_id' => $vn_museum_id)) : ''
	);
?>
<?php
	print caFormTag($this->request, 'Save', 'MuseumsForm');

		// ca_museums fields
		foreach($t_museum->getFormFields() as $vs_f => $va_museum_info) {
			print $t_museum->htmlFormElement($vs_f, null, array('field_errors' => $this->request->getActionErrors('field_'.$vs_f)));
		}
?>
		<div>
<?php
		// Output museum profile settings if defined
		$va_museum_profile_settings = $this->getVar('profile_settings');
		if (is_array($va_museum_profile_settings) && sizeof($va_museum_profile_settings)) {
			foreach($va_museum_profile_settings as $vs_field => $va_info) {
				if($va_errors[$vs_field]){
					print "<div class='formErrors' style='text-align: left;'>".$va_errors[$vs_field]."</div>";
				}
				print $va_info['element']."\n";
			}
		}
?>				
		</div>
	</form>
<?php
	print $vs_control_box;
?>
</div>
	<div class="editorBottomPadding"><!-- empty --></div>
	
<script type='text/javascript'>
	jQuery(document).ready(function() {
 		jQuery('#ca_museums_entity_id_lookup').autocomplete( 
			{ 
				minLength: 3, delay: 800,
				source: '<?php print caNavUrl($this->request, 'lookup', 'Entity', 'Get', array()); ?>',	
				select: function(event,ui) {
					if (parseInt(ui.item.id) >= 0) {
						jQuery('#ca_museums_entity_id_value').val(parseInt(ui.item.id));
					}
				}
			}
		);
	});
	
	function caClearMuseumEntityID() {
		jQuery('#ca_museums_entity_id_lookup').val('');
		jQuery('#ca_museums_entity_id_value').val(0);
	}	
 </script>
