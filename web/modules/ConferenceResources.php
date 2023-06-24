<?php

namespace Drupal\unfpa_offices_conferences\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

/**
 * Provides the Data for Conference Resources page.
 */
class ConferenceResources extends ControllerBase {

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
   * Function to get conference resources page data.
   *
   * @return node
   *   node data for conference resources twig template.
   */
  public function unfpa_offices_conferences_resources($node) {
    $node = $this->routeMatch->getParameter('node');
    if ($node->getType() == 'conference') {
    }
    return [
      '#theme' => 'conferences_resources',
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
