<?php

namespace Drupal\gdpr_dump\Commands;

use Consolidation\AnnotatedCommand\AnnotationData;
use Symfony\Component\Console\Input\InputInterface;

class DumpEnhancer {

  /**
   * Init the sql-dump command.
   *
   * @todo Also sql-sync, when it recognizes --extra-dump
   * @hook init sql:dump
   * @option $gdpr Make a gdpr compliant db dump or sync by obfuscating data.
   * @default $gdpr false
   *
   * @throws \Exception
   */
  public function initializeDump(InputInterface $input, AnnotationData $annotationData) {
    if ($input->getOption('gdpr')) {
      // Bootstrap
      $success = drush_bootstrap_to_phase(DRUSH_BOOTSTRAP_DRUPAL_FULL);
      if (!$success) {
        throw new \Exception('Can not bootstrap drupal, but --gdpr needs this.');
      }
      // Ask API.
      /** @var \Drupal\gdpr_dump\GdprDumpExpressionCollectorInterface $dumpExpressionCollector */
      $dumpExpressionCollector = \Drupal::service('gdpr.dump-expression-collector');
      $expressions = $dumpExpressionCollector->getExpressions();
      $extraDumpOption = escapeshellarg(json_encode($expressions));
      // Append to prior extraDumpOption-dump option.
      if ($input->getOption('extra-dump')) {
        $extraDumpOption = $input->getOption('extra-dump') . ' ' . $extraDumpOption;
      }
      $input->setOption('extra-dump', $extraDumpOption);
    }
  }
}
