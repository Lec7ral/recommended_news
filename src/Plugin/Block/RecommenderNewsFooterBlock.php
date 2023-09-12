<?php

namespace Drupal\recommender_news\Plugin\Block;

;
use Drupal\Core\Block\BlockBase;


/**
 * Provides a 'Recommender News' Block.
 *
 * @Block(
 *   id = "recommender_news_footer_block",
 *   admin_label = @Translation("Recommender News"),
 *   category = @Translation("Recommender News Block Footer"),
 * )
 */
class RecommenderNewsFooterBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $results = [];
    if(\Drupal::currentUser()->isAuthenticated()) {
      $query = \Drupal::database()->select('news_recomended', 'nr');
      $query->fields('nr', array('id_news'));
      $query->condition('nr.id_news_recommended',\Drupal::currentUser()->id());
      $id = $query->execute()->fetchField();
      $newsid = explode(',',$id);
/*       echo '<pre>';
      print_r($newsid);
      echo '</pre>'; */
      foreach ($newsid as $idnews) {
      $query = \Drupal::database()->select('news', 'n');
      $query->fields('n', array('title', 'url', 'urlimage'));
      $query->condition('n.id_news',$idnews);
      $res = $query->execute()->fetchAll();
      $results = array_merge($results, $res);
/*             echo '<pre>';
      print_r($res);
      echo '</pre>'; */
    }

    }

    else{
      $query = \Drupal::database()->select('news', 'n');
      $query->fields('n', array('title', 'url', 'urlimage'));
      $results = $query->execute()->fetchAll();
    }
/*     echo '<pre>';
    print_r($res);
    echo '</pre>';
      echo '<pre>';
      print_r($results);
      echo '</pre>'; */

    $items = [];
    foreach ($results as $result) {
      $news_item  = [
     'title' => $result->title,
     'url' => $result->url,
     'urlimage' => $result->urlimage,
    ];
      $items[] = $news_item;
      array_splice($items, 4);
    }
    $block = [
      '#attached' => [
        'library' => [
        'recommender_news/news.css',
        ],
        ],
      '#theme' => 'recommender_news',
      '#items' => $items,
      '#attributes' => ['class' => ['recommender-news-footer-block']],
    ];
/*     echo '<pre>';
    print_r($items);
    echo '</pre>'; */
    return $block;
  }

}

