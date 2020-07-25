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
      // 'results_count_config' => 'Results Count'
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
    // $results_count_config = $config['results_count_config'];

    // Site URL
    $host = \Drupal::request()->getSchemeAndHttpHost() . '/';

    $session = new SpotifyWebAPI\Session(
      $client_id,
      $client_secret
      // $host
    );

    $api = new SpotifyWebAPI\SpotifyWebAPI();

    $build = [];
    $build['#theme'] = 'spotify_api';
    $build['#items'] = [];

    if (isset($_GET['code'])) {
      $session->requestCredentialsToken($_GET['code']);
      $api->setAccessToken($session->getAccessToken());

      $artists = $api->getArtistRelatedArtists('6LuN9FCkKOj5PcnpouEgny');

      foreach ($artists->artists as $artist) {

        $artist_url = $artist->href;

        $item = [
          '#markup' => $artist->name,
        ];

        $build['#items'][] = $item;

        echo '<b>' . $artist->name . '</b> <br>';
      }
    } else {
      $options = [
        'scope' => [
          'user-read-email',
        ],
      ];

      header('Location: ' . $session->getAuthorizeUrl($options));
      die();
    }

    return $build;
  }
}
