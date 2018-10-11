/**
 * @file
 * Three.js behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.threejs = {
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
      var container;
      var camera, scene, renderer;
      var geometry, material, mesh;

      init();
      animate();

      function init() {

        // init rendered container
        container = document.getElementById('testThreejs');

        camera = new THREE.PerspectiveCamera(30, container.clientWidth / container.clientHeight, 0.01, 10);
        camera.position.z = 1;

        scene = new THREE.Scene();

        geometry = new THREE.BoxGeometry(0.2, 0.2, 0.2);
        material = new THREE.MeshNormalMaterial();

        mesh = new THREE.Mesh(geometry, material);
        scene.add(mesh);

        renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setSize(container.clientWidth, container.clientHeight);
        container.appendChild(renderer.domElement);

      }

      function animate() {

        requestAnimationFrame(animate);

        mesh.rotation.x += 0.01;
        mesh.rotation.y += 0.02;

        renderer.render(scene, camera);

      }
    }

  };

}(jQuery, Drupal));
