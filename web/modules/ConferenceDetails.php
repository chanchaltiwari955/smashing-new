<?php

namespace Drupal\unfpa_offices_conferences\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Provides the Data for Conference detail page.
 */
class ConferenceDetails extends ControllerBase {

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public function __construct(CurrentRouteMatch $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('language_manager'), $container->get('current_route_match'));
  }

  /**
   * Function to get conference details page data.
   *
   * @return node
   *   node data for conference details twig template.
   */
  public function unfpa_offices_conferences_detail($node) {
    $data = [];
    $node = $this->routeMatch->getParameter('node');
    if ($node->getType() == 'conference') {
      $menu_details_nid = !empty($node->hasField('field_menu_details')) ? $node->get('field_menu_details')->getValue() : '';
      foreach ($menu_details_nid as $menu_value) {
        $wrapper = Paragraph::load($menu_value['target_id']);
        $data['tab_title'] = $wrapper->get('field_menu_display_name')->value;
        $data['summary'] = $wrapper->get('field_description')->getValue()[0]['value'];
      }
    }
    return [
      '#theme' => 'conferences_detail',
      '#data' => $data,
      '#attached' => [
        'library' => ['unfpa_offices_conferences/unfpa_offices_conference_style'],
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

}
