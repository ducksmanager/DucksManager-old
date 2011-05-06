<html>
    <head>
        <script type="text/javascript">
            window.onload=function() {
                document.getElementById('pays').value=window.parent.pays;
            }
        </script>
    </head>
    <body>
<form method="POST" action="upload.php" enctype="multipart/form-data">
     <input type="hidden" name="MAX_FILE_SIZE" value="400000" />
     <input type="hidden" id="pays" name="pays" value="" />
     <input type="file" name="image" /><br />
     <input type="submit" value="Go" />
</form>
    </body>
    </html>