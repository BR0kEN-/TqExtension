<?php
// @codingStandardsIgnoreFile

/**
 * @file
 * Page with JavaScript errors.
 */

/**
 * {@inheritdoc}
 *
 * @see tqextension_menu()
 */
function tqextension_page_js_errors() {
  $page = [];

  $page['#attached']['js'][] = [
    'data' => 'console.l0g(12)',
    'type' => 'inline',
  ];

  return $page;
}
