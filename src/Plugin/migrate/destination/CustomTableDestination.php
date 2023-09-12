<?php

namespace Drupal\mymodule\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\DestinationBase;
use Drupal\migrate\Plugin\MigrateIdMapInterface;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Row;

/**
 * Custom table destination plugin.
 *
 * @MigrateDestination(
 *   id = "custom_table"
 * )
 */
class CustomTableDestination extends DestinationBase {

  /**
   * {@inheritdoc}
   */
  public function import(Row $row, array $old_destination_id_values = []) {
    // Implement your custom logic to insert the data into your custom table.
    $data = [
      'id' => $row->getIdMap()->getSourceIdValues()['guid'],
      'title' => $row->getDestinationProperty('title'),
      'url' => $row->getDestinationProperty('url'),
      'urlimagen' => $row->getDestinationProperty('urlimagen'),
      'tags' => $row->getDestinationProperty('tags'),
    ];
    // Insert the data into the "news" table using your preferred method (e.g., database API).
  }

  /**
   * {@inheritdoc}
   */
  public function rollback(array $destination_identifier) {
    // Implement your custom logic to rollback the insertion of the data.
    // Delete the corresponding row from the "news" table using your preferred method (e.g., database API).
  }

  /**
   * {@inheritdoc}
   */
  public function fields(MigrationInterface $migration = NULL) {
    // Implement the list of fields in your custom table.
    return [
      'id' => $this->t('ID'),
      'title' => $this->t('Title'),
      'url' => $this->t('URL'),
      'urlimagen' => $this->t('Image URL'),
      'tags' => $this->t('Tags'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    // Implement the unique identifier(s) for your custom table.
    return [
      'id' => [
        'type' => 'string',
        'alias' => 'nt',
      ],
    ];
  }

}
