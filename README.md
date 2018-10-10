Drupal 8 three.js module
========

[![Latest NPM release][npm-badge]][npm-badge-url]
[![License][license-badge]][license-badge-url]
[![Dependencies][dependencies-badge]][dependencies-badge-url]

#### JavaScript 3D library for Drupal 8 integration ####

The aim of the project is to create an easy to use Three.js library in Drupal projects. 
The library provides Canvas 2D, SVG, CSS3D and WebGL renderers.

### Usage ###

Download [Three.js](http://threejs.org/build/three.min.js) library and unzip into <strong>/libraries/three.js/</strong> folder, or use composer install with configs:

```composer
...
"repositories": [
        ...
        {
            "type": "package",
            "package": {
                "name": "mrdoob/threejs",
                "version": "master",
                "type": "drupal-library",
                "dist": {
                    "url": "https://github.com/mrdoob/three.js/archive/dev.zip",
                    "type": "zip"
                },
                "require": {
                    "composer/installers": "~1.0"
                }
            }
        },
        ...
        "require": {
        ...
            "mrdoob/threejs": "master"
        },
        ...
```
 
The [minified library](http://threejs.org/build/three.min.js) should be installed at <strong>/libraries/three.js/build/three.min.js</strong>, or any path supported by libraries.module if installed.

```html
<script src="/libraries/three.js/build/three.min.js"></script>
```

This code creates a scene, a camera, and a geometric cube, and it adds the cube to the scene. It then creates a `WebGL` renderer for the scene and camera, and it adds that viewport to the document.body element. Finally, it animates the cube within the scene for the camera.

```javascript
var camera, scene, renderer;
var geometry, material, mesh;

init();
animate();

function init() {

	camera = new THREE.PerspectiveCamera( 70, window.innerWidth / window.innerHeight, 0.01, 10 );
	camera.position.z = 1;

	scene = new THREE.Scene();

	geometry = new THREE.BoxGeometry( 0.2, 0.2, 0.2 );
	material = new THREE.MeshNormalMaterial();

	mesh = new THREE.Mesh( geometry, material );
	scene.add( mesh );

	renderer = new THREE.WebGLRenderer( { antialias: true } );
	renderer.setSize( window.innerWidth, window.innerHeight );
	document.body.appendChild( renderer.domElement );

}

function animate() {

	requestAnimationFrame( animate );

	mesh.rotation.x += 0.01;
	mesh.rotation.y += 0.02;

	renderer.render( scene, camera );

}
```

If everything went well on administrative [config page](http://localhost/admin/config/media/threejs) you should see [this](https://jsfiddle.net/f2Lommf5/).

### Change log ###

[Releases](https://github.com/eneus/drupal_threejs/releases)


[npm-badge]: https://img.shields.io/npm/v/three.svg
[npm-badge-url]: https://www.npmjs.com/package/three
[license-badge]: https://img.shields.io/npm/l/three.svg
[license-badge-url]: ./LICENSE
[dependencies-badge]: https://img.shields.io/david/mrdoob/three.js.svg
[dependencies-badge-url]: https://david-dm.org/mrdoob/three.js