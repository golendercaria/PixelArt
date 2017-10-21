<?php

	ini_set('memory_limit', '2048M');

	//error_reporting(0);
	function pre($a = null){
		echo "<pre>";
		print_r($a);
		echo "<pre>";
	}
	
	class pixelArt{
		
		private $pathImage = "image/";
		
		public function __construct( $params = array() ){
			if( isset($params["fileName"]) && $params["fileName"] != null){
				//get image
				$this->url 				= $this->pathImage . $params["fileName"];
				$this->imageRessource 	= imagecreatefromjpeg( $this->url );
				
				//save height and width of image
				if( $this->imageRessource ){
			    	$this->imageWidth 	= imagesx( $this->imageRessource );
			    	$this->imageHeight 	= imagesy( $this->imageRessource );
			    	
			    	pre($this);
				}else{
					echo "Image not loading";
				}
			}
		}

	}
	
	//params for class
	$params = array(
		"fileName" => "pikachu.jpg"
	);
	
	//launch object	
	$imageRendering = new pixelArt($params);
