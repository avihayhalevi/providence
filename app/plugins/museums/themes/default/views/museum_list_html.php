<?php
/* ----------------------------------------------------------------------
 * app/views/admin/access/museum_list_html.php :
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
	$va_museum_list = $this->getVar('museum_list');

?>
<script language="JavaScript" type="text/javascript">
/* <![CDATA[ */
	$(document).ready(function(){
		$('#caItemList').caFormatListTable();
	});
/* ]]> */
</script>
<div class="sectionBox">
<?php 
		print caFormTag($this->request, 'ListMuseums', 'caMuseumListForm', null, 'post', 'multipart/form-data', '_top', array('disableUnsavedChangesWarning' => true));
		print caFormControlBox(
			'<div class="list-filter">'._t('Filter').': <input type="text" name="filter" value="" onkeyup="$(\'#caItemList\').caFilterTable(this.value); return false;" size="20"/></div>', 
			'', 
			caNavHeaderButton($this->request, __CA_NAV_ICON_ADD__, _t("New museum"), 'museums', 'manage', 'Edit', array('museum_id' => 0), [], ['size' => '30px'])
		); 
?>		
		<h1 style='float:left; margin:10px 0px 10px 0px;'><?php print _t('%1 museums', ucfirst($this->getVar('museumclass_displayname'))); ?></h1>
<?php
	if(sizeof($va_museum_list)){	
?>	
		<a href='#' id='showTools' style="float:left;margin-top:10px;" onclick='jQuery("#searchToolsBox").slideDown(250); jQuery("#showTools").hide(); return false;'><?php print caNavIcon(__CA_NAV_ICON_SETTINGS__, "24px");?></a>
<?php
		print $this->render('museum_tools_html.php');
	}
?>
		<table id="caItemList" class="listtable" width="100%" border="0" cellpadding="0" cellspacing="1">
			<thead>
				<tr>
					<th class="list-header-unsorted">
						<?php print _t('Login name'); ?>
					</th>
					<th class="list-header-unsorted">
						<?php print _t('Name'); ?>
					</th>
					<th class="list-header-unsorted">
						<?php print _t('Email'); ?>
					</th>
					<th class="list-header-unsorted">
						<?php print _t('Active?'); ?>
					</th>
					<th class="{sorter: false} list-header-nosort listtableEditDelete"></th>
				</tr>
			</thead>
			<tbody>
<?php
	
	foreach($va_museum_list as $va_museum) {

?>
			<tr>
				<td>
					<?php print $va_museum['museum_name']; ?>
				</td>
				<td>
					<?php print $va_museum['lname'].', '.$va_museum['fname']; ?>
				</td>
				<td>
					<?php print $va_museum['email']; ?>
				</td>
				<td>
					<?php print $va_museum['active'] ? _t('Yes') : _t('No'); ?>
				</td>
				<td class="listtableEditDelete">
					<?php print caNavButton($this->request, __CA_NAV_ICON_EDIT__, _t("Edit"), '', 'museums', 'manage', 'Edit', array('museum_id' => $va_museum['museum_id']), array(), array('icon_position' => __CA_NAV_ICON_ICON_POS_LEFT__, 'use_class' => 'list-button', 'no_background' => true, 'dont_show_content' => true)); ?>
					<?php print caNavButton($this->request, __CA_NAV_ICON_DELETE__, _t("Delete"), '', 'museums', 'manage', 'Delete', array('museum_id' => $va_museum['museum_id']), array(), array('icon_position' => __CA_NAV_ICON_ICON_POS_LEFT__, 'use_class' => 'list-button', 'no_background' => true, 'dont_show_content' => true)); ?>
				</td>
			</tr>
<?php
		TooltipManager::add('.deleteIcon', _t("Delete"));
		TooltipManager::add('.editIcon', _t("Edit"));
		TooltipManager::add('#showTools', _t("Tools"));
	}
?>
			</tbody>
		</table>
	</form>
</div>
	<div class="editorBottomPadding"><!-- empty --></div>
