<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
	<head>
		<title>SWFUpload Demos - Classic Form Demo</title>
		<link href="../css/default.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="../swfupload.js"></script>
		<script type="text/javascript" src="../../mediaplayer/swfobject.js"></script>
		<script type="text/javascript" src="js/fileprogress.js"></script>
		<script type="text/javascript" src="js/handlers.js"></script>
		<script type="text/javascript">
			var swfu;

			window.onload = function () {
				swfu = new SWFUpload({
					// Backend settings
					upload_url: "upload.php",
					file_post_name: "resume_file",

					// Flash file settings
					file_size_limit : "400 KB",
					file_types : "*.png",			// or you could use something like: "*.doc;*.wpd;*.pdf",
					file_types_description : "All Files",
					file_upload_limit : "0",
					file_queue_limit : "1",

					// Event handler settings
					swfupload_loaded_handler : swfUploadLoaded,
				
					file_dialog_start_handler: fileDialogStart,
					file_queued_handler : fileQueued,
					file_queue_error_handler : fileQueueError,
					file_dialog_complete_handler : fileDialogComplete,
				
					//upload_start_handler : uploadStart,	// I could do some client/JavaScript validation here, but I don't need to.
					upload_progress_handler : uploadProgress,
					upload_error_handler : uploadError,
					upload_success_handler : uploadSuccess,
					upload_complete_handler : uploadComplete,

					// Button Settings
					button_image_url : "XPButtonUploadText_61x22.png",
					button_placeholder_id : "spanButtonPlaceholder",
					button_width: 61,
					button_height: 22,
				
					// Flash Settings
					flash_url : "../Flash/swfupload.swf",

					custom_settings : {
						progress_target : "fsUploadProgress",
						upload_successful : false
					},
				
					// Debug settings
					debug: false
				});

			};
		</script>
	</head>
	<body>

		<div id="content">
			<form id="form1" action="thanks.php" enctype="multipart/form-data" method="post">
				<div class="fieldset">
					<table style="vertical-align:top;">
						<tr>
							<td><label for="txtFileName">Image:</label></td>
							<td>
								<div>
									<div>
										<input type="text" id="txtFileName" disabled="true" style="border: solid 1px; background-color: #FFFFFF;" />
										<span id="spanButtonPlaceholder"></span>
								(400 KB max)
									</div>
									<div class="flash" id="fsUploadProgress">
										<!-- This is where the file progress gets shown.  SWFUpload doesn't update the UI directly.
											The Handlers (in handlers.js) process the upload events and make the UI updates -->
									</div>
									<input type="hidden" name="hidFileID" id="hidFileID" value="" />
									<!-- This is where the file ID is stored after SWFUpload uploads the file and gets the ID back from upload.php -->
								</div>
							</td>
						</tr>
					</table>
					<br />
					<input type="submit" value="Submit Application" id="btnSubmit" />
				</div>
			</form>
		</div>
	</body>
</html>
