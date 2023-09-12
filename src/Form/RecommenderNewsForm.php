<?php

namespace Drupal\recommender_news\Form;

use Drupal;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\FileUsage\DatabaseFileUsageBackend;
use Drupal\file\Entity\File;
use Drupal\feeds\Feeds\Processor\NodeProcessorInterface;


class RecommenderNewsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'recommender_news_config_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['recommender_news.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('recommender_news.settings');
    $form['news_source'] = [
      '#type' => 'fieldset',
      '#title' => t('News Source'),
    ];

    $form['news_source']['source_type'] = [
      '#type' => 'radios',
      '#title' => t('Source Type'),
      '#options' => [
        'rss' => t('RSS'),
        'file_upload' => t('File Upload'),
      ],
      '#default_value' => isset($config->get('news_source')['source_type']) ? $config->get('news_source')['source_type'] : 'rss',
    ];

    $form['news_source']['rss_url'] = [
      '#type' => 'url',
      '#title' => t('RSS URL'),
      '#states' => [
        'visible' => [
          ':input[name="source_type"]' => ['value' => 'rss'],
        ],
      ],
      '#required' => TRUE,
      '#default_value' => isset($config->get('news_source')['rss_url']) ? $config->get('news_source')['rss_url'] : '',
    ];

    $form['news_source']['file_upload'] = [
      '#title' => t('File Upload'),
      '#type' => 'file',
      '#required' => TRUE,
      '#states' => [
        'visible' => [
          ':input[name="source_type"]' => ['value' => 'file_upload'],
        ],
      ],
      '#default_value' => FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }
  public function validateForm(array &$form, FormStateInterface $form_state) {
      $source_type = $form_state->getValue('source_type');

      if ($source_type == 'rss') {
        $rss_url = $form_state->getValue('rss_url');
        if (!filter_var($rss_url, FILTER_VALIDATE_URL) || strpos($rss_url, 'rss/') == false) {
          $form_state->setErrorByName('rss_url', t('Please enter a valid RSS URL'));
        }
      }
 /*      elseif ($source_type == 'file_upload') {
          $file = $form_state->getValue('file_upload');
          // Validar que el archivo tenga el formato CSV
          $file_extension = strtolower(pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION));
          if ($file_extension != 'csv') {
            $form_state->setErrorByName('file_upload', t('Please upload a CSV file'));
          }
        }*/
  }
  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('recommender_news.settings')
      ->set('source_type', $form_state->getValue('source_type'))
      ->set('rss_url', $form_state->getValue('rss_url'))
      ->set('file_upload', $form_state->getValue('file_upload'))
      ->save();

      $rss_url = $form_state['values']['rss_url'];
      $csv_file = file_save_upload('csv_file', array('file_validate_extensions' => array('csv')));

      if ($csv_file) {
        // Si se cargó un archivo CSV, guarda el archivo en una ubicación temporal y obtén su ruta.
        $file_path = file_unmanaged_copy($csv_file->uri);
        $file_path = file_create_url($file_path);
      }

      // Configura la configuración de importación de Feeds.
      $config = [
        'content_type' => 'nombre_del_tipo_de_contenido',
        'importer_id' => 'nombre_del_importador',
        'source' => $rss_url, // O $file_path si se cargó un archivo CSV.
      ];

      // Ejecuta la importación utilizando el módulo Feeds.
      $importer = feeds_source($config);
      $importer->import();

      // Obtiene los resultados de la importación.
      $results = $importer->getResults();

      // Recorre los resultados y guarda la información en tu tabla de noticias.
      foreach ($results['imported'] as $item) {
        // Crea un nuevo objeto de noticia.
        $news = new stdClass();
        $news->title = $item['title'];
        $news->body = $item['description'];
        // Agrega más campos según sea necesario.

        // Guarda la noticia en la tabla de noticias.
        drupal_write_record('news', $news);
      }

      drupal_set_message(t('La importación se ha completado con éxito.'));

      parent::submitForm($form, $form_state);
    /*     $file = $form_state->getValue('file_upload');
        $file_path = \Drupal::service('file_system')->realpath($file->getFileUri());
        $content = file_get_contents($file_path);
    // Dividir el contenido en líneas.
    $lines = explode("\n", $content);

    // Procesar cada línea del archivo.
    foreach ($lines as $line) {
    // Dividir la línea en campos.
    $fields = str_getcsv($line);

    // Obtener los valores de los campos.
    $title = isset($fields[0]) ? $fields[0] : '';
    $url = isset($fields[1]) ? $fields[1] : '';
    $other_url = isset($fields[2]) ? $fields[2] : '';
    $tags = isset($fields[3]) ? $fields[3] : '';




        if ($source_type == 'file_upload') {
          $file = File::load($form_state->getValue('file_upload')[0]);
          $file->setPermanent();
          $file->save();

          $filePath = $file->getFileUri();
          $csvData = [];


          if (($handle = fopen($filePath, "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Analizar solo las columnas correspondientes a los campos 'titulo', 'url', 'urlimagen' y 'tags'
                $csvData[] = [
                    'title' => $data[0],
                    'url' => $data[2],
                    'urlimagen' => $data[1],
                    'tags' => $data[3]
                ];
            }
            fclose($handle);
        }
            // Insertar los datos en la tabla
      // foreach ($csvData as $row) {
          $query = \Drupal::database()->upsert('news');
              $query->fields([
                  'title' => $row['title'],
                  'url' => $row['url'],
                  'urlimagen' => $row['urlimagen'],
                  'tags' => $row['tags']
              ]);

            $query->key('id_news');
            $query->execute();
        }
      $form_state->setMessageByName('upload_file', t('Please enter a valid RSS URL')); */

  }
}


