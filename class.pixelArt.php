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
			
			//ratio opacity
			if( isset($this->params["rangeSizeShape"][0]) ){
				if( isset($this->params["minOpacity"]) ){
					//max opacity in real alpha
					$this->maxOpacity = 127 - 127/100*$this->params["minOpacity"];
				}
				$this->ratioOpacity = $this->maxOpacity / $this->params["rangeSizeShape"][1];
			}

			//convert params % to correct value
			if( isset( $this->params["minOpacity"] ) ){
				$this->minOpacity = 127 - ($this->params["minOpacity"] * 127/100);
				
				//set for opacity, based on minOpacity (100% to minOpacity by range size ex: 20,100) means 80 steps of opacity
				$this->stepOpacityPercent = ( 100 - $this->params["minOpacity"] ) / ( $this->params["rangeSizeShape"][1] - $this->params["rangeSizeShape"][0] );
			}
			
			//set lowerization level
			if( !isset($this->params["lowerizationLvl"]) ){
				$this->params["lowerizationLvl"] = 1;	
			}		
			
			
			

			if( isset($this->params["fileName"]) && $this->params["fileName"] != null){
				//get image
				$this->url 				= $this->pathImage . $this->params["fileName"];
				$this->imageRessource 	= imagecreatefromjpeg( $this->url );
				
				//save height and width of image
				if( $this->imageRessource ){
			    	$this->imageWidth 	= imagesx( $this->imageRessource );
			    	$this->imageHeight 	= imagesy( $this->imageRessource );

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
			$this->listOfPoint = array();
			while( $currentPoint < $this->params["nbrPoint"] ){
				$currentPoint++;
				
				$tmpX = rand(0,$this->imageWidth-1);
				$tmpY = rand(0,$this->imageHeight-1);
				
				$tmpPixel = $this->getPixelColor($tmpX, $tmpY);
				$this->listOfPoint[] = array(
					"x" => $tmpX, 
					"y"	=> $tmpY, 
					"color" => $tmpPixel
				);
			}
			
		}
		
		public function getPixelColor($x, $y){
			$rgb = imagecolorat($this->imageRessource, $x, $y);
			return imagecolorsforindex($this->imageRessource, $rgb);
		}
		
		public function makingShape(){
			
			//check if sufficient point
			if( !isset($this->params["shape"]) ){
				die("Not shape defined");
			}
			
			$this->{$this->params["shape"]}();
		}
		
		public function generateFusionImage(){
			$tmpImage  	= imagecreatetruecolor($this->imageWidth, $this->imageHeight);
			$whiteBg 	= imagecolorallocate($tmpImage, 255, 255, 255);
			imagefill($tmpImage, 0, 0, $whiteBg);
			
			return $tmpImage;
		}
		
				
		public function convertOpacityPercentToRealOpacityValue( $opacityPercent ){
			return 127 - $opacityPercent * $this->ratioOpacity;
		}
		
		
		public function getOpacity( $opacity, $size ){
			return rand($size * $this->ratioOpacity, $this->maxOpacity);
		}
		
		
		public function generateSizeShape($a, $b, $lvl){
			if( $lvl == 1 ){
				return rand($a, $b);
			}else{
				$lvl = $lvl-1;
				return $this->generateSizeShape($a, rand($a, $b), $lvl);
			}
		}
		
		public function rect(){
			
			//check if point exist
			if( empty($this->listOfPoint) ){
				die("Empty listOfPoint");
			}
			
			//check if range for size of shape
			if( !isset($this->params["rangeSizeShape"][0]) || !isset($this->params["rangeSizeShape"][1]) ){
				die("Missig range size shape");	
			}
			
			//generate blank image 
			$tmpImage = $this->generateFusionImage();
			
			//making shape for each point
			foreach($this->listOfPoint as $point){

				//generate random size and get the middle of shape
				$sizeShape = $this->generateSizeShape($this->params["rangeSizeShape"][0], $this->params["rangeSizeShape"][1], $this->params["lowerizationLvl"]);
				$middleOfShape 	= round($sizeShape/2);
				
				//calculate the position of begin shape
				$shapeX = $point["x"] - $middleOfShape;
				$shapeY = $point["y"] - $middleOfShape;
				
				//calculate end position of shape
				$shapeXEnd = $shapeX + $sizeShape;
				$shapeYEnd = $shapeY + $sizeShape;

				//generate random opacity
				$opacity = $this->getOpacity($point["color"]["alpha"], $sizeShape);

				//prepare color
				$tmpColor = imagecolorallocatealpha($tmpImage, $point["color"]["red"], $point["color"]["green"], $point["color"]["blue"], $opacity);
				//construct shape on tmpImage
				imagefilledrectangle($tmpImage, $shapeX, $shapeY, $shapeXEnd, $shapeYEnd, $tmpColor);
					
			}
			
			
			imagepng($tmpImage, "image_created/rect.png");
			imagedestroy($tmpImage);
			
			?><img src="image_created/rect.png?<?php uniqid(); ?>" /><?php
		}

	}
	
	//params for class
	$params = array(
		"fileName"            => "pikachu.jpg",
		"nbrPoint"            => 5000,
		"shape"               => "rect",
		"rangeSizeShape"	  => array(0,50),
		"minOpacity"		  => 30,	//0 = hide | 100 = visible
		"lowerizationLvl"	  => 2	
	);
	
	//launch object	
	$imageRendering = new pixelArt($params);
	$imageRendering->collectPixel();
	$imageRendering->makingShape();
	
	
	