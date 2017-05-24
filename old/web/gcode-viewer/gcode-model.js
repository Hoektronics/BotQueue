function createObjectFromGCode(gcode) {
  // GCode descriptions come from:
  //    http://reprap.org/wiki/G-code
  //    http://en.wikipedia.org/wiki/G-code
  //    SprintRun source code

  var object = new THREE.Object3D();

  var geometry = new THREE.Geometry();

  var lastLine = {x:0, y:0, z:0, e:0, f:0, extruding:false};

  var parser = new GCodeParser({

    G1: function(args, line) {
      // Example: G1 Z1.0 F3000
      //          G1 X99.9948 Y80.0611 Z15.0 F1500.0 E981.64869
      //          G1 E104.25841 F1800.0
      // Go in a straight line from the current (X, Y) point
      // to the point (90.6, 13.8), extruding material as the move
      // happens from the current extruded length to a length of
      // 22.4 mm.

      var newLine = {
        x: args.x !== undefined ? args.x : lastLine.x,
        y: args.y !== undefined ? args.y : lastLine.y,
        z: args.z !== undefined ? args.z : lastLine.z,
        e: args.e !== undefined ? args.e : lastLine.e,
        f: args.f !== undefined ? args.f : lastLine.f,
      };

      newLine.extruding = (newLine.e - lastLine.e) > 0;
      var color = new THREE.Color(newLine.extruding ? 0x00CC00 : 0x0000FF);

      if (newLine.extruding) {
        geometry.vertices.push(new THREE.Vector3(lastLine.x, lastLine.y, lastLine.z));
        geometry.vertices.push(new THREE.Vector3(newLine.x, newLine.y, newLine.z));
        //geometry.colors.push(color);
        //geometry.colors.push(color);
      }

      lastLine = newLine;
    },

    G21: function(args) {
      // G21: Set Units to Millimeters
      // Example: G21
      // Units from now on are in millimeters. (This is the RepRap default.)

      // No-op: So long as G20 is not supported.
    },

    G90: function(args) {
      // G90: Set to Absolute Positioning
      // Example: G90
      // All coordinates from now on are absolute relative to the
      // origin of the machine. (This is the RepRap default.)

      // TODO!
    },

    G91: function(args) {
      // G91: Set to Relative Positioning
      // Example: G91
      // All coordinates from now on are relative to the last position.

      // TODO!
    },

    G92: function(args) { // E0
      // G92: Set Position
      // Example: G92 E0
      // Allows programming of absolute zero point, by reseting the
      // current position to the values specified. This would set the
      // machine's X coordinate to 10, and the extrude coordinate to 90.
      // No physical motion will occur.

      // TODO: Only support E0
    },

    M82: function(args) {
      // M82: Set E codes absolute (default)
      // Descriped in Sprintrun source code.

      // No-op, so long as M83 is not supported.
    },

    M84: function(args) {
      // M84: Stop idle hold
      // Example: M84
      // Stop the idle hold on all axis and extruder. In some cases the
      // idle hold causes annoying noises, which can be stopped by
      // disabling the hold. Be aware that by disabling idle hold during
      // printing, you will get quality issues. This is recommended only
      // in between or after printjobs.

      // No-op
    },

    'default': function(args, info) {
      //console.error('Unknown command:', args.cmd, args, info);
    },
  });

  parser.parse(gcode);

  var lineMaterial = new THREE.LineBasicMaterial({
      opacity:0.4,
      linewidth: 1,
      vertexColors: false,
      color: 0x00BB00
  });
  var line = new THREE.Line(geometry, lineMaterial, THREE.LinePieces);
  line.castShadow = true;
  line.receiveShadow = true;
  object.add(line);

  // find our center.
  geometry.computeBoundingBox();
  geometry.computeBoundingSphere();

  geometry.bounds = new THREE.Vector3(
    geometry.boundingBox.max.x - geometry.boundingBox.min.x,
    geometry.boundingBox.max.y - geometry.boundingBox.min.y,
    geometry.boundingBox.max.z - geometry.boundingBox.min.z
  );
  //console.log(geometry.bounds);
  
  geometry.center = new THREE.Vector3(
    (geometry.boundingBox.max.x + geometry.boundingBox.min.x)/2,
    (geometry.boundingBox.max.y + geometry.boundingBox.min.y)/2,
    (geometry.boundingBox.max.z + geometry.boundingBox.min.z)/2
  );  
  
  var center = new THREE.Vector3()
      .add(geometry.boundingBox.min, geometry.boundingBox.max)
      .divideScalar(2);
  center.z = geometry.boundingBox.min.z;

  //move it to the center.
  object.position = center.multiplyScalar(-1);
  //object.scale.multiplyScalar(1);

  

  return object;
}
