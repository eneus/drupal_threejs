/**
 * @file
 * TestThreeJS behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Behavior description.
   */
  Drupal.behaviors.ThreeJSinit = {
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

      var container = $('#canvas-container', context);
      if (container.length) {
        if (typeof THREE !== "undefined") {
          this.renderThreeJSField();
        }
        else {

        }
      }

    },

    /**
     * Simple threejs example for testing library
     */
    renderThreeJSField: function () {

      if (!Detector.webgl) Detector.addGetWebGLMessage();

      var container, model, stats, clock, controls;
      var camera, scene, renderer, mixer;

      init();
      animate();

      function init() {

        // init rendered container
        container = document.getElementById('canvas-container');
        // Get file Url from 'data-model' attribute
        // var model = $(container).attr("data-model"); // jQuery
        model = container.getAttribute('data-model');

        // Camera Settings
        camera = new THREE.PerspectiveCamera(25, container.clientWidth / container.clientHeight, 1, 10000);
        camera.position.set(15, 10, -15);

        // Scene settings
        scene = new THREE.Scene();

        clock = new THREE.Clock();

        // collada

        var loader = new THREE.ColladaLoader();
        loader.load( model, function (collada) {

          var animations = collada.animations;
          var avatar = collada.scene;

          mixer = new THREE.AnimationMixer(avatar);
          var action = mixer.clipAction(animations[0]).play();

          scene.add(avatar);

        });

        // Grid in scene

        var gridHelper = new THREE.GridHelper(10, 20);
        scene.add(gridHelper);

        // Light

        var ambientLight = new THREE.AmbientLight(0xffffff, 0.2);
        scene.add(ambientLight);

        var directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
        directionalLight.position.set(1, 1, -1);
        scene.add(directionalLight);

        // Rendering

        renderer = new THREE.WebGLRenderer({antialias: true});
        renderer.setPixelRatio(window.devicePixelRatio);
        renderer.setSize(container.clientWidth, container.clientHeight);
        container.appendChild(renderer.domElement);

        // Controls

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.target.set(0, 2, 0);
        camera.lookAt(0, 0, 0);
        controls.update();

        // Statistic

        stats = new Stats();
        container.appendChild(stats.dom);

        // Resize container on fly

        window.addEventListener('resize', onWindowResize, false);

      }

      function onWindowResize() {

        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();

        renderer.setSize(container.clientWidth, container.clientHeight);

      }

      function animate() {

        requestAnimationFrame(animate);

        render();
        stats.update();

      }

      function render() {

        var delta = clock.getDelta();
        if (mixer !== undefined) {
          mixer.update(delta);
        }

        renderer.render(scene, camera);

      }

    }
  };

}(jQuery, Drupal));
