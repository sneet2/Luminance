<?php

if ($IDs = $_POST['id']) {
	$Queries = array();
	foreach ($IDs as &$ID) {
		$ID = (int) $ID;


		// Check if conversation belongs to user
		$DB->query("SELECT UserID, AssignedToUser FROM staff_pm_conversations WHERE ID=$ID");
		list($UserID, $AssignedToUser) = $DB->next_record();

                if (isset($_POST['StealthResolve']) && check_perms('admin_stealth_resolve')) {
			$Queries[] = "UPDATE staff_pm_conversations SET StealthResolved=1 WHERE ID=$ID";

                        // Add a log message to the StaffPM
                        $Message = sqltime()." - Stealth Resolved by ".$LoggedUser['Username'];//                        make_staffpm_note($Message, $ID);
                } else if ($UserID == $LoggedUser['ID'] || $DisplayStaff == '1' || $UserID == $AssignedToUser) {
			// Conversation belongs to user or user is staff, queue query
			$Queries[] = "UPDATE staff_pm_conversations SET Status='Resolved', ResolverID=".$LoggedUser['ID']." WHERE ID=$ID";

                        // Add a log message to the StaffPM
                        $Message = sqltime()." - Resolved by ".$LoggedUser['Username'];//                        make_staffpm_note($Message, $ID);
		} else {
			// Trying to run disallowed query
			error(403);
		}
	}

	// Run queries
	foreach ($Queries as $Query) {
		$DB->query($Query);
	}
	// Clear cache for user
	$Cache->delete_value('staff_pm_new_'.$LoggedUser['ID']);

	// Done! Return to inbox
	header("Location: staffpm.php");
} else {
	// No id
	header("Location: staffpm.php");
}
