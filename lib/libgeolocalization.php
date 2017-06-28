<?php
require_once "liberror.php";
/**
 * Classe che restituisce delle coordinate bounding box prendendo in input
 * una città e una distanza
 */
class GeoLocalization
{
  //
  private static $MIN_LAT;  // -PI/2
	private static $MAX_LAT;  //  PI/2
	private static $MIN_LON;  // -PI
	private static $MAX_LON;  //  PI

  const EARTHS_RADIUS_KM = 6371.01;

  function __construct()
  {
    self::$MIN_LAT = deg2rad(-90);
    self::$MAX_LAT = deg2rad(90);
    self::$MIN_LON = deg2rad(-180);
    self::$MAX_LON = deg2rad(180);
  }

  private static function checkBounds($radLat, $radLon) {
		if ($radLat < self::$MIN_LAT || $radLat > self::$MAX_LAT ||
        $radLon < self::$MIN_LON || $radLon > self::$MAX_LON)
			throw new Exception("Invalid Argument");
	}

  private static function getGeocodeFromGoogle(string $posizione) {
		$url = 'http://maps.googleapis.com/maps/api/geocode/json?address='.urlencode($posizione).'&sensor=false';
		$ch = curl_init();
	  curl_setopt($ch, CURLOPT_URL,$url);
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	  return json_decode(curl_exec($ch), true);
	}

  private static function getRadLatLon($latitudine, $longitudine){
    $latitudine = deg2rad($latitudine);
    $longitudine = deg2rad($longitudine);

    self::checkBounds($latitudine, $longitudine);
    return array('lat' => $latitudine, 'lng' => $longitudine);
  }

  private function boundingCoordinates(array $coordinate, int $distanza) {
      $radLat = $coordinate['lat'];
      $radLon = $coordinate['lng'];
  		// angular distance in radians on a great circle
  		$angular = $distanza / self::EARTHS_RADIUS_KM;
  		$minLat = $radLat - $angular;
  		$maxLat = $radLat + $angular;
  		$minLon = 0;
  		$maxLon = 0;
  		if ($minLat > self::$MIN_LAT && $maxLat < self::$MAX_LAT) {
  			$deltaLon = asin(sin($angular) / cos($radLat));
  			$minLon = $radLon - $deltaLon;
  			if ($minLon < self::$MIN_LON)
          $minLon += 2 * pi();
  			$maxLon = $radLon + $deltaLon;
  			if ($maxLon > self::$MAX_LON)
          $maxLon -= 2 * pi();
  		} else {
  			// a pole is within the distance
  			$minLat = max($minLat, self::$MIN_LAT);
  			$maxLat = min($maxLat, self::$MAX_LAT);
  			$minLon = self::$MIN_LON;
  			$maxLon = self::$MAX_LON;
  		}
      $minLat = rad2deg($minLat);
      $minLon = rad2deg($minLon);
      $maxLat = rad2deg($maxLat);
      $maxLon = rad2deg($maxLon);
  		return $minLon.','.$minLat.','.$maxLon.','.$maxLat;
  	}

  public function getBbox(string $posizione, int $distanza){
    $dati_json =  self::getGeocodeFromGoogle($posizione);
    if(isset($dati_json["results"][0])){
      $latitudine = $dati_json["results"][0]["geometry"]["location"]["lat"];
      $longitudine = $dati_json["results"][0]["geometry"]["location"]["lng"];
      $coordinate = self::getRadLatLon($latitudine, $longitudine);
      return $this->boundingCoordinates($coordinate, $distanza);
    }
    else {
      throw new ErrorNotListed("Impossibile ottenere dati geolocalizzazione");
    }
  }
}
?>
