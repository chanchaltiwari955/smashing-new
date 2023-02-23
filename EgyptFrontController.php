<?php

namespace Drupal\egypt_front\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\file\Entity\File;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Defines EyptFrontController class.
 */
class EgyptFrontController extends ControllerBase {
  public function egypt_front_configured_data() {
    global $base_url;
    $module_path = \Drupal::service('module_handler')->getModule('egypt_front')->getPath();
    $negotiator = \Drupal::service('domain.negotiator');
    $domain = $negotiator->getActiveDomain();
    if (!empty($domain)) {
      $domain_id = $domain->id();
    }
    $language_name = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $lan = \Drupal::service('language_manager')->getStandardLanguageList();
    $data = [];

    // Header data.
    $header_eg_title = \Drupal::state()->get("eg_title_{$language_name}_{$domain_id}");
    $header_eg_subtitle = \Drupal::state()->get("eg_subtitle_{$language_name}_{$domain_id}");
    $eg_desktop_image_fid = \Drupal::state()->get("eg_desktop_image_{$language_name}_{$domain_id}");

    if (!empty($eg_desktop_image_fid[0])) {
      $desktop_file = File::load($eg_desktop_image_fid[0]);
      $desktop_url = $desktop_file->uri[0]->value;
    }
    $eg_mobile_image_fid = \Drupal::state()->get("eg_mobile_image_{$language_name}_{$domain_id}");
    if (!empty($eg_mobile_image_fid[0])) {
      $mobile_file = File::load($eg_mobile_image_fid[0]);
      $mobile_url = $mobile_file->uri[0]->value;
    }
    $data['header_eg_title'] = isset($header_eg_title) ? $header_eg_title : '';
    $data['header_eg_subtitle'] = isset($header_eg_subtitle) ? $header_eg_subtitle : '';
    $data['desktop_url'][0] = isset($desktop_url) ? $desktop_url : '';
    $data['mobile_url'][0] = isset($mobile_url) ? $mobile_url : '';

    // Outcomes data.
    $count = egypt_front_get_loop_count();
    $outcome = [];
    for ($i = $count['start_loop']; $i < $count['end_loop']; $i++) {
      $title = \Drupal::state()->get("outcomes_title_{$language_name}_{$domain_id}_{$i}");

      $description = \Drupal::state()->get("outcomes_dec_{$language_name}_{$domain_id}_{$i}");
      if (!empty($title) && !empty($description)) {
        $outcome[$i]['title'] = $title;
        $outcome[$i]['description'] = $description;
      }

    }
    // $data['outcomes'] = $outcome;

    // Main info data.
    $mi_title = \Drupal::state()->get("eg_mi_title_{$language_name}_{$domain_id}");
    $mi_descr = \Drupal::state()->get("eg_mi_descr_{$language_name}_{$domain_id}");
    $mi_desktop_image_fid = \Drupal::state()->get("eg_mi_desktop_image_{$language_name}_{$domain_id}");
    $mi_mobile_image_fid = \Drupal::state()->get("eg_mi_mobile_image_{$language_name}_{$domain_id}");
    if (!empty($mi_desktop_image_fid[0])) {
      $mi_desktop_file = File::load($mi_desktop_image_fid[0]);
      $mi_desktop_url = $mi_desktop_file->uri[0]->value;
    }
    if (!empty($mi_mobile_image_fid[0])) {
      $mi_mobile_file = File::load($mi_mobile_image_fid[0]);
      $mi_mobile_url = $mi_mobile_file->uri[0]->value;
    }
    $data['mi_title'] = isset($mi_title) ? $mi_title : '';
    $data['mi_descr'] = isset($mi_descr) ? $mi_descr : '';
    $data['mi_desktop_url'][0] = isset($mi_desktop_url) ? $mi_desktop_url : '';
    $data['mi_mobile_url'][0] = isset($mi_mobile_url) ? $mi_mobile_url : '';
    
    return [
      '#theme' => 'page_EU_support_to_egypt',
      '#data' => $data,
      '#outcome' => $outcome,
      '#module_path' => $module_path,
      '#attached' => [
        'library' => [
          'egypt_front/egypt_front_library',
        ],
      ]
    ];
  }
}