<?php

namespace Hyde\Framework\Contracts;

use Hyde\Framework\Models\MarkdownDocument;

interface MarkdownPageContract
{
	/**
	 * Get the page's Markdown Document object.
	 *
	 * @return \Hyde\Framework\Models\MarkdownDocument
	 */
	public function markdown(): MarkdownDocument;
}
