(function ( $ ) {
	//"use strict";

	$(function () {
        $(document).ready(function(){
            $('.gmap3').gmap3();

            $('#gpsTrackFile').change(function(evt){


                var files = evt.target.files; // FileList object
                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, f; f = files[i]; i++) {
                    var fileType = f.type;
                    var fileName = f.name;
                    var fileSize = f.size;

                    // Only process image files.
                    if (!(fileType.match('text.*') || (fileType == ''))) {
                        console.log('fuck da file!', fileType);
                        return ;
                    }


                    var reader = new FileReader();

                    // Closure to capture the file information.
                    reader.onload = (function(theFile) {
                        return function(e) {
                            // Render thumbnail.
                            content = e.target.result;


                            var data = {
                                'action': 'gps_filerende_ajax',
                                'subaction': 'updateMap',
                                'trackfilemimetype': fileType,
                                'fileName': fileName,
                                'track': e.target.result
                            };

                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            jQuery.post(ajax_object.ajax_url, data, function(response) {
//                                console.log(response);

                                $('#gpsTrackContent').val(response)

                                var obj = JSON.parse(response);

                                $("#formMap").gmap3({
                                    map:{
                                        options:{
                                            center:[0, -180],
                                            zoom:2
                                        }
                                    },

                                    polyline:{
                                        options:{
                                            strokeColor: "#FF0000",
                                            strokeOpacity: 1.0,
                                            strokeWeight: 2,
                                            path: obj.polyline
                                        }
                                    },
                                    autofit:{}
                                });
                                setTimeout(function(){
                                    $('#gpsTrackFile').val('');
                                    console.log($('#gpsTrackContent').val());
                                }, 2000);


                            });
                        };
                    })(f);

                    // Read in the image file as a data URL.
                    reader.readAsText(f);
                }
                $('#submitTrackForm').submit(function(){
                    console.log($('#gpsTrackContent').val());
                    var data = {
                        'action': 'gps_filerende_ajax',
                        'subaction': 'submit',
                        'title': $('#gpsTrackTitle').val(),
                        'description': $('#gpsTrackDescription').val(),
                        'track': $('#gpsTrackContent').val()

                    };

                    // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                    jQuery.post(ajax_object.ajax_url, data, function(response) {
                        console.log(response);


                    });

                });

            });

        });


		// Place your public-facing JavaScript here

	});

}(jQuery));