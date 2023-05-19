<?php

namespace Drupal\unfpa_co_domain_site_settings\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Routing\RequestContext;
use Drupal\domain\DomainInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\Element;

/**
 * Save domain config settings.
 *
 * @package Drupal\unfpa_co_domain_site_settings\Form
 */
class DomainConfigSettingsForm extends ConfigFormBase {

  /**
   * The path alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

    /**
   * The Current Path variable.
   *
   * @var \Drupal\Core\Language\LanguageManager
   */
  protected $languageManager;

   /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $currentLanguage;

  /**
   * The request context.
   *
   * @var \Drupal\Core\Routing\RequestContext
   */
  protected $requestContext;

  /**
   * Constructs a SiteInformationForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\path_alias\AliasManagerInterface $alias_manager
   *   The path alias manager.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator.
   * @param \Drupal\Core\Routing\RequestContext $request_context
   *   The request context.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, AliasManagerInterface $alias_manager, PathValidatorInterface $path_validator, RequestContext $request_context, LanguageManagerInterface $language_manager) {
    parent::__construct($config_factory);

    $this->aliasManager = $alias_manager;
    $this->pathValidator = $path_validator;
    $this->requestContext = $request_context;
    $this->languageManager = $language_manager;
    $this->currentLanguage = $language_manager->getCurrentLanguage()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
        $container->get('config.factory'), $container->get('path_alias.manager'), $container->get('path.validator'), $container->get('router.request_context'), $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'unfpa_co_domain_site_settings.domainconfigsettings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'domain_config_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, DomainInterface $domain = NULL) {
    $languages = $this->languageManager->getStandardLanguageList();
    $config = $this->config('unfpa_co_domain_site_settings.domainconfigsettings');
    $domain_id = $domain->id();
    $site_config = $this->config('system.site');
    $site_mail = $site_config->get('mail');
    if (empty($site_mail)) {
      $site_mail = ini_get('sendmail_from');
    }
    if ($config->get($domain_id) != NULL) {
      $site_mail = $config->get($domain_id . '.site_mail');
    }
    $option = [];
    foreach($languages as $key =>$value){
     $option[$key] = $value[0];
    }
    
    $form['site_information'] = [
      '#type' => 'details',
      '#title' => $this->t('Site details'),
      '#open' => TRUE,
    ];
    $form['site_information']['site_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site name'),
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.site_name') : $site_config->get('name'),
      '#required' => TRUE,
    ];
    $form['site_information']['site_slogan'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Slogan'),
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.site_slogan') : $site_config->get('slogan'),
      '#description' => $this->t("How this is used depends on your site's theme."),
    ];
    $form['site_information']['site_mail'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#default_value' => $site_mail,
      '#description' => $this->t("The <em>From</em> address in automated emails sent during registration and new password requests, and other notifications. (Use an address ending in your site's domain to help prevent this email being flagged as spam.)"),
      '#required' => TRUE,
    ];
    $form['front_page'] = [
      '#type' => 'details',
      '#title' => $this->t('Front page'),
      '#open' => TRUE,
    ];
    $front_page = $site_config->get('page.front') != '/user/login' ? $this->aliasManager->getAliasByPath($site_config->get('page.front')) : '';
    $front_page = ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.site_frontpage') : $front_page;
    $form['front_page']['site_frontpage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default front page'),
      '#default_value' => $front_page,
      '#size' => 40,
      '#description' => $this->t('Optionally, specify a relative URL to display as the front page. Leave blank to display the default front page.'),
      '#field_prefix' => $this->requestContext->getCompleteBaseUrl(),
    ];
    $form['error_page'] = [
      '#type' => 'details',
      '#title' => $this->t('Error pages'),
      '#open' => TRUE,
    ];
    $form['error_page']['site_403'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default 403 (access denied) page'),
      '#default_value' => ($config->get($domain_id) !== NULL) ? $config->get($domain_id . '.site_403') : $site_config->get('page.403'),
      '#size' => 40,
      '#description' => $this->t('This page is displayed when the requested document is denied to the current user. Leave blank to display a generic "access denied" page.'),
    ];
    $form['error_page']['site_404'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default 404 (not found) page'),
      '#default_value' => ($config->get($domain_id) !== NULL) ? $config->get($domain_id . '.site_404') : $site_config->get('page.404'),
      '#size' => 40,
      '#description' => $this->t('This page is displayed when no other content matches the requested document. Leave blank to display a generic "page not found" page.'),
    ];
    $form['domain_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Domain ID'),
      '#default_value' => $domain_id,
    ];

    $form['gtm_code'] = [
      '#type' => 'details',
      '#title' => $this->t('GTM Code'),
      '#open' => TRUE,
    ];
    $value = $config;
    $form['gtm_code']['gtm_code_field'] = [
      '#type' => 'textfield',
      '#title' => $this->t('GTM Code'),
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.gtm_code_field') : $site_config->get('gtm code'),
      '#description' => $this->t('This ID is unique to each site you want to track separately, and is in the form of GTM-xxxxxxx.'),
    ];

    $form['site_info'] = [
      '#type' => 'details',
      '#title' => $this->t('Site Information'),
      '#open' => TRUE,
    ];
    $form['site_info']['site_info_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site name'),
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.site_info_name') : $site_config->get('name'),
      '#required' => TRUE,
      '#description' => $this->t('The name of this website.'),
    ];
    $form['site_info']['site_info_email'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Site email address'),
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.site_info_email') : $site_config->get('Site email'),
      '#required' => TRUE,
      '#description' => $this->t('The From address in automated e-mails sent during registration and new password requests, and other notifications. (Use an address ending in your site domain to help prevent this e-mail being flagged as spam.)'),
    ];
    $form['site_info']['site_info_frontpage'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Default front page'),
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.site_info_frontpage') : $site_config->get('Site Front page'),
      '#required' => TRUE,
      '#description' => $this->t('The home page displays content from this relative URL. If unsure, specify "node".'),
    ];

    $form['regional_languages'] = [
      '#type' => 'details',
      '#title' => $this->t('Regional Languages'),
      '#open' => TRUE,
    ];
    $form['regional_languages']['active_lang'] = [
      '#type' => 'checkboxes',
      '#multiple' =>  true,
      '#required' =>  true,
      '#title' => $this->t('List of active languages'),
      '#options' => $option,
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.active_lang') : $site_config->get('Active language'),
    ];
    $form['regional_languages']['default_lang'] = [
      '#type' => 'language_select',
      '#title' => $this->t('Site default language'),
      '#default_value' => ($config->get($domain_id) != NULL) ? $config->get($domain_id . '.default_lang') : $site_config->get('Default language'),
      '#languages' => LanguageInterface::STATE_ALL,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for empty front page path.
    if ($form_state->isValueEmpty('site_frontpage')) {
      // Set to default "user/login".
      $form_state->setValueForElement($form['front_page']['site_frontpage'], '/user/login');
    }
    else {
      // Get the normal path of the front page.
      $form_state->setValueForElement($form['front_page']['site_frontpage'], $this->aliasManager->getPathByAlias($form_state->getValue('site_frontpage')));
    }
    // Validate front page path.
    if (($value = $form_state->getValue('site_frontpage')) && $value[0] !== '/') {
      $form_state->setErrorByName('site_frontpage', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('site_frontpage')]));
    }
    if (!$this->pathValidator->isValid($form_state->getValue('site_frontpage'))) {
      $form_state->setErrorByName('site_frontpage', $this->t("The path '%path' is either invalid or you do not have access to it.", ['%path' => $form_state->getValue('site_frontpage')]));
    }
    // Get the normal paths of both error pages.
    if (!$form_state->isValueEmpty('site_403')) {
      $form_state->setValueForElement($form['error_page']['site_403'], $this->aliasManager->getPathByAlias($form_state->getValue('site_403')));
    }
    if (!$form_state->isValueEmpty('site_404')) {
      $form_state->setValueForElement($form['error_page']['site_404'], $this->aliasManager->getPathByAlias($form_state->getValue('site_404')));
    }
    if (($value = $form_state->getValue('site_403')) && $value[0] !== '/') {
      $form_state->setErrorByName('site_403', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('site_403')]));
    }
    if (($value = $form_state->getValue('site_404')) && $value[0] !== '/') {
      $form_state->setErrorByName('site_404', $this->t("The path '%path' has to start with a slash.", ['%path' => $form_state->getValue('site_404')]));
    }
    // Validate 403 error path.
    if (!$form_state->isValueEmpty('site_403') && !$this->pathValidator->isValid($form_state->getValue('site_403'))) {
      $form_state->setErrorByName('site_403', $this->t("The path '%path' is either invalid or you do not have access to it.", ['%path' => $form_state->getValue('site_403')]));
    }
    // Validate 404 error path.
    if (!$form_state->isValueEmpty('site_404') && !$this->pathValidator->isValid($form_state->getValue('site_404'))) {
      $form_state->setErrorByName('site_404', $this->t("The path '%path' is either invalid or you do not have access to it.", ['%path' => $form_state->getValue('site_404')]));
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    $domain_id = $form_state->getValue('domain_id');
    $site_name = $form_state->getValue('site_name');
    $site_slogan = $form_state->getValue('site_slogan');
    $site_mail = $form_state->getValue('site_mail');
    $site_frontpage = $form_state->getValue('site_frontpage');
    $site_403 = $form_state->getValue('site_403');
    $site_404 = $form_state->getValue('site_404');
    $gtm = str_replace('GTM-','',$form_state->getValue('gtm_code_field'));
    $gtm_code_field = 'GTM-'.$gtm;
    $site_info_name = $form_state->getValue('site_info_name');
    $site_info_email = $form_state->getValue('site_info_email');
    $site_info_frontpage = $form_state->getValue('site_info_frontpage');
    $active_lang = $form_state->getValue('active_lang');
    $default_lang = $form_state->getValue('default_lang');
    $config = $this->config('unfpa_co_domain_site_settings.domainconfigsettings');
    $config->set($domain_id . '.site_name', $site_name);
    $config->set($domain_id . '.site_slogan', $site_slogan);
    $config->set($domain_id . '.site_mail', $site_mail);
    $config->set($domain_id . '.site_frontpage', $site_frontpage);
    $config->set($domain_id . '.site_403', $site_403);
    $config->set($domain_id . '.site_404', $site_404);
    $config->set($domain_id . '.gtm_code_field', $gtm_code_field);
    $config->set($domain_id . '.site_info_name', $site_info_name);
    $config->set($domain_id . '.site_info_email', $site_info_email);
    $config->set($domain_id . '.site_info_frontpage', $site_info_frontpage);
    $config->set($domain_id . '.active_lang', $active_lang);
    $config->set($domain_id . '.default_lang', $default_lang);
    $config->save();
  }

}
