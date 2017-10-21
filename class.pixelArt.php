<?php


	class pixelArt{
		
		private $pathImage = "image/";
		
		public function __construct( $params = array() ){
			if( isset($params["fileName"]) && $params["fileName"] != null){
				//get image
				$this->url = $this->pathImage . $params["fileName"];
			    $image = imagecreatefromjpeg( $this->url );

				var_dump($image);
			}
		}
		
	}
	
	//params for class
	$params = array(
		"fileName" => "pikachu.jpg"
	);
	
	//launch object	
	$imageRendering = new pixelArt($params);
