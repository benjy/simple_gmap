<?php

/**
 * @file
 * Contains \Drupal\simple_gmap\Plugin\Field\FieldFormatter\SimpleGMapFormatter.
 */

namespace Drupal\simple_gmap\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\SafeMarkup;

/**
 * Plugin implementation of the 'simple_gmap' formatter.
 *
 * @FieldFormatter(
 *   id = "simple_gmap",
 *   label = @Translation("Google Map from one-line address"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class SimpleGMapFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      "include_map" => "1",
     "include_static_map" => "0",
     "include_link" => "0",
     "include_text" => "0",
     "iframe_height" => "200",
     "iframe_width" => "200",
     "zoom_level" => "14",
     "information_bubble" => "1",
     "link_text" => "View larger map",
     "map_type" => "m",
     "langcode" => "en"
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['embedded_label'] = array(
      '#type' => 'markup',
      '#markup' => '<h3>' . $this->t('Embedded map') . '</h3>',
    );
    $elements['include_map'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Include embedded dynamic map'),
      '#default_value' => $this->getSetting('include_map'),
    );
    $elements['include_static_map'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Include embedded static map'),
      '#default_value' => $this->getSetting('include_static_map'),
    );
    $elements['iframe_width'] = array(
      '#type' => 'number',
      '#title' => $this->t('Width of embedded map'),
      '#default_value' => $this->getSetting('iframe_width'),
      '#description' => $this->t('Note that static maps only accept sizes in pixels'),
      '#min' => 1,
      '#step' => 1,
    );
    $elements['iframe_height'] = array(
      '#type' => 'number',
      '#title' => $this->t('Height of embedded map'),
      '#default_value' => $this->getSetting('iframe_height'),
      '#description' => $this->t('Note that static maps only accept sizes in pixels'),
      '#min' => 1,
      '#step' => 1,
    );
    $elements['link_label'] = array(
      '#type' => 'markup',
      '#markup' => '<h3>' . $this->t('Link to map') . '</h3>',
    );
    $elements['include_link'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Include link to map'),
      '#default_value' => $this->getSetting('include_link'),
    );
    $elements['link_text'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $this->getSetting('link_text'),
      '#description' => $this->t("Enter the text to use for the link to the map, or enter 'use_address' (without the quotes) to use the entered address text as the link text"),
    );
    $elements['generic_label'] = array(
      '#type' => 'markup',
      '#markup' => '<h3>' . $this->t('General settings') . '</h3>',
    );
    $elements['zoom_level'] = array(
      '#type' => 'select',
      '#options' => array(
        1 => $this->t('1 - Minimum'),
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,
        11 => 11,
        12 => 12,
        13 => 13,
        14 => $this->t('14 - Default'),
        15 => 15,
        16 => 16,
        17 => 17,
        18 => 18,
        19 => 19,
        20 => $this->t('20 - Maximum'),
      ),
      '#title' => $this->t('Zoom level'),
      '#default_value' => $this->getSetting('zoom_level'),
    );
    $elements['information_bubble'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Show information bubble'),
      '#default_value' => $this->getSetting('information_bubble'),
      '#description' => $this->t('If checked, the information bubble for the marker will be displayed when the embedded or linked map loads.'),
    );
    $elements['include_text'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Include original address text'),
      '#default_value' => $this->getSetting('include_text'),
    );
    $elements['map_type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Map type'),
      '#description' => $this->t('Choose a default map type for embedded and linked maps'),
      '#options' => array(
        'm' => $this->t('Map'),
        'k' => $this->t('Satellite'),
        'h' => $this->t('Hybrid'),
        'p' => $this->t('Terrain'),
      ),
      '#default_value' => $this->getSetting('map_type'),
    );
    $elements['langcode'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Language'),
      '#default_value' => $this->getSetting('langcode'),
      '#description' => $this->t("Enter a two-letter language code that Google Maps can recognize, or enter 'page' (without the quotes) to use the current page's language code"),
    );
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $information_bubble = $this->getSetting('information_bubble') ? $this->t('Yes') : $this->t('No');
    $map_types = array(
      'm' => $this->t('Map'),
      'k' => $this->t('Satellite'),
      'h' => $this->t('Hybrid'),
      'p' => $this->t('Terrain'),
    );
    $map_type = $this->getSetting('map_type') ? $this->getSetting('map_type') : 'm';
    $map_type = isset($map_types[$map_type]) ? $map_types[$map_type] : $map_types['m'];

    $include_map = $this->getSetting('include_map');
    if ($include_map) {
      $summary[] = $this->t('Dynamic map: @width x @height', array('@width' => $this->getSetting('iframe_width'), '@height' => $this->getSetting('iframe_height')));
    }
    $include_static_map = $this->getSetting('include_static_map');
    if ($include_static_map) {
      $summary[] = $this->t('Static map: @width x @height', array('@width' => $this->getSetting('iframe_width'), '@height' => $this->getSetting('iframe_height')));
    }
    $include_link = $this->getSetting('include_link');
    if ($include_link) {
      $summary[] = $this->t('Map link: @link_text', array('@link_text' => $this->getSetting('link_text')));
    }

    if ($include_link || $include_map || $include_static_map) {
      $langcode = SafeMarkup::checkPlain($this->getSetting('langcode'));
      $language = isset($langcode) ? $langcode : 'en';
      $summary[] = $this->t('Map Type: @map_type', array('@map_type' => $map_type));
      $summary[] = $this->t('Zoom Level: @zoom_level', array('@zoom_level' => $this->getSetting('zoom_level')));
      $summary[] = $this->t('Information Bubble: @information_bubble', array('@information_bubble' => $information_bubble));
      $summary[] = $this->t('Language: @language', array('@language' => $language));
    }
    $include_text = $this->getSetting('include_text');
    if ($include_text) {
      $summary[] = $this->t('Original text displayed');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $element = array();
    $settings = $this->getSettings();

    $embed = (int) $settings['include_map'] ? TRUE : FALSE;
    $static = (int) $settings['include_static_map'] ? TRUE: FALSE;
    $link = (int) $settings['include_link'] ? TRUE : FALSE;
    $text = (int) $settings['include_text'] ? TRUE : FALSE;

    $link_text = $link ? SafeMarkup::checkPlain($settings['link_text']) : '';
    $bubble = (int) $settings['information_bubble'] ? TRUE : FALSE;
    $zoom_level = (int) $settings['zoom_level'];

    // For some reason, static gmaps accepts a different value for map type.
    $static_map_types = array('m' => 'roadmap', 'k' => 'satellite', 'h' => 'hybrid', 'p' => 'terrain');

    $map_type = $settings['map_type'];

    // Figure out a language code to use. Google cannot recognize 'und'.
    $lang_to_use = SafeMarkup::checkPlain($settings['langcode']);

    if (!$lang_to_use || $lang_to_use == 'page') {
      $lang_to_use = $langcode;
    }

    foreach ($items as $delta => $item) {
      $url_value = urlencode(SafeMarkup::checkPlain($item->value));
      $address_value = SafeMarkup::checkPlain($item->value);
      $address = $text ? $address_value : '';

      $element[$delta] = array(
        '#theme' => 'simple_gmap_output',
        '#include_map' => $embed,
        '#include_static_map' => $static,
        '#include_link' => $link,
        '#include_text' => $text,
        '#width' => $settings['iframe_width'],
        '#height' => $settings['iframe_height'],
        '#url_suffix' => $url_value,
        '#zoom' => $zoom_level,
        '#information_bubble' => $bubble,
        '#link_text' => ($link_text == 'use_address') ? $address_value : $link_text,
        '#address_text' => $address,
        '#map_type' => $map_type,
        '#langcode' => $lang_to_use,
        '#static_map_type' => $static_map_types[$map_type],
      );
    }
    return $element;
  }
}
