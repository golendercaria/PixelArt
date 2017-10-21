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
			
			//save params
			$this->params = $params;
			
			if( isset($this->params["fileName"]) && $this->params["fileName"] != null){
				//get image
				$this->url 				= $this->pathImage . $this->params["fileName"];
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
		
		public function collectPixel(){
			
			//check if sufficient point
			if( !isset($this->params["nbrPoint"]) || $this->params["nbrPoint"] < 1 ){
				die("Not sufficient point");
			}
			
			$currentPoint = 0;
			while( $currentPoint < $this->params["nbrPoint"] ){
				$currentPoint++;
				
				$tmpX = rand(0,$this->imageWidth);
				$tmpY = rand(0,$this->imageHeight);
				
				$tmpPixel = $this->getPixelColor($tmpX, $tmpY);
				
				pre( $tmpPixel );
			}
			
		}
		
		public function getPixelColor($x, $y){
			$rgb = imagecolorat($this->imageRessource, $x, $y);
			return imagecolorsforindex($this->imageRessource, $rgb);
		}

	}
	
	//params for class
	$params = array(
		"fileName"  => "pikachu.jpg",
		"nbrPoint"	=> 5,
	);
	
	//launch object	
	$imageRendering = new pixelArt($params);
	$imageRendering->collectPixel();