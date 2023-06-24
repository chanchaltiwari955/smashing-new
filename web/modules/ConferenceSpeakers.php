<?php

namespace Drupal\unfpa_offices_conferences\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Provides the Data for Conference Speakers page.
 */
class ConferenceSpeakers extends ControllerBase {

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
    return new static($container->get('current_route_match'));
  }

  /**
   * Function to get conference speakers page data.
   *
   * @return node
   *   node data for conference speakers twig template.
   */
  public function unfpa_offices_conferences_speakers($node) {
    $data = [];
    $data['speaker_summary'] = '';
    $node = $this->routeMatch->getParameter('node');
    if ($node->getType() == 'conference') {
      $data['speaker_summary'] = !empty($node->get('field_speaker_summary')) ? $node->get('field_speaker_summary')->value : '';
    }
    return [
      '#theme' => 'conferences_speakers',
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
