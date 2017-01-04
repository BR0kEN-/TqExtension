<?php
// @codingStandardsIgnoreFile

namespace Drupal\tqextension\Controller;

use Drupal\Core\Controller\ControllerBase;

class Pages extends ControllerBase {

  public function jsErrors() {
    $page = [];

    $page['#attached']['html_head'][] = [
      [
        '#tag' => 'script',
        '#type' => 'html_tag',
        '#value' => 'console.l0g(12)',

      ],
      'js-errors',
    ];

    return $page;
  }

}
