<?php

/**
 * Declare sql expressions to obfuscate on dump.
 *
 * @return array
 *   An array of sql expressions, keyed by table and column.
 */
function hook_gdpr_dump_expressions() {
  return [
    'user_field_data' => [
      // Copy the primary key to all columns that might carry a unique index.
      'user' => 'uid',
      'mail' => 'uid',
      'init' => 'uid',
      'pass' => '""',
    ],
  ];
}
