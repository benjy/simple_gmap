<?php

/**
 * @file
 * Contains \Drupal\simple_gmap\Plugin\Field\FieldFormatter\SimpleGMapFormatter.
 */

namespace Drupal\simple_gmap\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'simple_gmap' formatter.
 *
 * @FieldFormatter(
 *   id = "simple_gmap",
 *   label = @Translation("Google Map from one-line address"),
 *   field_types = {
 *     "text"
 *   },
 *   settings = {
 *     "include_map" = "1",
 *     "include_static_map" = "0",
 *     "include_link" = "0",
 *     "include_text" = "0",
 *     "iframe_height" = "200",
 *     "iframe_width" = "200",
 *     "zoom_level" = "14",
 *     "information_bubble" = "1",
 *     "link_text" = "View larger map",
 *     "map_type" = "m",
 *     "langcode" = "en"
 *   }
 * )
 */
class SimpleGMapFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, array &$form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['embedded_label'] = array(
      '#type' => 'markup',
      '#markup' => '<h3>' . t('Embedded map') . '</h3>',
    );
    $elements['include_map'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include embedded dynamic map'),
      '#default_value' => $this->getSetting('include_map'),
    );
    $elements['include_static_map'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include embedded static map'),
      '#default_value' => $this->getSetting('include_static_map'),
    );
    $elements['iframe_width'] = array(
      '#type' => 'textfield',
      '#title' => t('Width of embedded map'),
      '#default_value' => $this->getSetting('iframe_width'),
      '#description' => t('Note that static maps only accept sizes in pixels'),
    );
    $elements['iframe_height'] = array(
      '#type' => 'textfield',
      '#title' => t('Height of embedded map'),
      '#default_value' => $this->getSetting('iframe_height'),
      '#description' => t('Note that static maps only accept sizes in pixels'),
    );
    $elements['link_label'] = array(
      '#type' => 'markup',
      '#markup' => '<h3>' . t('Link to map') . '</h3>',
    );
    $elements['include_link'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include link to map'),
      '#default_value' => $this->getSetting('include_link'),
    );
    $elements['link_text'] = array(
      '#type' => 'textfield',
      '#title' => t('Link text'),
      '#default_value' => $this->getSetting('link_text'),
      '#description' => t("Enter the text to use for the link to the map, or enter 'use_address' (without the quotes) to use the entered address text as the link text"),
    );
    $elements['generic_label'] = array(
      '#type' => 'markup',
      '#markup' => '<h3>' . t('General settings') . '</h3>',
    );
    $elements['zoom_level'] = array(
      '#type' => 'select',
      '#options' => array(
        1 => t('1 - Minimum'),
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
        14 => t('14 - Default'),
        15 => 15,
        16 => 16,
        17 => 17,
        18 => 18,
        19 => 19,
        20 => t('20 - Maximum'),
      ),
      '#title' => t('Zoom level'),
      '#default_value' => $this->getSetting('zoom_level'),
    );
    $elements['information_bubble'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show information bubble'),
      '#default_value' => $this->getSetting('information_bubble'),
      '#description' => t('If checked, the information bubble for the marker will be displayed when the embedded or linked map loads.'),
    );
    $elements['include_text'] = array(
      '#type' => 'checkbox',
      '#title' => t('Include original address text'),
      '#default_value' => $this->getSetting('include_text'),
    );
    $elements['map_type'] = array(
      '#type' => 'select',
      '#title' => t('Map type'),
      '#description' => t('Choose a default map type for embedded and linked maps'),
      '#options' => array(
        'm' => t('Map'),
        'k' => t('Satellite'),
        'h' => t('Hybrid'),
        'p' => t('Terrain'),
      ),
      '#default_value' => $this->getSetting('map_type'),
    );
    $elements['langcode'] = array(
      '#type' => 'textfield',
      '#title' => t('Language'),
      '#default_value' => $this->getSetting('langcode'),
      '#description' => t("Enter a two-letter language code that Google Maps can recognize, or enter 'page' (without the quotes) to use the current page's Drupal language code"),
    );
    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $information_bubble = $this->getSetting('information_bubble') ? t('Yes') : t('No');
    $map_types = array(
      'm' => t('Map'),
      'k' => t('Satellite'),
      'h' => t('Hybrid'),
      'p' => t('Terrain'),
    );
    $map_type = $this->getSetting('map_type') ? $this->getSetting('map_type') : 'm';
    $map_type = isset($map_types[$map_type]) ? $map_types[$map_type] : $map_types['m'];

    $include_map = $this->getSetting('include_map');
    if ($include_map) {
      $summary[] = t('Dynamic map: @width x @height', array('@width' => $this->getSetting('iframe_width'), '@height' => $this->getSetting('iframe_height')));
    }
    $include_static_map = $this->getSetting('include_static_map');
    if ($include_static_map) {
      $summary[] = t('Static map: @width x @height', array('@width' => $this->getSetting('iframe_width'), '@height' => $this->getSetting('iframe_height')));
    }
    $include_link = $this->getSetting('include_link');
    if ($include_link) {
      $summary[] = t('Map link: @link_text', array('@link_text' => $this->getSetting('link_text')));
    }

    if ($include_link || $include_map || $include_static_map) {
      $langcode = check_plain($this->getSetting('langcode'));
      $language = isset($langcode) ? $langcode : 'en';
      $summary[] = t('Map Type: @map_type', array('@map_type' => $map_type));
      $summary[] = t('Zoom Level: @zoom_level', array('@zoom_level' => $this->getSetting('zoom_level')));
      $summary[] = t('Information Bubble: @information_bubble', array('@information_bubble' => $information_bubble));
      $summary[] = t('Language: @language', array('@language' => $language));
    }
    $include_text = $this->getSetting('include_text');
    if ($include_text) {
      $summary[] = t('Original text displayed');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {

    $element = array();
    $settings = $this->getSettings();
    $static_h = 0;
    $static_w = 0;

    $embed = (int) $settings['include_map'] ? TRUE : FALSE;
    $static = (int) $settings['include_static_map'] ? TRUE: FALSE;
    $link = (int) $settings['include_link'] ? TRUE : FALSE;
    $text = (int) $settings['include_text'] ? TRUE : FALSE;

    $height = check_plain($settings['iframe_height']);
    $width = check_plain($settings['iframe_width']);
    if ($static) {
      $static_h = (int) $height;
      $static_w = (int) $width;
    }
    $link_text = $link ? check_plain($settings['link_text']) : '';
    $bubble = (int) $settings['information_bubble'] ? TRUE : FALSE;
    $zoom_level = (int) $settings['zoom_level'];

    // For some reason, static gmaps accepts a different value for map type.
    $static_map_types = array('m' => 'roadmap', 'k' => 'satellite', 'h' => 'hybrid', 'p' => 'terrain');

    $map_type = $settings['map_type'];

    // Figure out a language code to use. Google cannot recognize 'und'.
    $lang_to_use = check_plain($settings['langcode']);

    if (!$lang_to_use || $lang_to_use == 'page') {
      $lang_to_use = $items->getLangcode();
    }

    foreach ($items as $delta => $item) {
      $url_value = urlencode(check_plain($item->value));
      $address_value = check_plain($item->value);
      $address = $text ? $address_value : '';

      $element[$delta] = array(
        '#theme' => 'simple_gmap_output',
        '#include_map' => $embed,
        '#include_static_map' => $static,
        '#include_link' => $link,
        '#include_text' => $text,
        '#width' => $width,
        '#height' => $height,
        '#url_suffix' => $url_value,
        '#zoom' => $zoom_level,
        '#information_bubble' => $bubble,
        '#link_text' => ($link_text == 'use_address') ? $address_value : $link_text,
        '#address_text' => $address,
        '#map_type' => $map_type,
        '#langcode' => $lang_to_use,
        '#static_map_type' => $static_map_types[$map_type],
        '#static_h' => $static_h,
        '#static_w' => $static_w,
      );
    }
    return $element;
  }
}
