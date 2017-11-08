<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2017 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Shared;

use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\TestHelperDOCX;

/**
 * Test class for PhpOffice\PhpWord\Shared\Html
 * @coversDefaultClass \PhpOffice\PhpWord\Shared\Html
 */
class HtmlTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test unit conversion functions with various numbers
     */
    public function testAddHtml()
    {
        $content = '';

        // Default
        $section = new Section(1);
        $this->assertCount(0, $section->getElements());

        // Heading
        $styles = array('strong', 'em', 'sup', 'sub');
        for ($level = 1; $level <= 6; $level++) {
            $content .= "<h{$level}>Heading {$level}</h{$level}>";
        }

        // Styles
        $content .= '<p style="text-decoration: underline; text-decoration: line-through; '
                  . 'text-align: center; color: #999; background-color: #000; font-weight: bold; font-style: italic;">';
        foreach ($styles as $style) {
            $content .= "<{$style}>{$style}</{$style}>";
        }
        $content .= '</p>';

        // Add HTML
        Html::addHtml($section, $content);
        $this->assertCount(7, $section->getElements());

        // Other parts
        $section = new Section(1);
        $content = '';
        $content .= '<table><tr><th>Header</th><td>Content</td></tr></table>';
        $content .= '<ul><li>Bullet</li><ul><li>Bullet</li></ul></ul>';
        $content .= '<ol><li>Bullet</li></ol>';
        $content .= "'Single Quoted Text'";
        $content .= '"Double Quoted Text"';
        $content .= '& Ampersand';
        $content .= '&lt;&gt;&ldquo;&lsquo;&rsquo;&laquo;&raquo;&lsaquo;&rsaquo;';
        $content .= '&amp;&bull;&deg;&hellip;&trade;&copy;&reg;&mdash;';
        $content .= '&ndash;&nbsp;&emsp;&ensp;&sup2;&sup3;&frac14;&frac12;&frac34;';
        Html::addHtml($section, $content);
    }

    /**
     * Test that html already in body element can be read
     * @ignore
     */
    public function testParseFullHtml()
    {
        $section = new Section(1);
        Html::addHtml($section, '<body><p>test paragraph1</p><p>test paragraph2</p></body>', true);

        $this->assertCount(2, $section->getElements());
    }

    /**
     * Test underline
     */
    public function testParseUnderline()
    {
        $html = '<u>test</u>';
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        Html::addHtml($section, $html);

        $doc = TestHelperDOCX::getDocument($phpWord, 'Word2007');
        $this->assertTrue($doc->elementExists('/w:document/w:body/w:p/w:r/w:rPr/w:u'));
        $this->assertEquals('single', $doc->getElementAttribute('/w:document/w:body/w:p/w:r/w:rPr/w:u', 'w:val'));
    }

    /**
     * Test text-decoration style
     */
    public function testParseTextDecoration()
    {
        $html = '<span style="text-decoration: underline;">test</span>';
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        Html::addHtml($section, $html);

        $doc = TestHelperDOCX::getDocument($phpWord, 'Word2007');
        $this->assertTrue($doc->elementExists('/w:document/w:body/w:p/w:r/w:rPr/w:u'));
        $this->assertEquals('single', $doc->getElementAttribute('/w:document/w:body/w:p/w:r/w:rPr/w:u', 'w:val'));
    }

    /**
     * Test text-align style
     */
    public function testParseTextAlign()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        Html::addHtml($section, '<p style="text-align: left;">test</p>');
        Html::addHtml($section, '<p style="text-align: right;">test</p>');
        Html::addHtml($section, '<p style="text-align: center;">test</p>');
        Html::addHtml($section, '<p style="text-align: justify;">test</p>');

        $doc = TestHelperDOCX::getDocument($phpWord, 'Word2007');
        $this->assertTrue($doc->elementExists('/w:document/w:body/w:p/w:pPr/w:jc'));
        $this->assertEquals('start', $doc->getElementAttribute('/w:document/w:body/w:p[1]/w:pPr/w:jc', 'w:val'));
        $this->assertEquals('end', $doc->getElementAttribute('/w:document/w:body/w:p[2]/w:pPr/w:jc', 'w:val'));
        $this->assertEquals('center', $doc->getElementAttribute('/w:document/w:body/w:p[3]/w:pPr/w:jc', 'w:val'));
        $this->assertEquals('both', $doc->getElementAttribute('/w:document/w:body/w:p[4]/w:pPr/w:jc', 'w:val'));
    }

    /**
     * Test parsing paragraph and span styles
     */
    public function testParseParagraphAndSpanStyle()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        Html::addHtml($section, '<p style="text-align: center;"><span style="text-decoration: underline;">test</span></p>');

        $doc = TestHelperDOCX::getDocument($phpWord, 'Word2007');
        $this->assertTrue($doc->elementExists('/w:document/w:body/w:p/w:pPr/w:jc'));
        $this->assertEquals('center', $doc->getElementAttribute('/w:document/w:body/w:p[1]/w:pPr/w:jc', 'w:val'));
        $this->assertEquals('single', $doc->getElementAttribute('/w:document/w:body/w:p[1]/w:r/w:rPr/w:u', 'w:val'));
    }

    /**
     * Test parsing table
     */
    public function testParseTable()
    {
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();
        $html = '
            <table style="width: 50%; border: 6px #0000FF solid;">
                <thead>
                    <tr style="background-color: #FF0000; text-align: center; color: #FFFFFF; font-weight: bold; ">
                        <th>a</th>
                        <th>b</th>
                        <th>c</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>1</td><td colspan="2">2</td></tr>
                    <tr><td>4</td><td>5</td><td>6</td></tr>
                </tbody>
            </table>';
        Html::addHtml($section, $html);

        $doc = TestHelperDOCX::getDocument($phpWord, 'Word2007');
//         echo $doc->printXml();
//         $this->assertTrue($doc->elementExists('/w:document/w:body/w:tbl/w:tr/w:tc'));
    }
}
