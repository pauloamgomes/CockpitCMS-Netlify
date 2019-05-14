<?php

/**
 * @file
 * Addon admin functions.
 */

 // Module ACL definitions.
$this("acl")->addResource('netlify', [
  'manage.view',
  'manage.deploy',
]);

$app->on('admin.init', function () use ($app) {
  // Bind admin routes.
  $this->bindClass('Netlify\\Controller\\Admin', 'netlify/deploys');

  if ($app->module('cockpit')->hasaccess('netlify', 'manage.view')) {
    // Add to modules menu.
    $this('admin')->addMenuItem('modules', [
      'label' => 'Netlify Deploys',
      'icon' => 'netlify:icon.svg',
      'route' => '/netlify/deploys',
      'active' => strpos($this['route'], '/netlify/deploys') === 0,
    ]);
  }
});
