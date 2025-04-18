<?php

declare(strict_types=1);

namespace DrevOps\BehatSteps;

use Behat\Gherkin\Node\TableNode;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * Trait TaxonomyTrait.
 *
 * Taxonomy term-related steps.
 *
 * @package DrevOps\BehatSteps
 */
trait TaxonomyTrait {

  /**
   * Assert that a vocabulary exist.
   *
   * @code
   * Given vocabulary "topics" with name "Topics" exists
   * @endcode
   *
   * @Given vocabulary :vid with name :name exists
   */
  public function taxonomyAssertVocabularyExist(string $name, string $vid): void {
    $vocab = Vocabulary::load($vid);

    if (!$vocab) {
      throw new \Exception(sprintf('"%s" vocabulary does not exist', $vid));
    }

    if ($vocab->get('name') != $name) {
      throw new \Exception(sprintf('"%s" vocabulary name is not "%s"', $vid, $name));
    }
  }

  /**
   * Assert that a taxonomy term exist by name.
   *
   * @code
   * Given taxonomy term "Apple" from vocabulary "Fruits" exists
   * @endcode
   *
   * @Given taxonomy term :name from vocabulary :vocabulary_id exists
   */
  public function taxonomyAssertTermExistsByName(string $name, string $vid): void {
    $vocab = Vocabulary::load($vid);

    if (!$vocab) {
      throw new \RuntimeException(sprintf('"%s" vocabulary does not exist', $vid));
    }

    $found = \Drupal::entityTypeManager()
      ->getStorage('taxonomy_term')
      ->loadByProperties([
        'name' => $name,
        'vid' => $vid,
      ]);

    if (count($found) == 0) {
      throw new \Exception(sprintf('Taxonomy term "%s" from vocabulary "%s" does not exist', $name, $vid));
    }
  }

  /**
   * Remove terms from a specified vocabulary.
   *
   * @code
   * Given no "Fruits" terms:
   * | Apple |
   * | Pear  |
   * @endcode
   *
   * @Given no :vocabulary terms:
   */
  public function taxonomyDeleteTerms(string $vocabulary, TableNode $termsTable): void {
    foreach ($termsTable->getColumn(0) as $name) {
      $terms = \Drupal::service('entity_type.manager')->getStorage('taxonomy_term')->loadByProperties([
        'name' => $name,
        'vid' => $vocabulary,
      ]);

      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($terms as $term) {
        $term->delete();
      }
    }
  }

  /**
   * Visit specified vocabulary term page.
   *
   * @When I visit :vocabulary vocabulary term :name
   */
  public function taxonomyVisitTermPageWithName(string $vocabulary, string $name): void {
    $tids = $this->taxonomyLoadMultiple($vocabulary, [
      'name' => $name,
    ]);

    if (empty($tids)) {
      throw new \RuntimeException(sprintf('Unable to find %s term "%s"', $vocabulary, $name));
    }

    ksort($tids);
    $tid = end($tids);

    $path = $this->locatePath('/taxonomy/term/' . $tid);
    print $path;

    $this->getSession()->visit($path);
  }

  /**
   * Visit specified vocabulary term edit page.
   *
   * @When I edit :vocabulary vocabulary term :name
   */
  public function taxonomyEditTermPageWithName(string $vocabulary, string $name): void {
    $tids = $this->taxonomyLoadMultiple($vocabulary, [
      'name' => $name,
    ]);

    if (empty($tids)) {
      throw new \RuntimeException(sprintf('Unable to find %s term "%s"', $vocabulary, $name));
    }

    ksort($tids);
    $tid = end($tids);

    $path = $this->locatePath('/taxonomy/term/' . $tid . '/edit');
    print $path;

    $this->getSession()->visit($path);
  }

  /**
   * Load multiple terms with specified vocabulary and conditions.
   *
   * @param string $vocabulary
   *   The term vocabulary.
   * @param array<string,string> $conditions
   *   Conditions keyed by field names.
   *
   * @return array<int, string>
   *   Array of term ids.
   */
  protected function taxonomyLoadMultiple(string $vocabulary, array $conditions = []): array {
    $query = \Drupal::entityQuery('taxonomy_term')
      ->accessCheck(FALSE)
      ->condition('vid', $vocabulary);

    foreach ($conditions as $k => $v) {
      $and = $query->andConditionGroup();
      $and->condition($k, $v);
      $query->condition($and);
    }

    return $query->execute();
  }

}
