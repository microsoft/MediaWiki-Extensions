<?php
class PandocUploadError extends SpecialPage {
	function __construct() {
		parent::__construct( 'PandocUpload', '', false );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$this->setHeaders();

		$wikitext = 'Pandoc conversion extension encountered some critical errors.<br />'
			. 'Your file is uploaded but the conversion failed.';
		$output->addWikiTextAsInterface( $wikitext );

		$output->addHTML(
			Html::element(
				'a',
				[ 'href' => Title::newMainPage()->getFullUrl() ],
				"Return to main page."
			)
		);
	}
}
