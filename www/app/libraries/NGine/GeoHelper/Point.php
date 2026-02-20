<?php


namespace NGine\GeoHelper;

/**
 * Permet de stocker et de manipuler un point géographique
 * @package GeoHelper
 */
class Point
{
    /**
     * Latitude du point
     *
     * @var float
     */
    private $latitude;
    /**
     * Longitude du point
     *
     * @var float
     */
    private $longitude;

    /**
     * Point constructor.
     *
     * @param float $latitude
     * @param float $longitude
     */
    public function __construct(float $latitude, float $longitude)
    {
        $this->setLatitude($latitude);
        $this->setLongitude($longitude);
    }

    /**
     * Retourne la latitude du point
     *
     * @return float
     */
    public function getLatitude(): float
    {
        return $this->latitude;
    }

    /**
     * Retourne la longitude du point
     *
     * @param float $latitude
     *
     * @return Point
     */
    public function setLatitude(float $latitude): Point
    {
        $this->latitude = $latitude;

        return $this;
    }

    /**
     * Retourne la longitude du point
     *
     * @return float
     */
    public function getLongitude(): float
    {
        return $this->longitude;
    }

    /**
     * Retourne la longitude du point
     *
     * @param float $longitude
     *
     * @return Point
     */
    public function setLongitude(float $longitude): Point
    {
        $this->longitude = $longitude;

        return $this;
    }

}