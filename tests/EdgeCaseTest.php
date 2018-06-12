<?hh // strict
/*
 *  Copyright (c) 2004-present, Facebook, Inc.
 *  All rights reserved.
 *
 *  This source code is licensed under the MIT license found in the
 *  LICENSE file in the root directory of this source tree.
 *
 */

namespace Facebook\Markdown;

use namespace Facebook\TypeAssert;
use namespace HH\Lib\{C, Str, Vec};

use function Facebook\FBExpect\expect;

final class EdgeCaseTest extends TestCase {
  public function getManualExamples(
  ): array<(string, string)> {
    return [
      tuple("- foo\n\n", "<ul>\n<li>foo</li>\n</ul>\n"),
      tuple("- foo\n\n\n", "<ul>\n<li>foo</li>\n</ul>\n"),
      // Already covered in the spec, but emphasizing here as they
      // illustrate correct binding for the next problme
      tuple('**foo*', "<p>*<em>foo</em></p>\n"),
      tuple('*foo**', "<p><em>foo</em>*</p>\n"),
      // Uncovered a bug
      tuple(
        '*foo **bar *baz bim** bam*',
        "<p>*foo <em><em>bar <em>baz bim</em></em> bam</em></p>\n",
      ),
      tuple(
        '*foo __bar *baz bim__ bam*',
        "<p><em>foo <strong>bar *baz bim</strong> bam</em></p>\n",
      ),
    ];
  }

  /** @dataProvider getManualExamples */
  public function testManualExample(
    string $in,
    string $expected_html,
  ): void {
    $this->assertExampleMatches(
      'unnamed',
      $in,
      $expected_html,
      null,
    );
  }

  public function testTagFilter(): void {
    $ast = parse((new ParserContext())->enableHTML_UNSAFE(), '<iframe />');
    $html = (new HTMLRenderer(new RenderContext()))->render($ast);
    expect($html)->toBeSame("&lt;iframe />\n");
    $html = (
      new HTMLRenderer(
        (new RenderContext())->disableNamedExtension('TagFilter'),
      )
    )->render($ast);
    expect($html)->toBeSame("<iframe />\n");
  }
}
