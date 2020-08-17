<?php

/**
* Plugin main class
**/
class PluginMyassetsAssets extends CommonGLPI {
	/**
	* Overwrite the Personal View Function... hopefully
	*/

	public function getAssets() {
		global $DB, $CFG_GLPI;
		$userID	= Session::getLoginUserID();
		$devices	= array();

		// My items
		 $entity_restrict=-1;
		foreach ($CFG_GLPI["linkuser_types"] as $itemtype) {
			if (($item = getItemForItemtype($itemtype))
				&& Ticket::isPossibleToAssignType($itemtype)) {
				$itemtable = getTableForItemType($itemtype);
				$modeltable = substr("$itemtable", 0, -1)."models";
				$model_id = substr($modeltable, 5)."_id";

				$query	=	"SELECT `$itemtable`.*,
							glpi_manufacturers.name as Manufacturer,
							`$modeltable`.name as Model
							FROM `$itemtable`
							RIGHT JOIN glpi_manufacturers
							ON `$itemtable`.manufacturers_id=glpi_manufacturers.id
							RIGHT JOIN `$modeltable`
							ON `$itemtable`.`$model_id`=`$modeltable`.id
							WHERE `users_id` = '$userID'";
				if ($item->maybeDeleted()) {
					$query .= " AND `$itemtable`.`is_deleted` = '0' ";
				}
				if ($item->maybeTemplate()) {
					$query .= " AND `$itemtable`.`is_template` = '0' ";
				}
				if (in_array($itemtype, $CFG_GLPI["helpdesk_visible_types"])) {
					$query .= " AND `is_helpdesk_visible` = '1' ";
				}
				$result	= $DB->query($query);
				$nb		= $DB->numrows($result);
				if ($DB->numrows($result) > 0) {
					$type_name = $item->getTypeName($nb);
					if (!is_array($type_name)) {
						$type_name = array($type_name);
					}
					while ($data = $DB->fetch_assoc($result)) {
						if (!isset($already_add[$itemtype]) || !in_array($data["id"], $already_add[$itemtype])) {
							$output = (string)$data["name1"];
							if (empty($output) || $_SESSION["glpiis_ids_visible"]) {
								$output = sprintf(__('%1$s (%2$s)'), $output, $data['id']);
							}

							if ($itemtype != 'Software') {
								if (!empty($data['serial'])) {
									$output = sprintf(__('%1$s - %2$s'), $output, $data['serial']);
								}
								if (!empty($data['Manufacturer'])) {
									$output = sprintf(__('%1$s - %2$s'), $output, $data['Manufacturer']);
								}
								if (!empty($data['Model'])) {
									$output = sprintf(__('%1$s - %2$s'), $output, $data['Model']);
								}

							}

							array_push($type_name, $data);
						}
					}
					if (!array_key_exists((string)$type_name, $devices)) {
						array_push($devices, $type_name);
					} else {
						$devices[$type_name] = array_merge($devices[$type_name], $type_name[0]);
					}
				}
			}
		}

		return $devices;
	}
	public function showAssets() {
		$data = $this->getAssets();
		//echo "
		//	<table class='myassets central'>
		//";
		foreach ($data as &$type) {
			echo "
				<tr class='noHover'>
					<td>
						<h3 style='text-align:center;'>
			";
						echo __("Your");
						echo " ".array_shift($type)."</h3>
						<table class='tab_cadrehov'>
							<tr class='noHover'>";
							echo "<th style='text-align:center;'>";
							echo __("Name");
							echo "</th>";

							echo "<th style='text-align:center;'>";
							echo __("Serial number");
							echo "</th>";

							echo "<th style='text-align:center;'>";
							echo __("Manufacturer");
							echo "</th>";

							echo "<th style='text-align:center;'>";
							echo __("Model");
							echo "</th>";


			echo			"</tr>";
			foreach ($type as &$asset) {
				echo "
						<tr>
							<td width='190' style='text-align:center;'>".$asset["name"]."</td>
							<td width='190' style='text-align:center;'>".$asset["serial"]."</td>
							<td width='190' style='text-align:center;'>".$asset["Manufacturer"]."</td>
							<td width='190' style='text-align:center;'>".$asset["Model"]."</td>
						</tr>";
			}
			echo "
						</table>
					</td>
				</tr>
			";
		}

	}
}
?>
