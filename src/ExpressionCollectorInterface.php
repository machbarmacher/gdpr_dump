<?php

namespace Drupal\gdpr_dump;

interface ExpressionCollectorInterface {
  /**
   * @return array
   */
  public function getExpressions();
}