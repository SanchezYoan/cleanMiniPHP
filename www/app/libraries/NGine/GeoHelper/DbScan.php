<?php

namespace NGine\GeoHelper;

/**
 * Implémente l'algorithme DBSCAN pour regrouper des points par distance.
 */
class DbScan {
	
	/**
	 * Identifiants de points à analyser.
	 *
	 * @var array<int|string>
	 */
	private $points;	

	/**
	 * Matrice de distances entre points (triangle supérieur accepté).
	 *
	 * @var array<int|string, array<int|string, float|int>>
	 */
	private $distance_matrix; 

	/**
	 * Points considérés comme du bruit.
	 *
	 * @var array<int|string>
	 */
	private $noise_points;  
	/**
	 * Points déjà affectés à un cluster.
	 *
	 * @var array<int|string>
	 */
	private $in_a_cluster;
	/**
	 * Clusters calculés.
	 *
	 * @var array<int, array<int|string>>
	 */
	private $clusters;
	
	/**
	 * Instancier un DBSCAN.
	 *
	 * @param array<int|string, array<int|string, float|int>> $distance_matrix Matrice de distances.
	 * @param array<int|string>                               $points          Identifiants des points.
	 */
	public function __construct($distance_matrix, $points)
	{
		$this->distance_matrix = $distance_matrix;
		$this->points = $points;
		$this->noise_points = array();
		$this->clusters = array();
		$this->in_a_cluster = array();
	}

	/**
	 * Redéfinir le sous-ensemble de points à analyser.
	 *
	 * @param array<int|string> $new_points Nouveaux identifiants.
	 *
	 * @return void Aucune valeur de retour.
	 */
	public function set_points($new_points)
	{
		$this->points = $new_points;
	}
	
	/**
	 * Étendre un cluster existant à partir d'un point.
	 *
	 * @param int|string            $point           Point de départ.
	 * @param array<int|string>     $neighbor_points Voisins déjà identifiés.
	 * @param int                   $c               Index de cluster.
	 * @param float|int             $epsilon         Rayon de voisinage.
	 * @param int                   $min_points      Minimum de points pour densité.
	 *
	 * @return void Aucune valeur de retour.
	 */
	private function expand_cluster($point, $neighbor_points, $c, $epsilon, $min_points)
	{
		$this->clusters[$c][] = $point;
		$this->in_a_cluster[] = $point;
		$neighbor_point = reset($neighbor_points);
		while ($neighbor_point)
		{
			$neighbor_points2 = $this->region_query($neighbor_point, $epsilon);
			if (count($neighbor_points2) >= $min_points)
			{
				foreach ($neighbor_points2 as $neighbor_point2)
				{
					if (!in_array($neighbor_point2, $neighbor_points))
					{
						$neighbor_points[] = $neighbor_point2;
					}
				}
			}
			if (!in_array($neighbor_point, $this->in_a_cluster))
			{
				$this->clusters[$c][] = $neighbor_point;
				$this->in_a_cluster[] = $neighbor_point;
			}

			$neighbor_point = next($neighbor_points);
		}
	}
	
	/**
	 * Récupérer les voisins d'un point dans un rayon donné.
	 *
	 * @param int|string $point   Point de référence.
	 * @param float|int  $epsilon Rayon de voisinage.
	 *
	 * @return array<int|string> Liste des voisins.
	 */
	private function region_query($point, $epsilon)
	{
		$neighbor_points = array();
		
		foreach ($this->points as $point2)
		{
			if ($point != $point2)
			{
				// Because we are using an upper diagonal representation of distances between points
				if (array_key_exists($point2, $this->distance_matrix[$point]))
				{	
					$distance = $this->distance_matrix[$point][$point2];
				} else {
					$distance = $this->distance_matrix[$point2][$point];
				}

				if ($distance < $epsilon)
				{
					$neighbor_points[] = $point2;
				}
			
			}
		}
		return $neighbor_points;
	}
	
	/**
	 * Lancer l'algorithme DBSCAN.
	 *
	 * @param float|int $epsilon    Distance maximale pour être voisin.
	 * @param int       $min_points Nombre minimal de points pour un cluster.
	 *
	 * @return array<int, array<int|string>> Clusters calculés.
	 */
	public function dbscan($epsilon, $min_points)  
	{
		$this->noise_points = array();  // points that do no belong to any cluster
		$this->clusters = array();		// contains an array for each cluster, each cluster array has points ids belonging to that cluster
		$this->in_a_cluster = array();  // points that have been added to a cluster
		
		$c = 0;
		$this->clusters[$c] = array();
		foreach ($this->points as $point_id)
		{
			$neighbor_points = $this->region_query($point_id, $epsilon);

			if (count($neighbor_points) < $min_points)
			{
				$this->noise_points[] = $point_id;
			} elseif (!in_array($point_id, $this->in_a_cluster)) {
				$this->expand_cluster($point_id, $neighbor_points, $c, $epsilon, $min_points);
				$c = $c + 1;
				$this->clusters[$c] = array();
			}
		}
		
		return $this->clusters;
	}

}
