<?php

namespace NGine\GeoHelper;


/**
 * Calcule une distance entre 2 points GPS.
 *
 * @package GeoHelper
 */
class GeoHelper
{
    public const EARTH_RADIUS = 6371000;
    
    /**
     * Returns distance in meters between two Points according to GPX coordinates.
     *
     * @param Point $point1
     * @param Point $point2
     *
     * @return float|int
     * @see Point
     */
    public function getDistance(Point $point1, Point $point2): float|int
    {
        $latFrom = deg2rad($point1->getLatitude());
        $lonFrom = deg2rad($point1->getLongitude());
        $latTo   = deg2rad($point2->getLatitude());
        $lonTo   = deg2rad($point2->getLongitude());
        
        $lonDelta = $lonTo - $lonFrom;
        $a        = ((cos($latTo) * sin($lonDelta)) ** 2) + ((cos($latFrom) * sin($latTo) - sin($latFrom) * cos($latTo) * cos($lonDelta)) ** 2);
        $b        = sin($latFrom) * sin($latTo) + cos($latFrom) * cos($latTo) * cos($lonDelta);
        $angle    = atan2(sqrt($a), $b);
        
        return $angle * self::EARTH_RADIUS;
    }
    
    
    /**
     * @param string $address
     * @param string $apiKey
     * @return object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getCoordinates(string $address, string $apiKey): object
    {
        
        if (!class_exists(\GuzzleHttp\Client::class)) {
            throw new \RuntimeException(__METHOD__ . " can't work without " . \GuzzleHttp\Client::class . " class");
        }
        $address  = urlencode($address);
        $apiKey   = $apiKey ?? "AIzaSyDz2eDmjSuImpITrAb-qbOO5NL6Y0BnKYk";
        $url      = "https://maps.google.com/maps/api/geocode/json?key={$apiKey}&address={$address}&sensor=false";
        $client   = new \GuzzleHttp\Client(["verify" => ENV === "PROD"]);
        $response = $client->get($url);
        $status   = $response->getStatusCode();
        if ($status === 200) {
            $data = json_decode($response->getBody());
            if (!isset($data->results[0])) {
                return (object)["error" => "Adresse non trouvé sur Google"];
            }
            $lat  = $data->results[0]->geometry->location->lat;
            $long = $data->results[0]->geometry->location->lng;
            \Logger::debug("getCoordinates : $url > " . var_export($data->results[0]->geometry->location, true));
            
            return (object)["latitude" => $lat, "longitude" => $long, "error" => null];
        }
        
        return (object)["error" => "status : $status"];
        
    }
    
    
    public function getClusters($distance_matrix, $point_ids): array
    {
        
        // Setup DBSCAN with distance matrix and unique point IDs
        $DBSCAN    = new DbScan($distance_matrix, $point_ids);
        $epsilon   = 30;
        $minpoints = 3;
        $zones     = [];
        // Perform DBSCAN clustering
        $clusters = $DBSCAN->dbscan($epsilon, $minpoints);
        
        foreach ($clusters as $index => $cluster) {
            if (count($cluster) > 0) {
                $DBSCAN->set_points($cluster);
                $zones[$index] = ["points" => $cluster, "sub" => $DBSCAN->dbscan(21, 2)];
            }
        }
        
        return $zones;
    }
    
    
}