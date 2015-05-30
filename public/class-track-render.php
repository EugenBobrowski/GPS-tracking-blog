<?php
/**
 * Created by PhpStorm.
 * User: eugen
 * Date: 26.05.15
 * Time: 0:54
 */
class TrackRender {
    public $srting;
    public function xtxt () {
        $stringPointsArray = explode(PHP_EOL, $this->srting);
        $response = array();
        $keys = array();
        foreach ($stringPointsArray as $key=>$value) {
            if (!empty($value)) {
                if (empty($keys) && (strpos($value, 'lat') !== false) && (strpos($value, 'lon') !== false)) {

                }
            }
            $trackPath[$key] = explode(",", $value); //time,lat,lon,elevation,accuracy,bearing,speed


        }
        return $response;
    }
    public function txt () {
        $trackPath = explode(PHP_EOL, $this->srting);
        $polyline = '';
        $i = 0;
        $timeStart = 0;
        $prevLat = null;
        $prevLon = null;
        $earthCircle = 6371000 * M_PI * 2;
        $unitLat = $earthCircle / 360;
        foreach ($trackPath as $key=>$value) {
            $trackPath[$key] = explode(",", $value);
            if ($key != 0 && !empty($trackPath[$key][0])) {
                if ($i != 0) {
                    $polyline .= ', ';
                } elseif ($i == 0) {
                    $timeStart = strtotime($trackPath[$key][0]);
                } else {

                }
                $i ++;
                if (!empty($trackPath[$key-1])) {

                    $elevCorrection = $trackPath[$key][3] * M_PI * 2 / 360;


                    $unitLon = cos(M_PI/180*$trackPath[$key][1]) * ($unitLat + $elevCorrection);
                    $deltaLat = $trackPath[$key][1] - $trackPath[$key-1][1];
                    $deltaLon = $trackPath[$key][2] - $trackPath[$key-1][2];
                    $deltaElevation = $trackPath[$key][3] - $trackPath[$key-1][3];
                    $sLat = ($unitLat + $elevCorrection) * $deltaLat;
                    $sLon = $unitLon * $deltaLon;
                    $S = sqrt(pow($sLat, 2) + pow($sLon, 2) + pow($deltaElevation, 2));


                    $trackPath[$key]['distance'] = $S;
                    $trackPath[$key]['distanceFull'] = $S + $trackPath[$key - 1]['distanceFull'];
                    $trackPath[$key]['speed'] = ($S * 3600) / (1000 * (strtotime($trackPath[$key][0]) - strtotime($trackPath[$key - 1][0])));
                    $trackPath[$key]['speed_ms'] = ($S ) / ((strtotime($trackPath[$key][0]) - strtotime($trackPath[$key - 1][0])));
                    /**
                     * Все что медленнее одного км/час стоит
                     * Все что до 6 км час идет
                     */

                } else {
                    $trackPath[$key]['distance'] = 0;
                    $trackPath[$key]['distanceFull'] = 0;
                    $trackPath[$key]['speed'] = 0;
                }
                $polyline .= ' [ '
                    .$trackPath[$key][1].', ' //lat
                    .$trackPath[$key][2].', ' //lon
                    .$trackPath[$key][3] //elevation
                    .' ] ';

            } else {
                unset ($trackPath[$key]);
            }
        }
        $polyline = '['.$polyline.']';
        $output = array();
        $output['polyline'] = json_decode($polyline);

        $output['trackFull'] = $trackPath;
        $output['points'] = count($trackPath);
        $stopPoint = array_pop($trackPath);
        $output['timeStart'] = $timeStart;
        $output['timeStop'] = strtotime($stopPoint[0]);
        $output['timeFull'] = $output['timeStop'] - $output['timeStart'];
        $output['distanceFull'] = $stopPoint['distanceFull'];
        return json_encode($output);
    }

    /**
     * Return ['trackFull'] element
     */
    public function get_gpx () {
        $track = new SimpleXMLElement(str_replace('\"', '"', $this->srting));
        $trackPoints = array();
        $i = 0;
        foreach ($track->trk->trkseg->trkpt as $point) {
            $key = $i++;
            $trackPoints[$key] = array (
                'time'  => ''.$point->time,
                'lat'   => ''.$point['lat'],
                'lon'   => ''.$point['lon'],
            );

            if (isset($point->ele)) $trackPoints[$key]['ele'] = ''.$point->ele;
            if (isset($point->course)) $trackPoints[$key]['krs'] = ''.$point->course;
            if (isset($point->src)) $trackPoints[$key]['src'] = ''.$point->src;
            if (isset($point->sat)) $trackPoints[$key]['sat'] = ''.$point->sat;
            if (isset($point->hdop)) $trackPoints[$key]['hdop'] = ''.$point->hdop;
            if (isset($point->vdop)) $trackPoints[$key]['vdop'] = ''.$point->vdop;
            if (isset($point->pdop)) $trackPoints[$key]['pdop'] = ''.$point->pdop;
            if (isset($point->geoidheight)) $trackPoints[$key]['geoidheight'] = ''.$point->geoidheight;

        }
//        var_dump($trackPoints);
        return $trackPoints;

    }
    public function gpx () {
        return $this->get_track_json($this->get_gpx());
    }
    public function get_track_json ($trackPoints) {
        $polyline = '';
        $i = 0;
        $timeStart = 0;
        $prevLat = null;
        $prevLon = null;

        $earthCircle = 6371000 * M_PI * 2;
        $unitLat = $earthCircle / 360;

        foreach ($trackPoints as $key=>$point) {
            if ($i != 0) {
                $polyline .= ', ';
            } elseif ($i == 0) {
                $timeStart = strtotime($point['time']);
            } else {

            }

            $i ++;

            if (!empty($trackPoints[$key-1])) {
                if (isset($trackPoints[$key]['ele']) && isset($trackPoints[$key-1]['ele'])) {
                    $elevCorrection = $trackPoints[$key]['ele'] * M_PI * 2 / 360;
                } else { $elevCorrection = 0; }


                $unitLon = cos(M_PI/180*$trackPoints[$key]['lat']) * ($unitLat + $elevCorrection);
                $deltaLat = $trackPoints[$key]['lat'] - $trackPoints[$key-1]['lat'];
                $deltaLon = $trackPoints[$key]['lon'] - $trackPoints[$key-1]['lon'];
                if (isset($trackPoints[$key]['ele']) && isset($trackPoints[$key-1]['ele'])) {
                    $deltaElevation = $trackPoints[$key]['ele'] - $trackPoints[$key-1]['ele'];
                }

                $sLat = ($unitLat + $elevCorrection) * $deltaLat;
                $sLon = $unitLon * $deltaLon;
                $S = sqrt(pow($sLat, 2) + pow($sLon, 2) + pow($deltaElevation, 2));


                $trackPoints[$key]['distance'] = $S;
                $trackPoints[$key]['distanceFull'] = $S + $trackPoints[$key - 1]['distanceFull'];
                $trackPoints[$key]['speed'] = ($S * 3600) / (1000 * (strtotime($trackPoints[$key]['time']) - strtotime($trackPoints[$key - 1]['time'])));
                $trackPoints[$key]['speed_ms'] = ($S ) / ((strtotime($trackPoints[$key]['time']) - strtotime($trackPoints[$key - 1]['time'])));
                /**
                 * Все что медленнее одного км/час стоит
                 * Все что до 6 км час идет
                 */

            } else {
                $trackPoints[$key]['distance'] = 0;
                $trackPoints[$key]['distanceFull'] = 0;
                $trackPoints[$key]['speed'] = 0;
            }
            $polyline .= ' [ '
                .$trackPoints[$key]['lat'].', ' //lat
                .$trackPoints[$key]['lon'] //lon
//                .', '.$trackPoints[$key]['ele'] //elevation
                .' ] ';
        }

        $polyline = '['.$polyline.']';
//        var_dump(json_decode($polyline));
        $output = array();
        $output['polyline'] = json_decode($polyline);

        $output['trackFull'] = $trackPoints;
        $output['points'] = count($trackPoints);
        $stopPoint = array_pop($trackPoints);
        $output['timeStart'] = $timeStart;
        $output['timeStop'] = strtotime($stopPoint['time']);
        $output['timeFull'] = $output['timeStop'] - $output['timeStart'];
        $output['distanceFull'] = $stopPoint['distanceFull'];
        return json_encode($output);
    }
    public function get_distance() {

    }

}