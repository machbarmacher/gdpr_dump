<?php

namespace Drupal\gdpr_dump;

interface GdprDumpExpressionCollectorInterface {
  /**
   * @return array
   */
  public function getExpressions();
}