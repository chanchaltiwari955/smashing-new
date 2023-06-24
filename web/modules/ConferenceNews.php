<?php

namespace Drupal\unfpa_offices_conferences\Controller;

use Drupal\node\NodeInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\pathauto\AliasCleanerInterface;

/**
 * Provides the Data for Conference News page.
 */
class ConferenceNews extends ControllerBase {

  /**
   * The RequestStack services.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The access manager service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected $routeMatch;

  /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPathStack;

  /**
   * The alias cleaner.
   *
   * @var \Drupal\pathauto\AliasCleanerInterface
   */
  protected $aliasCleaner;

  /**
   * {@inheritdoc}
   */
  public function __construct(CurrentRouteMatch $route_match, CurrentPathStack $current_path, AliasCleanerInterface $pathauto_alias_cleaner) {
    $this->routeMatch = $route_match;
    $this->currentPathStack = $current_path;
    $this->aliasCleaner = $pathauto_alias_cleaner;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_route_match'),
      $container->get('path.current'),
      $container->get('pathauto.alias_cleaner')
    );
  }

  /**
   * Function to get conference news page data.
   *
   * @return node
   *   node data for conference news twig template.
   */
  public function unfpa_offices_conferences_news($node) {
    $data = [];
    $node = $this->routeMatch->getParameter('node');
    if ($node instanceof NodeInterface) {
      if ($node->getType() == 'conference') {
        $menu_details_nid = !empty($node->hasField('field_menu_details')) ? $node->get('field_menu_details')->getValue() : '';
        foreach ($menu_details_nid as $menu_value) {
          $wrapper = Paragraph::load($menu_value['target_id']);
          $current_path = $this->currentPathStack->getPath();
          $arg = explode('/', $current_path);
          $menu_slug = $arg[4];
          $display_title = $wrapper->get('field_menu_display_name')->value;
          $title_slug = $this->aliasCleaner->cleanString($display_title);
          if ($menu_slug == $title_slug) {
            $tag_target = $wrapper->field_related_tags->referencedEntities();
            $target_term_id = [];
            foreach ($tag_target as $value) {
              $target_term_id[] = $value->get('tid')->value;
            }
            $default_list = trim($wrapper->get('field_list_type')->value);
            $data['target_term_id'] = implode('+', $target_term_id);
            $data['tab_title'] = $display_title;
          }
        }
      }
    }
    $data['default_list'] = $default_list != NULL ? $default_list : 'list_view';
    return [
      '#theme' => 'conferences_news',
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
