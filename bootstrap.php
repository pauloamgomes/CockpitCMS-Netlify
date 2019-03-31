<?php

/**
 * @file
 * Implements bootstrap functions.
 */

// Include addon functions only if its an admin request.
if (COCKPIT_ADMIN && !COCKPIT_API_REQUEST) {
  // Extend addon functions.
  $this->module('netlify')->extend([
    'fetchDeploys' => function ($limit = 50) {
      $settings = $this->app->config['netlify'] ?? FALSE;

      if (!$settings || !isset($settings['api_url'],
      $settings['site_id'],
      $settings['access_token'])) {
        return [];
      }

      $url = $settings['api_url'] . '/sites/' . $settings['site_id'] . '/deploys' . '?access_token=' . $settings['access_token'];

      if (!empty($settings['branch'])) {
        $url .= '&branch=' . $settings['branch'];
      }

      $headers = [
        'Content-Type: application/json',
      ];

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

      $deploys = curl_exec($ch);
      curl_close($ch);
      $deploys = json_decode($deploys);
      if ($deploys && is_array($deploys)) {
        $deploys = array_slice($deploys, 0, $limit);
      } else {
        $deploys = [];
      }

      // Parse dates and check if any deploy is on building status.
      $building = false;
      foreach ($deploys as $idx => $deploy) {
        $deploys[$idx]->building = false;
        if (in_array($deploy->state, ['building', 'enqueued', 'uploaded', 'uploaded', 'uploading', 'processing'])) {
          $building = true;
          $deploys[$idx]->building = true;
        }
        $deploys[$idx]->created_at = date('Y-m-d H:i', strtotime($deploy->created_at));
        $deploys[$idx]->updated_at = date('Y-m-d H:i', strtotime($deploy->updated_at));
      }

      return [
        'deploys' => $deploys,
        'building' => $building,
      ];
    },

    'createDeploy' => function () {
      $settings = $this->app->config['netlify'];
      $url = $settings['build_hook_url'];

      $data = json_encode([]);

      $headers = [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($data),
      ];

      $ch = curl_init($url);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $result = curl_exec($ch);
      curl_close($ch);

      return json_decode($result);
    },
  ]);

  // Include admin.
  include_once __DIR__ . '/admin.php';
}
