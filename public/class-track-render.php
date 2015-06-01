<?php
/**
 * Created by PhpStorm.
 * User: eugen
 * Date: 26.05.15
 * Time: 0:54
 */
class TrackRender {
    public $srting;

    public function get_txt () {
        $stringPointsArray = explode(PHP_EOL, $this->srting);
        $trackPoints = array();
        $keys = array();
        foreach ($stringPointsArray as $key=>$value) {
            $trackPoint = explode(",", $value);//time,lat,lon,elevation,accuracy,bearing,speed
            if (!empty($value) && count($trackPoint) > 3) {
                if (empty($keys) && (strpos($value, 'lat') !== false) && (strpos($value, 'lon') !== false)) {
                    foreach ($trackPoint as $keyt=>$title) {
                        if (strpos($title, 'time') !== false) {
                            $keys['time'] = $keyt;
                        } elseif (strpos($title, 'lat') !== false) {
                            $keys['lat'] = $keyt;

                        } elseif (strpos($title, 'lon') !== false) {
                            $keys['lon'] = $keyt;

                        } elseif (strpos($title, 'ele') !== false) {
                            $keys['ele'] = $keyt;
                        } elseif (strpos($title, 'accuracy') !== false) {
                            $keys['accuracy'] = $keyt;
                        } elseif (strpos($title, 'bearing') !== false) {
                            $keys['krs'] = $keyt;
                        }
                    }
                } else {
                    $thisPoint = array(
                        'time'  => $trackPoint[$keys['time']],
                        'lat'   => $trackPoint[$keys['lat']],
                        'lon'   => $trackPoint[$keys['lon']],
                        'ele'   => $trackPoint[$keys['ele']],
                    );
                    if (isset($trackPoint[$keys['krs']]))  $thisPoint['krs'] = $trackPoint[$keys['krs']];
                    if (isset($trackPoint[$keys['accuracy']]))  $thisPoint['accuracy'] = $trackPoint[$keys['accuracy']];

                    if ((isset($thisPoint['accuracy']) && $trackPoint[$keys['accuracy']] < 1000 ) || !isset($thisPoint['accuracy'])) {
                        $trackPoints[] = $thisPoint;
                    }
                }
            }
        }
        return $trackPoints;
    }
    public function txt () {
//        $this->get_txt();
        return $this->get_track_json($this->get_txt());
    }
    /**
     * Return ['trackFull'] element
     */
    public function get_gpx () {
        $track = new SimpleXMLElement(str_replace('\"', '"', $this->srting));
        $trackPoints = array();
        $i = 0;
        foreach ($track->trk->trkseg->trkpt as $point) {
            if (!isset($point->ele)) continue;
            $key = $i++;

            $trackPoints[$key] = array (
                'time'  => ''.$point->time,
                'lat'   => ''.$point['lat'],
                'lon'   => ''.$point['lon'],
                'ele'   => ''.$point->ele,
            );

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
        $upHill = 0;
        $downHill = 0;

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

                    if ($deltaElevation < 0 ) {
                        $downHill += ($deltaElevation);
                    } elseif ($deltaElevation > 0) {
                        $upHill += $deltaElevation;
                    }
                } else {
                    $deltaElevation = 0;
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
        $output = array();
        $output['polyline'] = json_decode($polyline);

        $output['trackFull'] = $trackPoints;
        $output['points'] = count($trackPoints);
        $stopPoint = array_pop($trackPoints);
        $output['upHill'] = $upHill;
        $output['downHill'] = -$downHill;
        $output['timeStart'] = $timeStart;
        $output['timeStop'] = strtotime($stopPoint['time']);
        $output['timeFull'] = $output['timeStop'] - $output['timeStart'];
        $output['distanceFull'] = $stopPoint['distanceFull'];
        return json_encode($output);
    }
    public function get_distance() {

    }

}