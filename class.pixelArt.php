<?php

	ini_set('memory_limit', '2048M');

	include("lib/gif.php");

	//error_reporting(0);
	function pre($a = null){
		echo "<pre>";
		print_r($a);
		echo "<pre>";
	}
	
	class pixelArt{
		
		private $pathImage 					= "image/";
		private $percentBorderLess  		= 90;
		private $borderLessColor    		= false;
		private $randomColor				= false;
		private $nbrAnimatedImage			= 10;
		private $intervalDurationAnimation  = 1000;
		
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
			    	$this->imageWidth 	= imagesx( $this->imageRessource )-1;
			    	$this->imageHeight 	= imagesy( $this->imageRessource )-1;

				}else{
					echo "Image not loading";
				}
			}
			
			//border less
			if( isset($this->params["borderLess"]) && $this->imageRessource){
				$this->getBorderColor();
			}
			
			//random color
			if( isset($this->params["randomColor"]) && $this->imageRessource){
				$this->randomColor = true;
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
				
				$tmpX = rand(0,$this->imageWidth);
				$tmpY = rand(0,$this->imageHeight);
				
				$tmpPixel = $this->getPixelColor($tmpX, $tmpY);
				
				if( $this->borderLessColor ){
					if( $tmpPixel["red"] == $this->borderLessColor["red"] && $tmpPixel["green"] == $this->borderLessColor["green"] && $tmpPixel["blue"] == $this->borderLessColor["blue"] ){
						continue;
					}
				}
				
				if( $this->randomColor ){
					$tmpPixel["red"] = rand(0, 255);
					$tmpPixel["green"] = rand(0, 255);
					$tmpPixel["blue"] = rand(0, 255);
				}
				
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
		
		public function getBorderColor(){
			$arrayColor = array();
			$nbrPixel = 0;
			//top and bottom
			for($w = 0; $w <= $this->imageWidth; $w++){
				$tmpPixel = $this->getPixelColor($w, 0);
				if( !isset($arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]) ){
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]] = 1;
				}else{
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]++;
				}
				$tmpPixel = $this->getPixelColor($w, $this->imageHeight);
				if( !isset($arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]) ){
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]] = 1;
				}else{
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]++;
				}
				$nbrPixel+=2;
			}
			
			//left and right
			for($h = 0; $h <= $this->imageHeight; $h++){
				$tmpPixel = $this->getPixelColor(0, $h);
				if( !isset($arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]) ){
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]] = 1;
				}else{
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]++;
				}
				$tmpPixel = $this->getPixelColor($this->imageWidth, $h);
				if( !isset($arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]) ){
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]] = 1;
				}else{
					$arrayColor[$tmpPixel["red"]][$tmpPixel["green"]][$tmpPixel["blue"]]++;
				}
				$nbrPixel+=2;
			}
		
			//get max
			$maxBorderOccurenceColor = array();
			$tmpMax = 0;
			foreach($arrayColor as $redValue => $redNext){
				foreach($redNext as $greenValue => $greenNext){
					foreach($greenNext as $blueValue => $occurence){
						if( $occurence > $tmpMax ){
							$tmpMax = $occurence;
							$maxBorderOccurenceColor = array("red" => $redValue, "green" => $greenValue, "blue" => $blueValue);
						}
					}
				}
			}
		
			//check percent
			if( ($tmpMax / $nbrPixel * 100) > $this->percentBorderLess ){
				$this->borderLessColor = $maxBorderOccurenceColor;
			}
		}
		
		public function makingShape($return = false){
			
			//check if sufficient point
			if( !isset($this->params["shape"]) ){
				die("Not shape defined");
			}
			
			//rendering option

			/*

			
			//display or return image
			if( isset($this->params["display"]) && $this->params["display"] == true ){				
				imagepng($tmpImage, "image_created/rect.png");
				imagedestroy($tmpImage);
				?><img src="image_created/rect.png?<?php uniqid(); ?>" /><?php
			}else{
				return $tmpImage;
			}
				
			*/
			
			if($return){
				return $this->{$this->params["shape"]}();
			}else{
				$image = $this->{$this->params["shape"]}();
				if( isset($this->params["exportMode"]) ){
					if( $this->params["exportMode"] == "image" ){
						header('Content-type: image');
						imagepng($image);
						exit();						
					}
				}
			}
		}
		
		public function generateFusionImage($blank = true){
			if( $blank === true){	
				$tmpImage  	= imagecreatetruecolor($this->imageWidth, $this->imageHeight);
				$whiteBg 	= imagecolorallocate($tmpImage, 255, 255, 255);
				imagefill($tmpImage, 0, 0, $whiteBg);
				
				return $tmpImage;
			}else{
				return $this->imageRessource;
			}
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
		
		public function makingAnimation(){
			
			//replace default nbr image
			if( isset($this->params["animated"]["nbrImage"]) ){
				$this->nbrAnimatedImage = $this->params["animated"]["nbrImage"];
			}
			
			//replace default interval
			if( isset($this->params["animated"]["timerInterval"]) ){
				$this->intervalDurationAnimation = $this->params["animated"]["timerInterval"];
			}
		
			//construct image
			$tmpListImg = array();
			for($nbrImage = 0; $nbrImage < $this->nbrAnimatedImage; $nbrImage++){
				$this->collectPixel();
				//$tmpListImg[] = $this->makingShape(true);
				
				ob_start();
				imagegif($this->makingShape(true));
				$tmpListImg[] = ob_get_clean();
		
				 
			}
	
			//making
			$gif = new GIFEncoder($tmpListImg, $this->intervalDurationAnimation/10, 0, 2, 0, 0, 0, 'bin');
			header('Content-type: image/gif');
			echo $gif->GetAnimation();	
		}
		
		public function rect(){
			
			//check if point exist
			if( empty($this->listOfPoint) ){
				die("Empty listOfPoint");
			}
			
			//check if range for size of shape
			if( !isset($this->params["rangeSizeShape"][0]) || !isset($this->params["rangeSizeShape"][1]) ){
				die("Missing range size shape");	
			}
			
			//generate blank image 
			$tmpImage = $this->generateFusionImage(true);
			
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
			
			return $tmpImage;			
			
		}
		
		
		public function polygoneShape(){
			
			//check if point exist
			if( empty($this->listOfPoint) ){
				die("Empty listOfPoint");
			}
			
			//check if range for size of shape
			if( !isset($this->params["rangeSizeShape"][0]) || !isset($this->params["rangeSizeShape"][1]) ){
				die("Missing range size shape");	
			}
			
			//generate blank image 
			$tmpImage = $this->generateFusionImage(false);

			foreach($this->listOfPoint as $point){
		
				//size of poly
				$sizeShape = rand($this->params["rangeSizeShape"][0], $this->params["rangeSizeShape"][1]);
		
				//posPoly 
				$posPoly = $this->generatePoly($point, $sizeShape);
		
				//generate random opacity
				$opacity = $this->getOpacity($point["color"]["alpha"], $sizeShape);

				//prepare color
				$tmpColor = imagecolorallocatealpha($tmpImage, $point["color"]["red"], $point["color"]["green"], $point["color"]["blue"], $opacity);
				
				//create poly
				imagefilledpolygon( $tmpImage, $posPoly, $this->anglePoly, $tmpColor);
		
			}
			
			return $tmpImage;
		
		}
		
		public function triangle(){
			return $this->polygoneShape();
		}
		
		public function diamond(){
			return $this->polygoneShape();
		}
		
		public function generatePoly($point, $sizeShape){
			
			//direction of poly
			$topOrBottom = rand(0,1);			
			
			if( $this->params["shape"] == "triangle" ){
				//coordinate of point
				$pointX = $point["x"] - $sizeShape;
				$pointY = $point["x"] + $sizeShape;
		
				if( $topOrBottom == 0 ){
					$pointZ = $point["y"] - $sizeShape;
				}else{
					$pointZ = $point["y"] + $sizeShape;
				}
				
				//define number of point for GD function
				$this->anglePoly = 3;
				
				return array(
					$pointX, $point["y"], //x y
					$pointY, $point["y"], //x y
					$point["x"], $pointZ, //x y
				);
			}elseif( $this->params["shape"] == "diamond" ){
				$point1 = $point["x"] - $sizeShape;
				$point2 = $point["x"] + $sizeShape;
				$point3 = $point["y"] - $sizeShape;
				$point4 = $point["y"] + $sizeShape;

				$this->anglePoly = 4;
				
				return array(
					$point1, $point["y"], //x y
					$point["x"], $point3, //x y
					$point2, $point["y"], //x y
					$point["x"], $point4, //x y
				);
			}
			
		}
	}
	
	
	
	//params for class
	$params = array(
		"fileName"            => "fire.jpg",
		"nbrPoint"            => 4,
		"shape"               => "diamond",
		"rangeSizeShape"	  => array(0,500),
		"minOpacity"		  => 10,	//0 = hide | 100 = visible
		"lowerizationLvl"	  => 1,
		"borderLess"		  => false,
		"exportMode"		  => "image",	
		"randomColor"		  => false			
	);
	
	//launch object	
	$imageRendering = new pixelArt($params);
	$imageRendering->collectPixel();
	$imageRendering->makingShape();
	
	
	/*
	//params for class
	$params = array(
		"fileName"            => "sangoku.jpg",
		"nbrPoint"            => 20000,
		"shape"               => "rect",
		"rangeSizeShape"	  => array(0,20),
		"minOpacity"		  => 30,	//0 = hide | 100 = visible
		"lowerizationLvl"	  => 2,
		"borderLess"		  => true,
		"exportMode"		  => "image",				
		"animated"		  	  => array(
			"nbrImage"		=> 3,
			"timerInterval"	=> 100
		)
	);
	
	//launch object	
	$imageRendering = new pixelArt($params);
	$imageRendering->makingAnimation();
	*/
	
	
	
	/*
	$imageRendering = new pixelArt($params);
	$imageRendering->collectPixel();
	$imageRendering->makingShape();
	*/
	
	