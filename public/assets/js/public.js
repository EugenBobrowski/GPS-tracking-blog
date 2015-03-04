(function ( $ ) {
	//"use strict";

	$(function () {
        $(document).ready(function(){
            $('.gmap3').each(function(){
                var $map = $(this);
                var track = $map.data('track');
                if (track == undefined ||
                    track === "" ||
                    track === 0   ||
                    track === "0" ||
                    track === null  ||
                    track === false  ||
                    ( ( track instanceof Array ) && track.length === 0 ) ) {
                    $map.gmap3();
                } else {

                    var lineSymbol = {
                        path: 'M 0,-1 0,1',
                        strokeOpacity: 1,
                        scale: 4
                    };

                    $map.gmap3({
                        map:{
                            options:{
                                mapTypeControl: true,
                                mapTypeControlOptions: {
                                    style: google.maps.MapTypeControlStyle.DROPDOWN_MENU
                                },
                                navigationControl: false,
                                scrollwheel: false,
                                streetViewControl: true
                            }
                        },
                        polyline:{
                            options:{
                                strokeColor: "#FF5522",
//                                strokeOpacity: 1.0,
                                strokeWeight: 2,
                                strokeOpacity: 0.5,
                                icons: [{
                                    icon: lineSymbol,
                                    offset: '0',
                                    repeat: '20px'
                                }],
                                path: track.polyline
                            }
                        },
                        autofit:{}
                    });
                    var dataPointss = [];
                    var tmp_obj = track.trackFull;
                    for(var i in tmp_obj) {
                        if (!tmp_obj.hasOwnProperty(i)) continue;
//                        console.log(new Date(tmp_obj[i][0]));
                        dataPointss.push({x: new Date(tmp_obj[i][0]), y: tmp_obj[i].distance});
                    }
                    console.log(dataPointss);
                    $map.after('<div id="chartContainer" style="height: 300px; width: 100%;">');
                    var chart = new CanvasJS.Chart("chartContainer",
                        {
                            axisX:{
                                interval: 3,
                                labelAngle : 60,
                                valueFormatString: "HH:mm",
                                gridThickness: 1,
                                tickThickness: 1
                            },
                            axisY:{
                                gridThickness: 1,
                                tickThickness: 1
                            },
                            data: [
                                {
                                    type: "area",
//                                    dataPoints: [dataPointss]
                                    dataPoints: dataPointss
                                }
                            ]
                        });

                    chart.render();


                }

            });

            $('#gpsTrackFile').change(function(evt){


                var files = evt.target.files; // FileList object
                // Loop through the FileList and render image files as thumbnails.
                for (var i = 0, f; f = files[i]; i++) {
                    var fileType = f.type;
                    var fileName = f.name;
                    var fileSize = f.size;

                    // Only process image files.
                    if (!(fileType.match('text.*') || (fileType == ''))) {
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
                                'chickenhut': $('#gpsChickenhut').val(),
                                'trackfilemimetype': fileType,
                                'fileName': fileName,
                                'track': e.target.result
                            };

                            // since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
                            jQuery.post(ajax_object.ajax_url, data, function(response) {
                                console.log(response);

                                $('#gpsTrackContent').val(response);

                                var obj = JSON.parse(response);
                                $("#formMap").gmap3('destroy').gmap3({
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
                                    $('#labelFileName').text(fileName);
                                }, 1000);


                            });
                        };
                    })(f);

                    // Read in the image file as a data URL.
                    reader.readAsText(f);
                }
                $('#submitTrackForm').submit(function(){
                    var track_json = $('#gpsTrackContent').val();
                    var track_obj = JSON.parse(track_json);
                    var data = {
                        'action': 'gps_filerende_ajax',
                        'subaction': 'submit',
                        'chickenhut': $('#gpsChickenhut').val(),
                        'title': $('#gpsTrackTitle').val(),
                        'description': $('#gpsTrackDescription').val(),
                        'track': track_json,
                        'track_data_simple': {
                            'time_full': track_obj.timeFull,
                            'time_start': track_obj.timeStart,
                            'time_stop': track_obj.timeStop,
                            'distance': track_obj.distanceFull,
                            'points': track_obj.points
                        }


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