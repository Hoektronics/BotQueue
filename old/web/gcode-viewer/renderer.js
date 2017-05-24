var directionalLight = null;
var pointLight = null;
var camera = null;

function createScene(element) {

  // Renderer
  var renderer = new THREE.WebGLRenderer({clearColor:0xEEEEEE, clearAlpha: 1, antialias: true});
  renderer.setSize(element.width(), element.height());
  element.append(renderer.domElement);
  renderer.clear();

  // Scene
  var scene = new THREE.Scene(); 

  // Lights...
/*
  [[0,0,1,  0xFFFFCC],
   [0,1,0,  0xFFCCFF],
   [1,0,0,  0xCCFFFF],
   [0,0,-1, 0xCCCCFF],
   [0,-1,0, 0xCCFFCC],
   [-1,0,0, 0xFFCCCC]].forEach(function(position) {
    var light = new THREE.DirectionalLight(position[3]);
    light.position.set(position[0], position[1], position[2]).normalize();
    scene.add(light);
  });
*/

  //our directional light is out in space
  directionalLight = new THREE.DirectionalLight(0xffffff, 0.65);
  directionalLight.x = -150;
  directionalLight.y = -150;
  directionalLight.z = 300;
  directionalLight.position.normalize();
  scene.add(directionalLight);

  pointLight = new THREE.PointLight(0xffffff, 0.6);
  pointLight.position.x = 0;
  pointLight.position.y = 0;
  pointLight.position.z = 300;
  scene.add(pointLight);

  // Camera...
  var fov    = 45,
      aspect = element.width() / element.height(),
      near   = 1,
      far    = 100000,
      camera = new THREE.PerspectiveCamera(fov, aspect, near, far);

  //set our initial position.
  camera.position.x = 0;
  camera.position.y = -150;
  camera.position.z = 150;

  //camera.lookAt(scene.position);
  scene.add(camera);
 
  //these are our controls.
  controls = new THREE.ModelControls(camera);
  controls.zoomSpeed = 0.05;
  controls.dynamicDampingFactor = 0.40;

  grid = new Grid(200, 200, 10, new THREE.LineBasicMaterial({color:0x111111, linewidth:1}));
  scene.add(grid);

  // Action!
  function render() {
    controls.update();
    renderer.render(scene, camera);

    requestAnimationFrame(render); // And repeat...
  }
  render();

  // Fix coordinates up if window is resized.
  $(window).on('resize', function() {
    renderer.setSize(element.width(), element.height());
    camera.aspect = element.width() / element.height();
    camera.updateProjectionMatrix();
    controls.screen.width = window.innerWidth;
    controls.screen.height = window.innerHeight;
  });

  return scene;
}

function updateCameraView(geometry)
{
  // set camera position outside and above our object.
  distance = geometry.boundingSphere.radius / Math.sin((camera.fov/2) * (Math.PI / 180));
  camera.position.x = 0;
  camera.position.y = -distance;
  camera.position.z = distance;

  //todo: how to control where it looks at!
  //camera.lookAt(new THREE.Vector3(0, 0, geometry.center.z));
  //camera.updateProjectionMatrix()

  //our directional light is out in space
  directionalLight.x = geometry.boundingBox.min.x * 2;
  directionalLight.y = geometry.boundingBox.min.y * 2;
  directionalLight.z = geometry.boundingBox.max.z * 2;

  //our point light is straight above.
  pointLight.x = geometry.center.x;
  pointLight.y = geometry.center.y;
  pointLight.z = geometry.boundingBox.max.z * 2; 
}

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