<?php

namespace Drupal\unfpa_offices_conferences\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;

/**
 * Provides the Data for conference programme template.
 */
class ConferenceProgramme extends ControllerBase {

  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * {@inheritdoc}
   */
  public function __construct(CurrentRouteMatch $route_match, EntityTypeManagerInterface $entity_type_manager, FileUrlGeneratorInterface $file_url_generator) {
    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    $this->nodeStorage = $this->entityTypeManager->getStorage('node');
    $this->fileUrlGenerator = $file_url_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('current_route_match'), $container->get('entity_type.manager'), $container->get('file_url_generator'));
  }

  /**
   * Function to get programme page data.
   *
   * @return node
   *   return node data for programme twig template.
   */
  public function unfpa_offices_conferences_programme($node) {
    $data = [];
    $node = $this->routeMatch->getParameter('node');
    $fid = !empty($node->hasField('field_document_upload')) ? $node->get('field_document_upload')->getValue() : '';
    foreach ($fid as $key => $value) {
      $DocData = Paragraph::load($value['target_id']);
      $pdf_uri = !empty($DocData->get('field_event_pdf_upload')->entity) ? $DocData->get('field_event_pdf_upload')->entity->getFileUri() : '';
      $data['doc'][$key]['language_doc'] = !empty($pdf_uri) ? $this->fileUrlGenerator->generateAbsoluteString($pdf_uri) : '';
      $lid = !$DocData->get('field_available_language')->isEmpty() ? $DocData->get('field_available_language')->getValue()[0]['target_id'] : '';
      $languageData = $this->nodeStorage->load($lid);
      $data['doc'][$key]['language'] = !empty($languageData->field_language_code) ? $languageData->get('field_language_code')->value : '';
    }
    if ($node->getType() == 'conference') {
      $data['programme_body'] = !$node->get('field_programme_body')->isEmpty() ? trim($node->get('field_programme_body')->value) : '';
      // PROVISIONAL PROGRAMME.
      $provisional_programme_fc = $node->hasField('field_provisional_programme') ? $node->get('field_provisional_programme')->getValue() : '';
      $provisional_programme_details = [];
      $i = 1;
      foreach ($provisional_programme_fc as $value) {
        $provisional_programme_details[$value['target_id']]['current_tab'] = $i == 1 ? 'current' : '';
        $provisional_programme_details[$value['target_id']]['alphabet_count'] = $this->getAlphabetCount($i);
        $provisional_programme_details[$value['target_id']]['provisional_day_count'] = $i;
        $provisional_programme_wrapper = Paragraph::load($value['target_id']);
        $provisional_programme_details[$value['target_id']]['main_heading'] = !empty($provisional_programme_wrapper->get('field_main_heading')) ? $provisional_programme_wrapper->get('field_main_heading')->value : '';
        $programme_content_fc = $provisional_programme_wrapper->field_provisional_programme_cont->getValue();
        if (is_array($programme_content_fc) && count($programme_content_fc) > 0) {
          foreach ($programme_content_fc as $key => $value_child) {
            $programme_content = Paragraph::load($value_child['target_id']);
            $provisional_programme_details[$value['target_id']]['content'][$key]['description'] = !empty($programme_content->get('field_provisional_description')) ? $programme_content->get('field_provisional_description')->value : '';
            $provisional_programme_details[$value['target_id']]['content'][$key]['time'] = !empty($programme_content->field_time) ? $programme_content->get('field_time')->value : '';
            $provisional_programme_details[$value['target_id']]['content'][$key]['sub_title'] = !empty($programme_content->field_sub_title) ? $programme_content->get('field_sub_title')->value : '';
            $provisional_programme_details[$value['target_id']]['content'][$key]['title'] = !empty($programme_content->field_provisional_title) ? $programme_content->get('field_provisional_title')->value : '';
          }
        }
        $i++;
      }
      $data['provisional_programme_details'] = $provisional_programme_details;
      $data['document_title'] = !empty($node->field_document_title) ? $node->get('field_document_title')->value : $this->t('Download Agenda');
    }
    return [
      '#theme' => 'conferences_programme',
      '#data' => $data,
      '#attached' => [
        'library' => [
          'unfpa_offices_conferences/unfpa_offices_conference_style',
        ],
      ],
      '#cache' => [
        'tags' => [
          'node_list',
        ],
        'contexts' => [
          'languages',
        ],
      ],
    ];
  }

  /**
   * Function to get alphabet list value.
   */
  public function getAlphabetCount($i) {
    $alphabet_list = [
      '1' => $this->t('One'),
      '2' => $this->t('Two'),
      '3' => $this->t('Three'),
      '4' => $this->t('Four'),
      '5' => $this->t('Five'),
      '6' => $this->t('Six'),
    ];

    return $alphabet_list[$i];
  }

}
