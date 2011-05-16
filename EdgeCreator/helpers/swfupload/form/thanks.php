<?php
// Check for a degraded file upload, this means SWFUpload did not load and the user used the standard HTML upload
$used_degraded = false;
$resume_id = "";
if (isset($_FILES["resume_degraded"]) && is_uploaded_file($_FILES["resume_degraded"]["tmp_name"]) && $_FILES["resume_degraded"]["error"] == 0) {
	$resume_id = $_FILES["resume_degraded"]["name"];
	$used_degraded = true;
}

// Check for the file id we should have gotten from SWFUpload
if (isset($_POST["hidFileID"]) && $_POST["hidFileID"] != "") {
	$resume_id = $_POST["hidFileID"];
}


include_once('upload.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml" >
	<head>
		<title>SWFUpload Demos - Classic Form Demo</title>
		<link href="../css/default.css" rel="stylesheet" type="text/css" />
	</head>
	<body>

		<div id="content">
<?php if ($resume_id == "") { ?>
				<p>Your resume was not received.</p>
			<?php } else {
 ?>
			<table>
				<tr>
					<td>Resume ID: </td>
					<td><?php echo htmlspecialchars($resume_id); ?> </td>
				</tr>
			</table>
<?php if ($used_degraded) { ?>
				<p>You used the standard HTML form.</p>
<?php } ?>
			<hr width="90%" />
			<p> Thank you for your submission. </p>
<?php } ?>
			<p><a href="index.php">Submit another Application</a></p>
			<p> Thanks for trying this demo.  Your files are discarded for the purposes of this demo. </p>
		</div>
	</body>
</html>
