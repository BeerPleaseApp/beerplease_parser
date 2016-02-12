<?php
include_once 'simple_html_dom.php';

$Parser = new ParseBeer('http://www.paradis-biere.com/', 1);
echo $Parser->parseToJson();

/**
 * Parse le DOM d'un site pour récupérer les informations des bières
 */
class ParseBeer
{
	private $url;
	private $count 	  = 1;
	private $filename = 'beers';

	public function __construct($url, $count)
	{
		$this->url   = $url;
		$this->count = $count;
	}
	
	public function parseToJson()
	{
		$result = $this->parse();
		$this->createFile(json_encode($result , JSON_FORCE_OBJECT));

		if (file_exists($this->getFilename('json'))) {
			return 'Fichier généré avec succès.';
		}

		return 'Erreur lors de la génération du fichier.';
	}

	public function parseToCSV()
	{
		// @toto
	}

	private function getBeer($data)
	{
		$beer = array();
		foreach ($data as $key => $value) {
			$beer['name']        = utf8_encode($data[0]);
			$beer['description'] = utf8_encode($data[1]);
			$beer['brewer']      = $data[2];
			$beer['country']     = $data[3];
			$beer['alcohol']     = $data[4];
			$beer['color']       = $data[8];
			$beer['rate']        = $data[9];
		}

		return $beer;
	}

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

	private function createFile($data, $format = 'json')
	{
		$file = fopen($this->getFilename($format), 'w');
		fwrite($file, $data);
		fclose($file);
	}
	
	private function getDom($index)
	{
		$pathUrl = str_replace('%id%', $index, 'toutesbieres-%id%.html');
		return file_get_html($this->url.$pathUrl);
	}

	private function getFilename($format)
	{
		return $this->filename.'.'.$format;
	}
}