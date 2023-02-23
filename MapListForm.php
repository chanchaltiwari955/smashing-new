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
class MapListForm extends ConfigFormBase {

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
    $this->settings = 'egypt_front.map_list_form';
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
    return 'map_list_form';
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
    $default_country_list_title = \Drupal::state()->get("country_list_title_{$language_name}_{$domain_id}");
    $default_country_list_decription = \Drupal::state()->get("country_list_decription_{$language_name}_{$domain_id}");

    $form['egypt_map'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Egypt Map country list data'),
      '#prefix' => '<div id="egypt-map-country-form">',
      '#suffix' => '</div>',
    ];
    $form['egypt_map']['#tree'] = TRUE;

    $form['egypt_map']['country_list_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Country List Title'),
      '#size' => 40,
      '#default_value' => $default_country_list_title,
    ];
    $form['egypt_map']['country_list_decription'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Decription about country map data'),
      '#size' => 40,
      '#default_value' => $default_country_list_decription,
      '#format' => 'full_html',
    ];
    $map_country_list = egypt_front_map_country_list();
    foreach ($map_country_list as $key => $val) {
      $default_eg_country_desription = \Drupal::state()->get("eg_country_desription_{$language_name}_{$domain_id}_{$key}");
      $default_eg_country_title = \Drupal::state()->get("eg_country_title_{$language_name}_{$domain_id}_{$key}");
      $default_eg_country_url = \Drupal::state()->get("eg_country_url_{$language_name}_{$domain_id}_{$key}");
      $default_eg_country_enable = \Drupal::state()->get("eg_country_enable_{$language_name}_{$domain_id}_{$key}");
      $form['egypt_map'][$key] = [
        '#type' => 'details',
        '#title' => $val . ' ' . $this->t('Regions'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      ];
      $form['egypt_map'][$key]['eg_country_title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Country title'),
        '#size' => 40,
        '#default_value' => $default_eg_country_title,
      ];
      $form['egypt_map'][$key]['eg_country_desription'] = [
        '#type' => 'text_format',
        '#title' => $this->t('Please enter description for ') . $val,
        '#size' => 40,
        '#default_value' => $default_eg_country_desription,
        '#format' => 'full_html',
      ];
      $form['egypt_map'][$key]['eg_country_url'] = [
        '#type' => 'textfield',
        '#title' => $this->t('URL'),
        '#size' => 40,
        '#default_value' => $default_eg_country_url,
      ];
      $form['egypt_map'][$key]['eg_country_enable'] = [
        '#type' => 'select',
        '#title' => $this->t('Select country for enable'),
        '#options' => [
          0 => t('No'),
          1 => t('Yes'),
        ],
        '#default_value' => $default_eg_country_enable,
        '#description' => $this->t('Set this to <em>Yes</em> if you would like this country enable on map.'),
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
public function submitForm(array &$form, FormStateInterface $form_state){
  $negotiator = \Drupal::service('domain.negotiator');
    $domain = $negotiator->getActiveDomain();
    if (!empty($domain)) {
      $domain_id = $domain->id();
      $domain_site_name = '';
    }
    $language_name = \Drupal::languageManager()->getCurrentLanguage()->getId();
    $form_values = $form_state->getValues();
  if (!empty($domain_id)) {
    $map_country_list = egypt_front_map_country_list();
    $form_values = $form_state->getValues();
    foreach ($map_country_list as $key => $val) {

       \Drupal::state()->set("eg_country_desription_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_desription']['value']);
       \Drupal::state()->set("eg_country_title_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_title']);
       \Drupal::state()->set("eg_country_url_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_url']);
       \Drupal::state()->set("eg_country_enable_{$language_name}_{$domain_id}_{$key}", $form_values['egypt_map'][$key]['eg_country_enable']);
    }
     \Drupal::state()->set("country_list_title_{$language_name}_{$domain_id}", $form_values['egypt_map']['country_list_title']);
     \Drupal::state()->set("country_list_decription_{$language_name}_{$domain_id}", $form_values['egypt_map']['country_list_decription']['value']);

    \Drupal::messenger()->addMessage($this->t("The settings have been saved for"));
  }
  else {
    \Drupal::messenger()->addMessage($this->t("The settings have not been saved"));
  }
}


}

