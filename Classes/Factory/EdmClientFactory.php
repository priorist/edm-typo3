<?php

namespace Priorist\EdmTypo3\Factory;

use Priorist\EDM\Client\Client;

class EdmClientFactory 
{
  static function create() {
    $url = ''; // $settings['edm']['url'];
		$clientId = ''; // $settings['edm']['auth']['anonymous']['clientId'];
		$clientSecret = ''; //$settings['edm']['auth']['anonymous']['clientSecret'];

    return new Client($url, $clientId, $clientSecret);
  }
}