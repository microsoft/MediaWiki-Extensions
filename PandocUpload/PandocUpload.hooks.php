<?php
/**
 * Hooks for PageInfoBar extension
 *
 * @file
 * @ingroup Extensions
 * @copyright © 2017 Microsoft
 * @licence GNU General Public Licence 2.0 or later
 */

class PandocUploadHooks {

  private static $conversionArray = array(
    "docx" => "docx",
    "md" => "markdown",
    "markdown" => "markdown"
  );

  private static $TITLE_MIN_LENGTH = 4;
  private static $TITLE_MAX_LENGTH = 255;

  public static function onUploadFormInitDescriptor( &$descriptor ) {

    global $wgOut;

    // add checkbox and textbox in options section
    $descriptor['ConvertToArticle'] = [
      'type' => 'check',
      'id' => 'wpConvertToArticle',
      'default' => false,
      'label' => 'Convert uploaded document to an article',
      'section' => 'options',
      'disabled' => true
    ];

    $descriptor['ConvertToArticleTextbox'] = [
      'type' => 'text',
      'id' => 'wpArticleTitle',
      'size' => 80,
      'placeholder' => "Type in the title for the article created here. Existing article will get overwritten.",
      'section' => 'options',
      'disabled' => true
    ];

    // add convertible file format message    
    $substr = implode(", ", array_keys(self::$conversionArray));
    $descriptor['SupportedFormats'] = [
      'type' => 'info',
      'section' => 'source',
      'default' => wfMessage( "pandocupload-supported-formats-msg" )->params( $substr ),
      'raw' => true,
    ];
    
    $wgOut->addModules( "ext.pandocUpload" );

    return true;
  }

  public static function onUploadComplete( &$image ) {

    global $wgSMTP, $wgSitename;
    global $wgPandocExecutablePath;

    $context = RequestContext::getMain();
    
    $request = $context->getRequest();
    $user = $context->getUser();

    try {

      if ( $request->getCheck( "wpConvertToArticle" ) == 1 ) {

        $file = $image->getLocalFile();
        $filename = $file->getName();
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        $titleString = $request->getText("wpConvertToArticleTextbox");
        if ( strlen($titleString) < self::$TITLE_MIN_LENGTH || strlen($titleString) > self::$TITLE_MAX_LENGTH ) {
          throw new MWException(wfMessage("pandocupload-warning-title-length")->params(self::$TITLE_MIN_LENGTH, self::$TITLE_MAX_LENGTH)->plain());
        }
        $title = Title::newFromText($titleString);
        $page = WikiPage::factory( $title );

        if ( !array_key_exists( $ext, self::$conversionArray ) ) {
          throw new MWException(wfMessage("pandocupload-warning-unsupported-format")->params( $ext )->plain()); 
        }
        
        $output = array();
        $command = sprintf(
          '"%s" --from=%s --to=%s "%s" 2>&1',
          $wgPandocExecutablePath,
          self::$conversionArray[$ext],
          "mediawiki",
          $file->getLocalRefPath()
        );
        exec( $command, $output );
        // post processing: transform regular tables to wikitables
        foreach ( $output as &$line ) {
          if ( $line == "{|" ) {
            $line = "{| class='wikitable'";
          }
        }
        // generate the entire wikitext
        $newContent = new WikiTextContent( implode("\n", $output) );

        $page->doUserEditContent( $newContent, $user, "pandoc conversion");

        // send email for notification
        $subject = wfMessage('pandocupload-notification-email-subject')->params( $user->getName() )->plain();
        
        $body = wfMessage('pandocupload-notification-email-body')->params(
          $user->getName(),
          $title->getText(),
          "File:" . $filename)->text();

        $from = new MailAddress($wgSMTP['username'],'','');
        $to = MailAddress::newFromUser($user);
        $jobParams = array( 'to' => $to, 'from' => $from, 'subj' => "$wgSitename - $subject", 'body' => $body, 'replyto' => null) ;
        $job = new EmaillingJob( $title, $jobParams );
        JobQueueGroup::singleton()->push( $job );       
      }
    } catch ( Exception $e ) {
      header( 'location: ' . Title::newFromText( "Special:PandocUpload" )->getFullUrl() );
      exit;
    }
  }
}
