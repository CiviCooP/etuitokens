<?php

class CRM_Etuitokens_Helper {

  public static function replaceTokens($contactID, &$details) {
    // etui_addressee is in most cases just the display name...
    $details[$contactID]['etui.etui_addressee'] = self::getDisplayName($contactID, $details);

    // ...except for individuals with an address referring to a company
    if (self::isIndividual($contactID, $details) && self::hasAddressId($contactID, $details)) {
      $addressDao = self::getAddress($contactID, $details);
      if (self::isAddressOrganization($addressDao)) {
        $details[$contactID]['etui.etui_addressee'] = self::getEmployerAndEmployeeName($addressDao);
      }
    }
  }

  private static function isIndividual($contactID, &$details) {
    if ($details[$contactID]['contact_type'] == 'Individual') {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function hasAddressId($contactID, &$details) {
    if (!empty($details[$contactID]['address_id'])) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  private static function isAddressOrganization($addressDao) {
    if (empty($addressDao)) {
      return FALSE;
    }

    if ($addressDao->location_type_id == 1) {
      return FALSE; // home address type
    }

    if ($addressDao->location_type_id == 9) {
      return FALSE; // magazine home address type
    }

    return TRUE;
  }

  private static function getDisplayName($contactID, &$details) {
    return $details[$contactID]['display_name'];
  }

  private static function getEmployerAndEmployeeName($addressDao) {
    // name of the employee
    $addressee = $addressDao->addressee_display;

    // add the name of the correct organization
    // (if the address is linked to a contact, it gets precedence over the current employer
    if ($addressDao->master_organization_name) {
      $addressee .= '<br>' . $addressDao->master_organization_name;
    }
    elseif ($addressDao->employer_name) {
      $addressee .= '<br>' . $addressDao->employer_name;
    }

    return $addressee;
  }

  private static function getAddress($contactID, &$details) {
    $sql = "
      select
        c.organization_name employer_name
        , c.addressee_display
        , a.location_type_id
        , c_master.display_name master_organization_name
      from
        civicrm_contact c
      left outer join
        civicrm_address a ON a.contact_id = c.id and a.id = %2
      left outer join
        civicrm_address a_master ON a.master_id = a_master.id
      left outer join
        civicrm_contact c_master ON c_master.id = a_master.contact_id
      where
      c.id = %1
    ";
    $sqlParams = [
      1 => [$contactID, 'Integer'],
      2 => [$details[$contactID]['address_id'], 'Integer'],
    ];
    $dao = CRM_Core_DAO::executeQuery($sql, $sqlParams);
    if ($dao->fetch()) {
      return $dao;
    }
    else {
      return NULL;
    }
  }

}
