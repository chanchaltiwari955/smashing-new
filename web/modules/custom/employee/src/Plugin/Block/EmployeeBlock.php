<?php
namespace Drupal\employee\Plugin\Block;

use Drupal\Core\Block\BlockBase;
/**
 * Provides a 'CustomBlock' block.
 *
 * @Block(
 *  id = "custom_block",
 *  admin_label = @Translation("custom block"),
 * )
 */
class EmployeeBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    ////$build = [];
    //$build['custom_block']['#markup'] = 'Implement CustomBlock.';

    $form = \Drupal::formBuilder()->getForm('Drupal\employee\Form\EmployeeForm');

    return $form;
  }

}