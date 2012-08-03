<?php

class Image_3D_Chunk {
	
	protected $type;
	protected $content;
	protected $size;
	
	protected $chunks;
	
 //>------ Primary chunk
	const	MAIN3DS			= 0x4D4D;

 //>------ Main Chunks
 	const	EDIT3DS			= 0x3D3D;  // this is the start of the editor config
 	const	KEYF3DS			= 0xB000;  // this is the start of the keyframer config

 //>------ sub defines of EDIT3DS
 	const	EDIT_MATERIAL	= 0xAFFF;
 	const	EDIT_CONFIG1	= 0x0100;
 	const	EDIT_CONFIG2	= 0x3E3D;
 	const	EDIT_VIEW_P1	= 0x7012;
 	const	EDIT_VIEW_P2	= 0x7011;
 	const	EDIT_VIEW_P3	= 0x7020;
 	const	EDIT_VIEW1		= 0x7001;
 	const	EDIT_BACKGR		= 0x1200;
 	const	EDIT_AMBIENT	= 0x2100;
 	const	EDIT_OBJECT		= 0x4000;

 //>------ sub defines of EDIT_OBJECT
 	const	OBJ_TRIMESH		= 0x4100;
 	const	OBJ_LIGHT		= 0x4600;
 	const	OBJ_CAMERA		= 0x4700;

 	const	OBJ_UNKNWN01	= 0x4010;
 	const	OBJ_UNKNWN02	= 0x4012;

 //>------ sub defines of OBJ_CAMERA
 	const	CAM_UNKNWN01	= 0x4710; 
 	const	CAM_UNKNWN02	= 0x4720; 

 //>------ sub defines of OBJ_LIGHT
 	const	LIT_OFF			= 0x4620;
 	const	LIT_SPOT		= 0x4610;
 	const	LIT_UNKNWN01	= 0x465A;

 //>------ sub defines of OBJ_TRIMESH
 	const	TRI_VERTEXL		= 0x4110;
 	const	TRI_FACEL2		= 0x4111; 
 	const	TRI_FACEL1		= 0x4120;
 	const	TRI_SMOOTH		= 0x4150;
 	const	TRI_LOCAL		= 0x4160;
 	const	TRI_VISIBLE		= 0x4165;

 //>>------ sub defs of KEYF3DS
 	const	KEYF_UNKNWN01	= 0xB009;
 	const	KEYF_UNKNWN02	= 0xB00A;
 	const	KEYF_FRAMES		= 0xB008;
 	const	KEYF_OBJDES		= 0xB002;

 //>>------  these define the different color chunk types
 	const	COL_RGB			= 0x0010;
 	const	COL_TRU			= 0x0011;
 	const	COL_UNK			= 0x0013;

 //>>------ defines for viewport chunks
 	const	TOP				= 0x0001;
 	const	BOTTOM			= 0x0002;
 	const	LEFT			= 0x0003;
 	const	RIGHT			= 0x0004;
 	const	FRONT			= 0x0005;
 	const	BACK			= 0x0006;
 	const	USER			= 0x0007;
 	const	CAMERA			= 0x0008;
 	const	LIGHT			= 0x0009;
 	const	DISABLED		= 0x0010;
 	const	BOGUS			= 0x0011;
 	
	public function __construct($type, $content) {
		$this->type = (int) $type;
		$this->size = strlen($content);
		$this->content = $content;
	}
	
	public function readChunks() {
		
		if (count($this->chunks) || ($this->size < 6)) return false;
		
		$position = 0;
		$string = $this->content;
		$length = $this->size - 6;
		
		while ($position <= $length) {
			$type = $this->getWord(substr($string, $position, 2));
			$position += 2;
			$chunkLength = $this->getDWord(substr($string, $position, 4)) - 6;
			$position += 4;
			
			$this->chunks[] = new Image_3D_Chunk($type, substr($string, $position, $chunkLength));
			$position += $chunkLength;
		}
	}
	
	public function debug() {
		printf("Typ: %6d (0x%04x) (%6d bytes) | Objects:%4d | Content:%6d\n", $this->type, $this->type, $this->size, count($this->chunks), strlen($this->content));
	}
	
	protected function getWord($string) {
		return (ord($string{1}) << 8) | ord($string{0});
	}
	
	protected function getDWord($string) {
		return ord($string{0}) | (ord($string{1}) << 8) | (ord($string{2}) << 16) | (ord($string{3}) << 32);
	}
	
	protected function getUnsignedInt($string) {
		return (ord($string{0}) << 8) | ord($string{1});
	}
	
	protected function getFloat($string) {
		// Convert C-Float to PHP-Float
		return (ord($string{3}) & 128 ? -1 : 1) * (1 + (float) (ord($string{2}) & 127) / 127 + (float) (ord($string{1})) / 256 / 127 + (float) (ord($string{0})) / 256 / 256 / 127) * pow(2., ((((ord($string{3}) & 127) << 1) | (ord($string{2}) >> 7)) - 127));
	}
	
	public function getChunks() {
		return $this->chunks;
	}
	
	public function getType() {
		return $this->type;
	}
	
	public function getContent() {
		return $this->content;
	}
	
	public function getFirstChunkByType($type) {
		if (!is_int($type)) $type = hexdec($type);
		
		foreach ($this->chunks as $chunk) if ($chunk->getType() === $type) return $chunk;
		return false;
	}
	
	public function getChunksByType($type) {
		if (!is_int($type)) $type = hexdec($type);
		
		$chunks = array();
		foreach ($this->chunks as $chunk) if ($chunk->getType() === $type) $chunks[] = $chunk;
		return $chunks;
	}
}

class Image_3D_Chunk_Object extends Image_3D_Chunk {
	
	protected $name;
	
	public function __construct($type, $content) {
		parent::__construct($type, $content);
		$this->getName();
	}
	
	protected function getName() {
		$i = 0;
		while ((ord($this->content{$i}) !== 0) && ($i < $this->size)) $this->name .= $this->content{$i++};
		$this->content = substr($this->content, $i + 1);
	}
	
	public function readChunks(Image_3D_Object_3ds $k3ds) {
		$subtype = $this->getWord(substr($this->content, 0, 2));
		$subcontent = substr($this->content, 6);
		
		switch ($subtype) {
			case self::OBJ_TRIMESH:
				$object = $k3ds->addObject($this->name);
				$this->chunks[] = new Image_3D_Chunk_TriMesh($subtype, $subcontent, $object);
			break;
		}
	}
	
	public function debug() {
		echo 'Object: ', $this->name, "\n";
		parent::debug();
	}
}

class Image_3D_Chunk_TriMesh extends Image_3D_Chunk {
	
	protected $matrix;
	
	protected $object;
	
	public function __construct($type, $content, $object) {
		parent::__construct($type, $content);
		
		$this->object = $object;
		
		$this->readChunks();
		
		$this->getPoints();
		$this->getFaces();
	}
	
	protected function getPoints() {
		$vertexlists = $this->getChunksByType(Image_3D_Chunk::TRI_VERTEXL);
		foreach ($vertexlists as $vertexlist) {
			$points = $vertexlist->getContent();
			$count = $this->getWord(substr($points, 0, 2));
			$points = substr($points, 2);
			
			for ($i = 0; $i < $count; $i++) {
				$x = $this->getFloat(substr($points, 0, 4));
				$y = $this->getFloat(substr($points, 4, 4));
				$z = $this->getFloat(substr($points, 8, 4));
				$this->object->newPoint($x, $y, $z);
				$points = substr($points, 12);
			}
		}
	}
	
	protected function getFaces() {
		$facelists = $this->getChunksByType(Image_3D_Chunk::TRI_FACEL1);
		foreach ($facelists as $facelist) {
			$faces = $facelist->getContent();
			$count = $this->getWord(substr($faces, 0, 2));
			$faces = substr($faces, 2);
			
			for ($i = 0; $i < $count; $i++) {
				$p1 = $this->getWord(substr($faces, 0, 2));
				$p2 = $this->getWord(substr($faces, 2, 2));
				$p3 = $this->getWord(substr($faces, 4, 2));
				$this->object->newPolygon($p1, $p2, $p3);
				$faces = substr($faces, 8);
			}
		}
	}
	
	protected function getTranslations() {
		$translists = $this->getChunksByType(Image_3D_Chunk::TRI_LOCAL);
		foreach ($translists as $translist) {
			$trans = $translist->getContent();

			echo "Trans: " . strlen($trans), "\n";
		}
	}
	
	public function debug() {
		parent::debug();
		printf("Trimesh with %d (0x%04x) points - Pointsize: %.2f\n", $this->pointCount, $this->pointCount, $this->size / $this->pointCount);
	}
}

class Image_3D_Object_3ds_Object extends Image_3D_Object {
	
	protected $_points;
	
	public function __construct() {
		parent::__construct();
		
		$this->_points = array();
	}
	
	public function newPoint($x, $y, $z) {
		$this->_points[] = new Image_3D_Point($x, $y, $z);
//		echo "New Point: $x, $y, $z -> ", count($this->_points), "\n";
	}
	
	public function newPolygon($p1, $p2, $p3) {
		if (!isset($this->_points[$p1]) || !isset($this->_points[$p2]) || !isset($this->_points[$p3])) {
//			printf("ERROR: Unknown point (%d, %d, %d of %d)\n", $p1, $p2, $p3, count($this->_points) - 1);
			return false;
		}
		$this->_addPolygon(new Image_3D_Polygon($this->_points[$p1], $this->_points[$p2], $this->_points[$p3]));
//		echo "New Polygon: $p1, $p2, $p3 -> ", count($this->_polygones), "\n";
	}
	
	public function debug() {
		printf("Points: %d | Polygones: %d (%d)\n", count($this->_points), count($this->_polygones), $this->_polygonCount);
	}
}

?>