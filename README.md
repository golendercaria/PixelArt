pixelArt
=====================

Realise of a PHP script that can manipulate an image source and create images with a pixel style.
Research and study with the use of the GD library.

<img src="https://nouslesdevs.com/wp-content/uploads/2017/10/making_rect-example_opacityBySize_v2.png" alt="" />

Documentation
=============

fileName (string) 			: Source of image.
nbrPoint (int)  			: The nunmber of pixel that you want to take in the picture.
shape (string)  			: The shape, for the moment only rect is available. 
rangeSizeShape (array:int) 	: Range of size for the rect shape in pixel.
minOpacity					: The mininum opacity for shape, in percent.
lowerizationLvl	(int)		: If this number is large then the large size will be rare 
borderLess (bool)			: True, then the edge detection is done and similar color pixels are not taken, typically use when the images are on a solid background.

Required
--------

PHP & GD

How to use
----------

Load the script with URL of this

Basic example
-------------

```php
//params for class
$params = array(
	"fileName"            => "pikachu.jpg",
	"nbrPoint"            => 10000,
	"shape"               => "rect",
	"rangeSizeShape"	  => array(0,50),
	"minOpacity"		  => 30,	//0 = hide | 100 = visible
	"lowerizationLvl"	  => 3,
	"borderLess"		  => true	
);

//launch object	
$imageRendering = new pixelArt($params);
$imageRendering->collectPixel();
$imageRendering->makingShape();
```