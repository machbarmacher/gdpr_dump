<?php

namespace Drupal\gdpr_dump;

class ExpressionCollector implements ExpressionCollectorInterface {

  /** @var \Drupal\Core\Extension\ModuleHandlerInterface */
  private $moduleHandler;

  /**
   * GdprDumpExpressions constructor.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   */
  public function __construct(\Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler) {
    $this->moduleHandler = $moduleHandler;
  }

  public function getExpressions() {
    $expressions = $this->moduleHandler->invokeAll('gdpr_dump_expressions');
    $this->moduleHandler->alter('gdpr_dump_expressions', $expressions);
    return $expressions;
  }

}
