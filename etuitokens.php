<?php

require_once 'etuitokens.civix.php';

use CRM_Etuitokens_ExtensionUtil as E;

function etuitokens_civicrm_tokens(&$tokens) {
  $tokens['etui'] = [
    'etui.etui_addressee' => 'Person and/or Organisation name (addressee)',
  ];
}

function etuitokens_civicrm_tokenValues(&$details, $contactIDs, $jobID, $tokens, $className) {
  // sometimes $cids is not an array
  if (!is_array($contactIDs)) {
    $contactIDs = [$contactIDs];
  }

  // make sure one or more ETUI tokens are requested
  if (!empty($tokens['etui'])) {
    foreach ($contactIDs as $contactID) {
      CRM_Etuitokens_Helper::replaceTokens($contactID, $details);
    }
  }
}

function etuitokens_civicrm_config(&$config): void {
  _etuitokens_civix_civicrm_config($config);
}

function etuitokens_civicrm_install(): void {
  _etuitokens_civix_civicrm_install();
}

function etuitokens_civicrm_enable(): void {
  _etuitokens_civix_civicrm_enable();
}
