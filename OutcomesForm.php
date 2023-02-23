<?php

namespace Drupal\egypt_front\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\domain\DomainNegotiator;
use Drupal\Core\Form\FormBase;
use Drupal\file\Entity\File;

/**
 * Configuration Form for Front Page Settings.
 */
class OutcomesForm extends ConfigFormBase {

  /**
   * The form settings.
   *
   * @var \Drupal\Core\Form\ConfigFormBase
   */
  protected $settings;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $currentLanguage;

  /**
   * The domain negotiator.
   *
   * @var \Drupal\domain\DomainNegotiator
   */
  protected $activeDomain;

  /**
   * The entityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The FileUsageInterface service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * Constructs for front page configuration.
   *
   * @param \Drupal\Core\Form\ConfigFormBase $config_factory
   *   A configuration array containing information about the plugin instance.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   * @param \Drupal\domain\DomainNegotiator $domain_negotiator
   *   The domain negotiator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManagerInterface service.
   * @param \Drupal\file\FileUsage\FileUsageInterface $file_usage
   *   The FileUsageInterface service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LanguageManagerInterface $language_manager, DomainNegotiator $domain_negotiator, EntityTypeManagerInterface $entity_type_manager, FileUsageInterface $file_usage) {
    parent::__construct($config_factory);
    $this->settings = 'egypt_front.outcomes_form';
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();
    $this->activeDomain = $domain_negotiator->getActiveDomain()->id();
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('language_manager'),
      $container->get('domain.negotiator'),
      $container->get('entity_type.manager'),
      $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      $this->settings,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'outcomes_form';
  }

    /**
   * {@inheritdoc}
   */
    
   public function buildForm(array $form, FormStateInterface $form_state) {
    global $base_url;
    $negotiator = \Drupal::service('domain.negotiator');
    $domain = $negotiator->getActiveDomain();
    if (!empty($domain)) {
      $domain_id = $domain->id();
    }
    $header_data = $this->config($this->settings)->get("header_data_{$this->currentLanguage}");
    $language_name = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $lan = \Drupal::service('language_manager')->getStandardLanguageList();
    // $lan = array_keys($language_list);
    // foreach ($langcodesList as $key => $value) {
    //   $lan = $value->getName();
    // }
    $form['#attributes']['enctype'] = "multipart/form-data";
    $form['language'] = [
      '#type' => 'select',
      '#title' => $this->t('Language'),
      '#description' =>  $this->t('Set the Language'),
      '#options' => $lan,
      '#ajax' => [
        'callback' => 'egypt_front_ajax_eg_header_config',
        'event' => 'change',
        'progress' => ['type' => 'throbber'],
        'method' => 'replace',
        'wrapper' => 'egypt-header-section',
      ],
      '#default_value' => $lan,
    ];

//       /**
//  * Function to get loop count.
//  */
// function egypt_front_get_loop_count() {

//   return [
//     'start_loop' => 1,
//     'end_loop' => 6,
//   ];
// }

  $count = egypt_front_get_loop_count();
  $ones = [
    1 => "first",
    2 => "second",
    3 => "third",
    4 => "fourth",
    5 => "fifth",
  ];

  $form['outcomes'] = [
    '#type' => 'fieldset',
    '#title' => t('Outcomes details'),
    '#prefix' => '<div id="my-outcomes-form">',
    '#suffix' => '</div>',
  ];
  $form['outcomes']['#tree'] = TRUE;
  for ($i = $count['start_loop']; $i < $count['end_loop']; $i++) {
    $default_outcomes_dec = \Drupal::state()->get("outcomes_dec_{$language_name}_{$domain_id}_{$i}");
    $default_outcomes_title = \Drupal::state()->get("outcomes_title_{$language_name}_{$domain_id}_{$i}");
    $form['outcomes'][$i] = [
      '#type' => 'details',
      '#title' => $ones[$i] . ' ' . t('Outcomes data'),
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,

    ];
    
    $form['outcomes'][$i]['outcomes_title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#size' => 100,
      '#default_value' => !empty($default_outcomes_title) ? $default_outcomes_title : '',
      '#maxlength' => 255,
    ];
    $form['outcomes'][$i]['description'] = [
      '#type' => 'text_format',
      '#title' => t('Decription '),
      '#size' => 40,
      '#default_value' => $default_outcomes_dec,
      '#format' => 'full_html',
    ];
  }

    $form_state->setCached(FALSE);
    return parent::buildForm($form, $form_state);
  }

  /**
 * Implements hook_form_submit() for header config data.
 *
 * @param array $form
 * @param array $form_state
 */
 public function submitForm(array &$form, FormStateInterface $form_state) {
  $negotiator = \Drupal::service('domain.negotiator');
    $domain = $negotiator->getActiveDomain();
    if (!empty($domain)) {
      $domain_id = $domain->id();
      $domain_site_name = '';
    }
    $language_name = \Drupal::languageManager()->getCurrentLanguage()->getId();
    
  $count = egypt_front_get_loop_count();
  $form_values = $form_state->getValues();
  if (!empty($domain_id)) {
    for ($i = $count['start_loop']; $i < $count['end_loop']; $i++) {
      \Drupal::state()->set("outcomes_title_{$language_name}_{$domain_id}_{$i}", $form_values['outcomes'][$i]['outcomes_title']);
      \Drupal::state()->set("outcomes_dec_{$language_name}_{$domain_id}_{$i}", $form_values['outcomes'][$i]['description']['value']);
    }
    \Drupal::messenger()->addMessage($this->t("Our outcomes settings have been saved for"));
  }
  else {
    \Drupal::messenger()->addMessage($this->t("Our outcomes settings have not been saved"));
  }
}

}

