<?php
/*
	<id>ChenZhen:EhPortal</id>
	<name>EhPortal</name>
	<version>1.1</version>
*/
/*
 * EhPortal is a ported version of SimplePortal 2.3.6 (Copyright (c) 2014 SimplePortal Team.)
 * This software is in no way affiliated with the original developers
 * EhPortal Portal ~ Copyright (c) 2015 WebDev (http://web-develop.ca)
 * Distributed under the BSD 2-Clause License (http://opensource.org/licenses/BSD-2-Clause)
*/

if (!defined('SMF'))
	die('Hacking attempt...');

/**
 * Truncates HTML text retaining tags and formatting.
 * See http://www.pjgalbraith.com/2011/11/truncating-text-html-with-php/ for related blog post.
 *
 * Example:
 *
 * $output = TruncateHTML::truncateChars($your_html, '40', '...');
 * $output = TruncateHTML::truncateWords($your_html, '7', '...');
 *
 * @author pjgalbraith http://www.pjgalbraith.com
 *
 */

/*
    Copyright (c) 2011 Patrick Galbraith (http://www.pjgalbraith.com).
    All rights reserved.

    Permission is hereby granted, free of charge, to any person obtaining a copy
    of this software and associated documentation files (the "Software"), to deal
    in the Software without restriction, including without limitation the rights
    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
    copies of the Software, and to permit persons to whom the Software is
    furnished to do so, subject to the following conditions:

    The above copyright notice and this permission notice shall be included in
    all copies or substantial portions of the Software.

    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    THE SOFTWARE.
*/

class TruncateHTML {

    public static function truncateChars($html, $limit, $ellipsis = '...') {

	if($limit <= 0 || $limit >= strlen(strip_tags($html)))
	    return $html;

	$dom = new DOMDocument();
	$dom->loadHTML($html);

	$body = $dom->getElementsByTagName("body")->item(0);

	$it = new DOMLettersIterator($body);

	foreach($it as $letter) {
	    if($it->key() >= $limit) {
		$currentText = $it->currentTextPosition();
		$currentText[0]->nodeValue = substr($currentText[0]->nodeValue, 0, $currentText[1] + 1);
		self::removeProceedingNodes($currentText[0], $body);
		self::insertEllipsis($currentText[0], $ellipsis);
		break;
	    }
	}

	return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }

    public static function truncateWords($html, $limit, $ellipsis = '...') {

	if($limit <= 0 || $limit >= self::countWords(strip_tags($html)))
	    return $html;

	$dom = new DOMDocument();
	$dom->loadHTML($html);

	$body = $dom->getElementsByTagName("body")->item(0);

	$it = new DOMWordsIterator($body);

	foreach($it as $word) {
	    if($it->key() >= $limit) {
		$currentWordPosition = $it->currentWordPosition();
		$curNode = $currentWordPosition[0];
		$offset = $currentWordPosition[1];
		$words = $currentWordPosition[2];

		$curNode->nodeValue = substr($curNode->nodeValue, 0, $words[$offset][1] + strlen($words[$offset][0]));

		self::removeProceedingNodes($curNode, $body);
		self::insertEllipsis($curNode, $ellipsis);
		break;
	    }
	}

	return preg_replace('~<(?:!DOCTYPE|/?(?:html|head|body))[^>]*>\s*~i', '', $dom->saveHTML());
    }

    private static function removeProceedingNodes(DOMNode $domNode, DOMNode $topNode) {
	$nextNode = $domNode->nextSibling;

	if($nextNode !== NULL) {
	    self::removeProceedingNodes($nextNode, $topNode);
	    $domNode->parentNode->removeChild($nextNode);
	} else {
	    //scan upwards till we find a sibling
	    $curNode = $domNode->parentNode;
	    while($curNode !== $topNode) {
		if($curNode->nextSibling !== NULL) {
		    $curNode = $curNode->nextSibling;
		    self::removeProceedingNodes($curNode, $topNode);
		    $curNode->parentNode->removeChild($curNode);
		    break;
		}
		$curNode = $curNode->parentNode;
	    }
	}
    }

    private static function insertEllipsis(DOMNode $domNode, $ellipsis) {
	$avoid = array('a', 'strong', 'em', 'h1', 'h2', 'h3', 'h4', 'h5'); //html tags to avoid appending the ellipsis to

	if( in_array($domNode->parentNode->nodeName, $avoid) && $domNode->parentNode->parentNode !== NULL) {
	    // Append as text node to parent instead
	    $textNode = new DOMText($ellipsis);

	    if($domNode->parentNode->parentNode->nextSibling)
		$domNode->parentNode->parentNode->insertBefore($textNode, $domNode->parentNode->parentNode->nextSibling);
	    else
		$domNode->parentNode->parentNode->appendChild($textNode);
	} else {
	    // Append to current node
	    $domNode->nodeValue = rtrim($domNode->nodeValue).$ellipsis;
	}
    }

    private static function countWords($text) {
	$words = preg_split("/[\n\r\t ]+/", $text, -1, PREG_SPLIT_NO_EMPTY);
	return count($words);
    }

}

/**
 * Iterates individual words of DOM text and CDATA nodes
 * while keeping track of their position in the document.
 *
 * Example:
 *
 *  $doc = new DOMDocument();
 *  $doc->load('example.xml');
 *  foreach(new DOMWordsIterator($doc) as $word) echo $word;
 *
 * @author pjgalbraith http://www.pjgalbraith.com
 * @author porneL http://pornel.net (based on DOMLettersIterator available at http://pornel.net/source/domlettersiterator.php)
 * @license Public Domain
 *
 */

final class DOMWordsIterator implements Iterator {

    private $start, $current;
    private $offset, $key, $words;

    /**
     * expects DOMElement or DOMDocument (see DOMDocument::load and DOMDocument::loadHTML)
     */
    function __construct(DOMNode $el)
    {
	if ($el instanceof DOMDocument) $this->start = $el->documentElement;
	else if ($el instanceof DOMElement) $this->start = $el;
	else throw new InvalidArgumentException("Invalid arguments, expected DOMElement or DOMDocument");
    }

    /**
     * Returns position in text as DOMText node and character offset.
     * (it's NOT a byte offset, you must use mb_substr() or similar to use this offset properly).
     * node may be NULL if iterator has finished.
     *
     * @return array
     */
    function currentWordPosition()
    {
	return array($this->current, $this->offset, $this->words);
    }

    /**
     * Returns DOMElement that is currently being iterated or NULL if iterator has finished.
     *
     * @return DOMElement
     */
    function currentElement()
    {
	return $this->current ? $this->current->parentNode : NULL;
    }

    // Implementation of Iterator interface
    function key()
    {
	return $this->key;
    }

    function next()
    {
	if (!$this->current) return;

	if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE)
	{
	    if ($this->offset == -1)
	    {
		$this->words = preg_split("/[\n\r\t ]+/", $this->current->textContent, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);
	    }
	    $this->offset++;

	    if ($this->offset < count($this->words)) {
		$this->key++;
		return;
	    }
	    $this->offset = -1;
	}

	while($this->current->nodeType == XML_ELEMENT_NODE && $this->current->firstChild)
	{
	    $this->current = $this->current->firstChild;
	    if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE) return $this->next();
	}

	while(!$this->current->nextSibling && $this->current->parentNode)
	{
	    $this->current = $this->current->parentNode;
	    if ($this->current === $this->start) {$this->current = NULL; return;}
	}

	$this->current = $this->current->nextSibling;

	return $this->next();
    }

    function current()
    {
	if ($this->current) return $this->words[$this->offset][0];
	return NULL;
    }

    function valid()
    {
	return !!$this->current;
    }

    function rewind()
    {
	$this->offset = -1; $this->words = array();
	$this->current = $this->start;
	$this->next();
    }
}

/**
 * Iterates individual characters (Unicode codepoints) of DOM text and CDATA nodes
 * while keeping track of their position in the document.
 *
 * Example:
 *
 *  $doc = new DOMDocument();
 *  $doc->load('example.xml');
 *  foreach(new DOMLettersIterator($doc) as $letter) echo $letter;
 *
 * NB: If you only need characters without their position
 *     in the document, use DOMNode->textContent instead.
 *
 * @author porneL http://pornel.net
 * @license Public Domain
 *
 */
final class DOMLettersIterator implements Iterator
{
    private $start, $current;
    private $offset, $key, $letters;

    /**
     * expects DOMElement or DOMDocument (see DOMDocument::load and DOMDocument::loadHTML)
     */
    function __construct(DOMNode $el)
    {
	if ($el instanceof DOMDocument) $this->start = $el->documentElement;
	else if ($el instanceof DOMElement) $this->start = $el;
	else throw new InvalidArgumentException("Invalid arguments, expected DOMElement or DOMDocument");
    }

    /**
     * Returns position in text as DOMText node and character offset.
     * (it's NOT a byte offset, you must use mb_substr() or similar to use this offset properly).
     * node may be NULL if iterator has finished.
     *
     * @return array
     */
    function currentTextPosition()
    {
	return array($this->current, $this->offset);
    }

    /**
     * Returns DOMElement that is currently being iterated or NULL if iterator has finished.
     *
     * @return DOMElement
     */
    function currentElement()
    {
	return $this->current ? $this->current->parentNode : NULL;
    }

    // Implementation of Iterator interface
    function key()
    {
	return $this->key;
    }

    function next()
    {
	if (!$this->current) return;

	if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE)
	{
	    if ($this->offset == -1)
	    {
		// fastest way to get individual Unicode chars and does not require mb_* functions
		preg_match_all('/./us',$this->current->textContent,$m); $this->letters = $m[0];
	    }
	    $this->offset++; $this->key++;
	    if ($this->offset < count($this->letters)) return;
	    $this->offset = -1;
	}

	while($this->current->nodeType == XML_ELEMENT_NODE && $this->current->firstChild)
	{
	    $this->current = $this->current->firstChild;
	    if ($this->current->nodeType == XML_TEXT_NODE || $this->current->nodeType == XML_CDATA_SECTION_NODE) return $this->next();
	}

	while(!$this->current->nextSibling && $this->current->parentNode)
	{
	    $this->current = $this->current->parentNode;
	    if ($this->current === $this->start) {$this->current = NULL; return;}
	}

	$this->current = $this->current->nextSibling;

	return $this->next();
    }

    function current()
    {
	if ($this->current) return $this->letters[$this->offset];
	return NULL;
    }

    function valid()
    {
	return !!$this->current;
    }

    function rewind()
    {
	$this->offset = -1; $this->letters = array();
	$this->current = $this->start;
	$this->next();
    }
}

?>