var imageBeingRotated = false;  // The DOM image currently being rotated (if any)
var mouseStartAngle = false;    // The angle of the mouse relative to the image centre at the start of the rotation
var imageStartAngle = false;    // The rotation angle of the image at the start of the rotation


// Start rotating an image

function startRotate( e ) {

  // Track the image that we're going to rotate
  imageBeingRotated = this;

  // Store the angle of the mouse at the start of the rotation, relative to the image centre
  var imageCentre = getImageCentre( imageBeingRotated );
  var mouseStartXFromCentre = e.pageX - imageCentre[0];
  var mouseStartYFromCentre = e.pageY - imageCentre[1];
  mouseStartAngle = Math.atan2( mouseStartYFromCentre, mouseStartXFromCentre );

  // Store the current rotation angle of the image at the start of the rotation
  imageStartAngle = $(imageBeingRotated).data('currentRotation');

  // Set up an event handler to rotate the image as the mouse is moved
  $(document).mousemove( rotateImage );

  return false;
}

// Stop rotating an image

function stopRotate( e ) {

  // Exit if we're not rotating an image
  if ( !imageBeingRotated ) return;

  // Remove the event handler that tracked mouse movements during the rotation
  $(document).unbind( 'mousemove' );

  setTimeout( function() { imageBeingRotated = false; }, 10 );
  return false;
}

// Rotate image based on the current mouse position

function rotateImage( e ) {

  if ( !imageBeingRotated ) return;

  // Calculate the new mouse angle relative to the image centre
  var imageCentre = getImageCentre( imageBeingRotated );
  var mouseXFromCentre = e.pageX - imageCentre[0];
  var mouseYFromCentre = e.pageY - imageCentre[1];
  var mouseAngle = Math.atan2( mouseYFromCentre, mouseXFromCentre );

  // Calculate the new rotation angle for the image
  var rotateAngle = mouseAngle - mouseStartAngle + imageStartAngle;

  var degValue=parseFloat(rotateAngle * 180 / Math.PI);
  rotateImageDegValue(imageBeingRotated, degValue);
  
  return false;
}

function rotateImageRadValue(image, rotateAngle) {
	  // Rotate the image to the new angle, and store the new angle
	  $(image).css('transform','rotate(' + rotateAngle + 'rad)');
	  $(image).css('-moz-transform','rotate(' + rotateAngle + 'rad)');
	  $(image).css('-webkit-transform','rotate(' + rotateAngle + 'rad)');
	  $(image).css('-o-transform','rotate(' + rotateAngle + 'rad)');
	  $(image).data('currentRotation', rotateAngle );
}

function rotateImageDegValue(image, degValue) {
	  var radValue=parseFloat(degValue * Math.PI / 180);
	  rotateImageRadValue(image, radValue);
	  
	  $(image).val($(image).val().replace(/\-?[0-9]+\.?[0-9]*/g,
			  							  toFloat2Decimals(degValue)));
	  tester_option_preview("TexteMyFonts","Rotation");
}

function radToDeg(rad) {
	return toFloat2Decimals(parseFloat(rad * 180 / Math.PI));
}

// Calculate the centre point of a given image

function getImageCentre( image ) {

  // Rotate the image to 0 radians
  $(image).css('transform','rotate(0rad)');
  $(image).css('-moz-transform','rotate(0rad)');
  $(image).css('-webkit-transform','rotate(0rad)');
  $(image).css('-o-transform','rotate(0rad)');

  // Measure the image centre
  var imageOffset = $(image).offset();
  var imageCentreX = imageOffset.left + $(image).width() / 2;
  var imageCentreY = imageOffset.top + $(image).height() / 2;

  // Rotate the image back to its previous angle
  var currentRotation = $(image).data('currentRotation');
  $(imageBeingRotated).css('transform','rotate(' + currentRotation + 'rad)');
  $(imageBeingRotated).css('-moz-transform','rotate(' + currentRotation + 'rad)');
  $(imageBeingRotated).css('-webkit-transform','rotate(' + currentRotation + 'rad)');
  $(imageBeingRotated).css('-o-transform','rotate(' + currentRotation + 'rad)');

  // Return the calculated centre coordinates
  return Array( imageCentreX, imageCentreY );
}