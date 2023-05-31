### Pandoc Upload Extension for MediaWiki
This extension adds a new upload option in Special:Upload to allow optional conversion of uploaded Word (.docx) & Markdown (.md) files to a wiki article.

#### Prerequisite
- This extension require an installation of Pandoc and a mailing agent for PHP

#### Installation instruction
Load the extension:
```php
wfLoadExtension(PandocUpload);
```

Specify path to pandoc in $wgPandocExecutablePath (run ```whereis pandoc``` on linux to get the pandoc binary location)
```php
$wgPandocExecutablePath = '/your/path/to/pandoc';
```

You can also create 2 MediaWiki namespace articles with a single string describing the upload dialog box in the special page
- MediaWiki:ConvertUploadToArticle
- MediaWiki:ArticleTitlePlaceholder

#### Usage
To convert a file to an article, open "Special:Upload" page, select checkbox "Convert uploaded document to an article" and specify a page name where content will be saved to. 

![image](https://github.com/Griboedow/MediaWiki-Extensions/assets/4194526/494de0ce-bfb1-472d-ab83-4a5b500ea481)

The file will be saved and after that pandoc conversion will start. Please note, that the original fill will remain in uploads. 
