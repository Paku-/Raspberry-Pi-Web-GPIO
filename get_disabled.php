<?php

require_once('mysqli.php');

// Set up calling params.

/*
 print '<pre>';
print_r($_GET);
if($_GET["id"] === "") echo "id is an empty string\n";
if($_GET["id"] === false) echo "id is false\n";
if($_GET["id"] === null) echo "id is null\n";
if(isset($_GET["id"])) echo "id is set\n";
if(!empty($_GET["id"])) echo "id is not empty\n";
print '</pre>';
*/

$sort 	= isset($_GET['sort']) 	&& ($_GET['sort']!= 'undefined') 	? $_GET['sort'] 	: "pinNumberBCM+0";
$id 	= isset($_GET['id'])  	&& ($_GET['id']!= 'undefined') 		? $_GET['id'] 		: 0;
$field 	= isset($_GET['field']) && ($_GET['field']!= 'undefined')  	? $_GET['field'] 	: 'none';

// Set up state "icons".
$on =  '[X]';
$off = '[_]';
$unknown = '[?]';

// Escape params.
$sort = $mysqli->real_escape_string($sort);
$id = $mysqli->real_escape_string($id);
$field = $mysqli->real_escape_string($field);

// Update state and enabled fields as needed.
$query_update = "";
if ($id>0) {
	$query_update = "UPDATE pinRevision" . $pi_rev . " SET " . $field . "= NOT " . $field . " WHERE pinID =" . $id . ";";
	$qry_result = $mysqli->query($query_update);
	if (!$qry_result) {
		$message  = '<pre>Invalid query: ' . $mysqli->error . "</pre>";
		$message .= '<pre>Whole query: ' . $query_update . "</pre>";
		die($message);
	}
}

// Select rows.
$query = "SELECT * FROM pinRevision$pi_rev WHERE pinID > 0 AND pinEnabled='0'";
$query .= "ORDER BY ".$sort." ASC";
$qry_result = $mysqli->query($query);

if (!$qry_result) {
	$message  = '<pre>Invalid query: ' . $mysqli->error . "</pre>";
	$message .= '<pre>Whole query: ' . $query . "</pre>";
	die($message);
}

// Refresh using current sort order.
print "<a href=\"#\" onclick=\"showDisabledPins('" . urlencode($sort) . "')\">Refresh</a>";

// Build Result String.
// Important %2B0 is url encoded "+0" string passed to mySQL to force numerical varchars to be sorted as true numbers.
$display_string = "<table>";
$display_string .= "<tr>";
if ($debugMode) {
	$display_string .= "<th><a href=\"#\" onclick=\"showDisabledPins('pinID%2B0',0,'none')\">pinID</a></th>";
	$display_string .= "<th><a href=\"#\" onclick=\"showDisabledPins('pinDirection',0,'none')\">Direction</a></th>";
}
$display_string .= "<th><a href=\"#\" onclick=\"showDisabledPins('pinNumberBCM%2B0',0,'none')\">BCM#</a></th>";
$display_string .= "<th><a href=\"#\" onclick=\"showDisabledPins('pinNumberWPi%2B0',0,'none')\">WPi#</a></th>";
$display_string .= "<th><a href=\"#\" onclick=\"showDisabledPins('pinDescription',0,'none')\">Description</a></th>";

$display_string .= "<th><a href=\"#\" onclick=\"showDisabledPins('pinEnabled%2B0',0,'none')\">Enabled</a></th>";
$display_string .= "</tr>";

// Insert a new row in the table for each person returned.
while($row = mysqli_fetch_array($qry_result)){
	$display_string .= "<tr>";

	if ($debugMode) {
		$display_string .= "<td>" . $row['pinID'] . "</td>";
		$display_string .= "<td>" . $row['pinDirection'] . "</td>";
	}

	$display_string .= "<td>" . $row['pinNumberBCM'] . "</td>";
	$display_string .= "<td>" . $row['pinNumberWPi'] . "</td>";
	$display_string .= "<td>" . $row['pinDescription'] . "</td>";

	// Enabled
	$display_string .= "<td><a href=\"#\" onclick=\"showDisabledPins('" . urlencode($sort) . "'," . $row['pinID'] . ",'pinEnabled'" . ")\">";
	switch ($row['pinEnabled']){
		case 1 :
		$display_string .= "$on";
		break;
		case 0 :
		$display_string .= "$off";
		break;
		default:
		$display_string .= "$unknown";
	}
	$display_string .= "</a></td>";
	$display_string .= "</tr>";
}
$display_string .= "</table>";
print $display_string;

if ($debugMode) {
	//debug output
	print '<pre>' . $sort . ' ' . $id . ' ' . $field . '</pre>';
	print '<pre>' . $query . '</pre>';
	print '<pre>' . $query_update . '</pre>';
}

?>