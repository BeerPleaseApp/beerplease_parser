<?php

/**
 * Parse le DOM d'un site pour récupérer les informations des bières
 */
class ParseBeer
{
	private $url;
	private $count 	  = 1;
	private $filename = 'beers';

	/**
	 * @param string $url
	 * @param int $count
	 */
	public function __construct($url, $count)
	{
		$this->url   = $url;
		$this->count = $count;
	}
	
	/**
	 * Transforme le tableau de bières en fichier JSON
	 * @return string
	 */
	public function parseToJson()
	{
		$result = $this->parse();
		$this->createFile(json_encode($result , JSON_FORCE_OBJECT));
		
		if (file_exists($this->getFilename('json'))) {
			return 'Fichier généré avec succès.';
		}

		return 'Erreur lors de la génération du fichier.';
	}

	/**
	 * Transforme le tableau de bières en fichier CSV
	 * @return string
	 */
	public function parseToCSV()
	{
		// @toto
	}

	/**
	 * Retourne un tableau avec les informations d'une bière
	 * @param  array $data
	 * @return array
	 */
	private function getBeer($data)
	{
		$beer = array();
		foreach ($data as $key => $value) {
			$beer['name']        = $data[0];
			$beer['description'] = $data[1];
			$beer['brewer']      = $data[2];
			$beer['country']     = $data[3];
			$beer['alcohol']     = $data[4];
			$beer['color']       = $data[8];
			$beer['rate']        = $data[9];
		}

		$beer = array_map("utf8_encode", $beer);

		return $beer;
	}

	/**
	 * Parse le DOM pour retourner les données brutes d'une bière
	 * @return array
	 */
	private function parse()
	{
		$beers = array();
		for ($i=1; $i <= $this->count; $i++) {
			$data 		   = array();
			$html 		   = $this->getDom($i);
			$content       = $html->find('.bienvenue', 0);
			$data          = $this->getData($content);
			$beers[]	   = $this->getBeer($data);
		}

		return $beers;
	}

	/**
	 * Récupère les informations d'une bière en parsant le DOM
	 * @param  object $content
	 * @return array
	 */
	private function getData($content)
	{
		$data        = array();
		$data[]      = $content->find('h1', 0)->plaintext;
		$data[]      = $content->children(5)->innertext;
		$detailLeft  = $content->children(2)->children(0)->find('p');
		$detailRight = $content->children(2)->children(1)->find('p');

		foreach ($detailLeft as $item) {
			$data[] = $item->find('strong', 0)->innertext;
		}
		foreach ($detailRight as $item) {
			$data[] = $item->find('strong', 0)->innertext;
		}

		return $data;
	}

	/**
	 * Génère un fichier
	 * @param string $data
	 * @param string $format
	 */
	private function createFile($data, $format = 'json')
	{
		$file = fopen($this->getFilename($format), 'w');
		fwrite($file, $data);
		fclose($file);
	}
	
	/**
	 * Retourne l'intégralité du DOM de l'url en paramètre
	 * @param  int $index
	 * @return object
	 */
	private function getDom($index)
	{
		$pathUrl = str_replace('%id%', $index, 'toutesbieres-%id%.html');
		return file_get_html($this->url.$pathUrl);
	}

	/**
	 * Retourne le nom du fichier à générer
	 * @param  string $format
	 * @return string
	 */
	private function getFilename($format)
	{
		return $this->filename.'.'.$format;
	}
}