<?php

declare(strict_types=1);

namespace Drupal\Tests\oe_theme\Kernel\Patterns;

use Drupal\Core\Url;
use Drupal\Tests\oe_theme\Kernel\AbstractKernelTestBase;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Test link pattern rendering.
 *
 * @group batch9
 */
class LinkPatternRenderingTest extends AbstractKernelTestBase {

  /**
   * Test that link patterns are correctly rendered when passing a URL object.
   *
   * @throws \Exception
   */
  public function testLinkPatternRendering() {
    $url = Url::fromUserInput('/node/add', [
      'attributes' => [
        'class' => ['foo'],
        'foo' => 'bar',
      ],
    ]);
    // Extract attributes from the URL object, as the SDC component expects
    // explicit props (no preprocess hook auto-extracts them anymore).
    $attributes = (array) $url->getOption('attributes');
    $extra_classes = implode(' ', $attributes['class'] ?? []);
    unset($attributes['class']);
    $extra_attributes = [];
    foreach ($attributes as $name => $value) {
      $extra_attributes[] = ['name' => $name, 'value' => $value];
    }

    $pattern = [
      '#type' => 'component',
      '#component' => 'oe_theme:link',
      '#props' => [
        'text' => 'Link text',
        'url' => $url,
        'extra_classes' => $extra_classes,
        'extra_attributes' => $extra_attributes,
      ],
    ];

    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);
    $this->assertEquals('Link text', $crawler->filter('a.ecl-link.ecl-link--standalone.foo')->text());
    $this->assertEquals('/node/add', $crawler->filter('a.ecl-link.ecl-link--standalone.foo')->attr('href'));
    $this->assertEquals('bar', $crawler->filter('a.ecl-link.ecl-link--standalone.foo')->attr('foo'));
    $this->assertCount(0, $crawler->filter('span.ecl-link__label'));
    $this->assertCount(0, $crawler->filter('svg.ecl-icon.ecl-icon--s.ecl-link__icon'));

    $pattern = [
      '#type' => 'component',
      '#component' => 'oe_theme:link',
      '#props' => [
        'text' => 'Link text',
        'url' => Url::fromUri('https://example.com'),
        'external_link' => TRUE,
      ],
    ];

    $html = $this->renderRoot($pattern);
    $crawler = new Crawler($html);
    $this->assertEquals('Link text', $crawler->filter('a.ecl-link.ecl-link--standalone.ecl-link--icon span.ecl-link__label')->text());
    $this->assertEquals('https://example.com', $crawler->filter('a.ecl-link.ecl-link--standalone.ecl-link--icon')->attr('href'));
    $this->assertCount(1, $crawler->filter('span.ecl-icon.ecl-icon--2xs.ecl-link__icon.wt-icon--external'));
  }

}
