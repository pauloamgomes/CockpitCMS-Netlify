<?php

namespace Netlify\Controller;

use \LimeExtra\Controller;

/**
 * RestApi class for dealing with sscore api requests.
 */
class RestApi extends Controller {

  protected $settings = [];

  public function __construct($app) {
    parent::__construct($app);
    $this->settings = $app->config['netlify'] ?? ['site_id' => '', 'branch' => ''];
  }

  /**
   * Netlify success webhook.
   */
  public function success() {
    if ($this->validate()) {
      $this->app->trigger("netlify.build.success");
      return ['status' => TRUE];
    }
    return ['status' => FALSE];
  }

  /**
   * Netlify error webhook.
   */
  public function error() {
    if ($this->validate()) {
      $this->app->trigger("netlify.build.error");
      return ['status' => TRUE];
    }
    return ['status' => FALSE];
  }

  protected function validate() {
    $site_id = $this->param('site_id', FALSE);
    $branch = $this->param('branch', FALSE);

    if ($branch !== $this->settings['branch'] || $site_id !== $this->settings['site_id']) {
      return FALSE;
    }

    return TRUE;
  }

}
