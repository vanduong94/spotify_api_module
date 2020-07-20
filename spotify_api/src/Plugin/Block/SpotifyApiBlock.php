<?php


namespace Drupal\spotify_api\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use SpotifyWebAPI;

/**
 * Provides a 'Spotify Results' block.
 * 
 * @Block(
 *  id = "spotify_api",
 *  admin_label = @Translation("Spotify Results"),
 * )
 */
class SpotifyApiBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration()
  {
    return [
      'client_id' => '',
      'client_secret' => '',
    ];
  }


  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state)
  {
    $config = $this->getConfiguration();

    $form_elements = $this->getFormElements();

    foreach ($form_elements as $key => $title) {
      $form[$key] = [
        '#type' => 'textfield',
        '#title' => $this->t($title),
        '#default_value' => $config[$key],
        '#required' => TRUE,
      ];
    }

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, \Drupal\Core\Form\FormStateInterface $form_state)
  {
    $form_elements = $this->getFormElements();

    foreach ($form_elements as $key => $title) {
      $value = $form_state->getValue($key);
      $this->setConfigurationValue($key, $value);
    }
  }


  /**
   * The array of configuration values needed.
   */
  public function getFormElements()
  {
    return [
      'client_id' => 'Client ID',
      'client_secret' => 'Client Secret',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {

    $config = $this->getConfiguration();

    $client_id = $config['client_id'];
    $client_secret = $config['client_secret'];

    $host = \Drupal::request()->getSchemeAndHttpHost() . '/';

    // Move this out into config as well.
    // $results_count_config = '1';

    $session = new SpotifyWebAPI\Session(
      $client_id,
      $client_secret,
      $host
    );

    $api = new SpotifyWebAPI\SpotifyWebAPI();

    if (isset($_GET['code'])) {
      $session->requestAccessToken($_GET['code']);
      $api->setAccessToken($session->getAccessToken());

      $album = $api->getAlbum('6KT8x5oqZJl9CcnM66hddo?highlight=spotify:track:6TqXcAFInzjp0bODyvrWEq');

      echo '<b>' . $album->name . '</b>';
    } else {
      $options = [
        'scope' => [
          'user-read-email',
        ],
      ];

      header('Location: ' . $session->getAuthorizeUrl($options));
    }

    $build = [];
    $build['#theme'] = 'spotify_api';
    $build['#results'] = [];

    return $build;
  }
}
