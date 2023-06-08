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
				var_dump($itemtable);
				$query	=	"SELECT *
							FROM `$itemtable`
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
							$output = (string)$data["name"];
							if (empty($output) || $_SESSION["glpiis_ids_visible"]) {
								$output = sprintf(__('%1$s (%2$s)'), $output, $data['id']);
							}

							if ($itemtype != 'Software') {
								if (!empty($data['serial'])) {
									$output = sprintf(__('%1$s - %2$s'), $output, $data['serial']);
								}
								if (!empty($data['otherserial'])) {
									$output = sprintf(__('%1$s - %2$s'), $output, $data['otherserial']);
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
		global $DB, $CFG_GLPI;
		var_dump($CFG_GLPI["linkuser_types"]);
		$data = $this->getAssets();
		echo "
			<table class='myassets central'>
		";
		foreach ($data as &$type) {
			echo "
				<tr class='noHover'>
					<td>
						<h3>
			";
						//echo __("Your");
						echo " ".array_shift($type)."</h3>
						<table class='tab_cadrehov'>
							<tr class='noHover'>";
							echo "<th>";
							echo __("Name");
							echo "</th>";

							echo "<th>";
							echo __("Serial number");
							echo "</th>";

							echo "<th>";
							echo __("Inventory number");
							echo "</th>";

			echo			"</tr>";
			foreach ($type as &$asset) {
				echo "
						<tr>
							<td width='200'>".$asset["name"]."</td>
							<td width='200' style='text-align:center;'>".$asset["serial"]."</td>
							<td width='200' style='text-align:center;'>".$asset["otherserial"]."</td>
						</tr>";
			}
			echo "
						</table>
					</td>
				</tr>
			";
		}
		echo "
			</table>
		</table>
		";
	}
}
?>