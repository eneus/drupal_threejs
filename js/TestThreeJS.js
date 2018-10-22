/**
 * @file
 * TestThreeJS behaviors.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.TestThreeJS = {
    /**
     * ThreeJS Options, coming from Drupal.settings.
     */
    ThreeJSOptions: {},
    /**
     * Instantiated WEB GL container.
     */
    $container: [],
    /**
     * Load ThreeJS once page is ready
     */
    attach: function (context, settings) {
      this.ThreeJSOptions = settings.threejs ? settings.threejs.options : {};


      var container = $('#testThreejs', context);
      if (container.length) {
        if (typeof THREE !== "undefined") {
          this.initTestThreeJS();
        }
        else {

        }
      }

    },

    /**
     * Simple threejs example for testing library
     */
    initTestThreeJS: function () {
      var container, helper;
      var camera, scene, renderer, controls;
      var geometry, material, mesh;

      // Get Background Color
      var background = drupalSettings.threejs.container.background_color;
      var background_color = background.replace('#', '0x') * 0x1;
      ///
      init();
      animate();

      function init() {

        // init rendered container
        container = document.getElementById('testThreejs');

        camera = new THREE.PerspectiveCamera(100, container.clientWidth / container.clientHeight, 1, 100);
        camera.position.z = 10;
        camera.position.y = 5;
        camera.position.x = 0;

        // Controls
        controls = new THREE.OrbitControls( camera, container );
        camera.lookAt(0, 0, 0);
        // controls = new THREE.FlyControls( camera );

        controls.movementSpeed = 2500;
        controls.domElement = container;
        controls.rollSpeed = Math.PI / 6;
        controls.autoForward = false;
        controls.dragToLook = false;
        // scene

        scene = new THREE.Scene();
        scene.background = new THREE.Color( background_color );

        // Grid initialization
        helper = new THREE.GridHelper( 1200, 120, 0xFF4444, 0x404040 );
        scene.add( helper );

        // Light
        var ambientLight = new THREE.AmbientLight( 0xcccccc, 1.4 );
        scene.add( ambientLight );

        var pointLight = new THREE.PointLight( 0xffffff, 0.8 );
        camera.add( pointLight );

        // Object
        geometry = new THREE.BoxGeometry(2, 2, 2);
        material = new THREE.MeshNormalMaterial();

        mesh = new THREE.Mesh(geometry, material);
        scene.add(mesh);

        renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setSize(container.clientWidth, container.clientHeight);
        container.appendChild(renderer.domElement);

        //

        // window.addEventListener( 'resize', onWindowResize, false );
      }

      // function onWindowResize() {
      //
      //   camera.aspect = container.clientWidth / container.clientHeight;
      //   camera.updateProjectionMatrix();
      //
      //   renderer.setSize( container.clientWidth / container.clientHeight );
      //
      // }

      function animate() {

        requestAnimationFrame(animate);

        mesh.rotation.x += 0.01;
        mesh.rotation.y += 0.02;
        controls.update();
        renderer.render(scene, camera);

      }
    }

  };

}(jQuery, Drupal, drupalSettings));
