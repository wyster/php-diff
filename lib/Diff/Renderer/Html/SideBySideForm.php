<?php
/**
 * Side by Side HTML diff generator for PHP DiffLib.
 *
 * PHP version 5
 *
 * Copyright (c) 2009 Chris Boulton <chris.boulton@interspire.com>
 * Copyright (c) 2013 Andreas Seifert <cobra_fast@wtwrp.de>
 * 
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without 
 * modification, are permitted provided that the following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice,
 *    this list of conditions and the following disclaimer.
 *  - Redistributions in binary form must reproduce the above copyright notice,
 *    this list of conditions and the following disclaimer in the documentation
 *    and/or other materials provided with the distribution.
 *  - Neither the name of the Chris Boulton nor the names of its contributors 
 *    may be used to endorse or promote products derived from this software 
 *    without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" 
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE 
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE 
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE 
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR 
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF 
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN 
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) 
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE 
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package DiffLib
 * @author Chris Boulton <chris.boulton@interspire.com>
 * @author Andreas Seifert <cobra_fast@wtwrp.de>
 * @copyright (c) 2009 Chris Boulton
 * @copyright (c) 2013 Andreas Seifert
 * @license New BSD License http://www.opensource.org/licenses/bsd-license.php
 * @version 1.1
 * @link http://github.com/chrisboulton/php-diff
 * @link http://github.com/cobrafast/php-diff
 */

require_once dirname(__FILE__).'/Array.php';

class Diff_Renderer_Html_SideBySideForm extends Diff_Renderer_Html_Array
{
	/**
	 * Render a and return diff with changes between the two sequences
	 * displayed side by side.
	 *
	 * @return string The generated side by side diff.
	 */
	public function render()
	{
		$changes = parent::render();

		$html = '';
		if(empty($changes)) {
			return $html;
		}

                $html .= '<form method="post">';
		$html .= '<table class="Differences DifferencesSideBySide">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th colspan="2">Old Version</th>';
                $html .= '<th></th>';
		$html .= '<th>New Version</th>';
                $html .= '<th>Use</th>';
		$html .= '</tr>';
		$html .= '</thead>';
		foreach($changes as $i => $blocks) {
			if($i > 0) {
				$html .= '<tbody class="Skipped">';
				$html .= '<th>&hellip;</th><td>&nbsp;</td>';
				$html .= '<th>&hellip;</th><td>&nbsp;</td>';
				$html .= '</tbody>';
			}

			foreach($blocks as $change) {
				$html .= '<tbody class="Change'.ucfirst($change['tag']).(($change['tag'] == 'replace' && count($change['base']['lines']) < count($change['changed']['lines'])) ? ' ReplaceMultiline' : '').'">';
				// Equal changes should be shown on both sides of the diff
				if($change['tag'] == 'equal') {
					foreach($change['base']['lines'] as $no => $line) {
						$fromLine = $change['base']['offset'] + $no + 1;
						$toLine = $change['changed']['offset'] + $no + 1;
						$html .= '<tr>';
						$html .= '<th>'.$fromLine.'</th>';
						$html .= '<td class="Left"><span>'.$line.'</span>&nbsp;</span></td>';
						$html .= '<th>'.$toLine.'</th>';
						$html .= '<td class="Right"><span>'.$line.'</span>&nbsp;</span></td>';
                                                $html .= '<td></td>';
						$html .= '</tr>';
					}
				}
				// Added lines only on the right side
				else if($change['tag'] == 'insert') {
					foreach($change['changed']['lines'] as $no => $line) {
						$toLine = $change['changed']['offset'] + $no + 1;
						$html .= '<tr>';
						$html .= '<th>&nbsp;</th>';
						$html .= '<td class="Left">&nbsp;</td>';
						$html .= '<th>'.$toLine.'</th>';
						$html .= '<td class="Right"><ins>'.$line.'</ins>&nbsp;</td>';
                                                $html .= '<td><input type="checkbox" name="use_line[' . $toLine . ']" value="' . $toLine . '" /></td>';
						$html .= '</tr>';
					}
				}
				// Show deleted lines only on the left side
				else if($change['tag'] == 'delete') {
					foreach($change['base']['lines'] as $no => $line) {
						$fromLine = $change['base']['offset'] + $no + 1;
						$html .= '<tr>';
						$html .= '<th>'.$fromLine.'</th>';
						$html .= '<td class="Left"><del>'.$line.'</del>&nbsp;</td>';
						$html .= '<th>&nbsp;</th>';
						$html .= '<td class="Right">&nbsp;</td>';
                                                $html .= '<td><input type="checkbox" name="use_line[' . $fromLine . ']" value="' . $fromLine . '" /></td>';
						$html .= '</tr>';
					}
				}
				// Show modified lines on both sides
				else if($change['tag'] == 'replace') {
					if(count($change['base']['lines']) >= count($change['changed']['lines'])) {
						foreach($change['base']['lines'] as $no => $line) {
							$fromLine = $change['base']['offset'] + $no + 1;
							$html .= '<tr>';
							$html .= '<th>'.$fromLine.'</th>';
							$html .= '<td class="Left"><span>'.$line.'</span>&nbsp;</td>';
							if(!isset($change['changed']['lines'][$no])) {
								$toLine = '&nbsp;';
								$changedLine = '&nbsp;';
							}
							else {
								$toLine = $change['changed']['offset'] + $no + 1;
								$changedLine = '<span>'.$change['changed']['lines'][$no].'</span>';
							}
							$html .= '<th>'.$toLine.'</th>';
							$html .= '<td class="Right">'.$changedLine.'</td>';
                                                        $html .= '<td><input type="checkbox" name="use_line[' . $toLine . ']" value="' . $toLine . '" /></td>';
							$html .= '</tr>';
						}
					}
					else {
						foreach($change['changed']['lines'] as $no => $changedLine) {
							if(!isset($change['base']['lines'][$no])) {
								$fromLine = '&nbsp;';
								$line = '&nbsp;';
							}
							else {
								$fromLine = $change['base']['offset'] + $no + 1;
								$line = '<span>'.$change['base']['lines'][$no].'</span>';
							}
							$html .= '<tr>';
							$html .= '<th>'.$fromLine.'</th>';
							$html .= '<td class="Left"><span>'.$line.'</span>&nbsp;</td>';
							$toLine = $change['changed']['offset'] + $no + 1;
							$html .= '<th>'.$toLine.'</th>';
							$html .= '<td class="Right">'.$changedLine.'</td>';
                                                        if ($no == 0)
                                                            $html .= '<td rowspan="' . count($change['changed']['lines']) . '"><input type="checkbox" name="use_line[' . $toLine . ']" value="' . $toLine . '" /></td>';
							$html .= '</tr>';
						}
					}
				}
				$html .= '</tbody>';
			}
		}
		$html .= '</table>';
                $html .= '<input type="checkbox" name="use_type[]" value="1" /> Use all inserts<br />';
                $html .= '<input type="checkbox" name="use_type[]" value="2" /> Use all replaces and insert-replaces<br />';
                $html .= '<input type="checkbox" name="use_type[]" value="4" /> Use all deletes<br />';
                $html .= '<br /><input type="submit" name="merge" value="Merge" />';
                $html .= '</form>';
		return $html;
	}
}