<?php

namespace Netlify\Controller;

use Cockpit\AuthController;

/**
 * Admin controller class.
 */
class Admin extends AuthController
{

  /**
   * Default index controller.
   */
  public function index()
  {
    if (!$this->app->module('cockpit')->hasaccess('netlify', 'manage.view')) {
      return false;
    }

    $data = $this->app->module('netlify')->fetchDeploys();

    return $this->render('netlify:views/deploys/index.php', [
      'deploys' => $data['deploys'],
      'building' => $data['building'],
    ]);
  }

}
