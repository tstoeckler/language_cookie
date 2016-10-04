<?php

namespace Drupal\language_cookie;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Executable\ExecutableInterface;
use Drupal\Core\Executable\ExecutableManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Manages language cookie condition plugins.
 */
class LanguageCookieConditionManager extends DefaultPluginManager implements ExecutableManagerInterface {

  /**
   * Constructs a new LanguageCookieConditionManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   An object that implements CacheBackendInterface.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   An object that implements ModuleHandlerInterface.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/LanguageCookieCondition', $namespaces, $module_handler, 'Drupal\language_cookie\LanguageCookieConditionInterface', 'Drupal\language_cookie\Annotation\LanguageCookieCondition');
    $this->cacheBackend = $cache_backend;
    $this->cacheKeyPrefix = 'language_cookie_condition_plugins';
    $this->cacheKey = 'language_cookie_condition_plugins';
    $this->alterInfo('language_cookie_condition_info');
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinitions() {
    $definitions = parent::getDefinitions();

    uasort($definitions, 'Drupal\Component\Utility\SortArray::sortByWeightElement');

    return $definitions;
  }

  /**
   * {@inheritdoc}
   */
  public function createInstance($plugin_id, array $configuration = array()) {
    $plugin = $this->getFactory()->createInstance($plugin_id, $configuration);

    // If we receive any context values via config set it into the plugin.
    if (!empty($configuration['context'])) {
      foreach ($configuration['context'] as $name => $context) {
        $plugin->setContextValue($name, $context);
      }
    }

    return $plugin->setExecutableManager($this);
  }

  /**
   * {@inheritdoc}
   */
  public function execute(ExecutableInterface $condition) {
    return $condition->evaluate();
  }

}
