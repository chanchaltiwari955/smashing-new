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
class MainInfoForm extends ConfigFormBase {

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
    $this->settings = 'egypt_front.main_info_form';
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
    return 'main_info_form';
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

    $default_eg_mi_title = \Drupal::state()->get("eg_mi_title_{$language_name}_{$domain_id}");
    $eg_mi_desktop_image_fid = \Drupal::state()->get("eg_mi_desktop_image_{$language_name}_{$domain_id}");
    $eg_mi_mobile_image_fid = \Drupal::state()->get("eg_mi_mobile_image_{$language_name}_{$domain_id}");
    $default_eg_mi_descr = \Drupal::state()->get("eg_mi_descr_{$language_name}_{$domain_id}");

    $form['eg_main_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Egypt main info configuration'),
      '#prefix' => '<div id="egypt-main-info-section">',
      '#suffix' => '</div>',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['eg_main_info']['#tree'] = TRUE;
    $form['eg_main_info'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Egypt main info configuration'),
      '#prefix' => '<div id="egypt-main-info-section">',
      '#suffix' => '</div>',
      '#collapsible' => TRUE,
      '#collapsed' => FALSE,
    ];
    $form['eg_main_info']['#tree'] = TRUE;
    
    $form['eg_main_info']['eg_mi_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Title'),
      '#default_value' => $default_eg_mi_title,
    ];
    
    $form['eg_main_info']['eg_mi_desktop_image'] = [
      '#title' => 'Desktop image',
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded image will be displayed on home page.Uploads limited to .png .gif .jpg .jpeg extensions.<br/>Image resolution must be <strong>960*613</strong>'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_image_resolution' => ['1440x501', '1440x501'],
      ],
      '#required' => TRUE,
      '#upload_location' => 'public://egypt/mi/desktop/',
      '#default_value' => $eg_mi_desktop_image_fid,
    ];
   
    $form['eg_main_info']['eg_mi_mobile_image'] = [
      '#title' => 'Mobile image',
      '#type' => 'managed_file',
      '#description' => $this->t('The uploaded mobile image will be displayed on home page.Uploads limited to .png .gif .jpg .jpeg extensions.<br/>Image resolution must be <strong>320*530</strong>'),
      '#attributes' => ['enctype' => 'multipart/form-data'],
      '#upload_validators' => [
        'file_validate_extensions' => ['gif png jpg jpeg'],
        'file_validate_image_resolution' => ['320x530', '320x530'],
      ],
      '#required' => TRUE,
      '#upload_location' => 'public://egypt/mi/mobile/',
      '#default_value' => $eg_mi_mobile_image_fid,
      ];
    
    $form['eg_main_info']['eg_mi_descr'] = [
      '#type' => 'text_format',
      '#title' => $this->t('Please enter decription about egypt'),
      '#size' => 40,
      '#default_value' => $default_eg_mi_descr,
      '#format' => 'full_html',
    ];

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
      $form_values = $form_state->getValues();
      \Drupal::state()->set("eg_mi_title_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_title']);
      \Drupal::state()->set("eg_mi_descr_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_descr']['value']);
      $form_values = $form_state->getValues();
      \Drupal::state()->set("eg_mi_mobile_image_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_mobile_image']);
      if (!empty($form_values['eg_main_info']['eg_mi_mobile_image'])) {
        $file = File::load($form_values['eg_main_info']['eg_mi_mobile_image'][0]);
        $file->setPermanent();
        $file->save();
      }
      $form_values = $form_state->getValues();
      \Drupal::state()->set("eg_mi_desktop_image_{$language_name}_{$domain_id}", $form_values['eg_main_info']['eg_mi_desktop_image']);
      if (!empty($form_values['eg_main_info']['eg_mi_desktop_image'])) {
        $file = File::load($form_values['eg_main_info']['eg_mi_desktop_image'][0]);
        $file->setPermanent();
        $file->save();
      }
      \Drupal::messenger()->addMessage($this->t("Main info settings have been saved for"));
    }
    else {
      \Drupal::messenger()->addMessage($this->t("Main info settings have not been saved"));
    }
  }

}
