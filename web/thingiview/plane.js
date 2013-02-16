/**
 * @author mr.doob / http://mrdoob.com/
 * based on http://papervision3d.googlecode.com/svn/trunk/as3/trunk/src/org/papervision3d/objects/primitives/Plane.as
 */

var Grid = function ( width, height, size, material) {

	THREE.Object3D.call( this );
  
  var xSegments = (width / size) + 1;
  var ySegments = (height / size) + 1;
  var xCenter = width / 2;
  var yCenter = height / 2
  
  var iy, ix;

	for( ix = 0; ix < xSegments; ix++ ) {
		var x = ix * size - xCenter;
		var g = new THREE.Geometry();
		g.vertices.push(new THREE.Vector3(x, -yCenter, 0))
		g.vertices.push(new THREE.Vector3(x, yCenter, 0));
		var line = new THREE.Line(g, material);
		this.add(line);
	}
	
	for( iy = 0; iy < ySegments; iy++ ) {
		var y = iy * size - yCenter;
		var g = new THREE.Geometry();
		g.vertices.push(new THREE.Vector3(-xCenter, y, 0))
		g.vertices.push(new THREE.Vector3(xCenter, y, 0));
		var line = new THREE.Line(g, material);
		this.add(line);
	}
};

Grid.prototype = new THREE.Object3D();
Grid.prototype.constructor = Grid;
