### Pandoc Upload Extension for MediaWiki
This extension adds a new upload option in Special:Upload to allow optional conversion of uploaded Word (.docx) & Markdown (.md) files to a wiki article.

#### Prerequisite
- This extension require an installation of Pandoc and a mailing agent for PHP

#### Installation instruction
Create 2 MediaWiki namespace articles with a single string describing the upload dialog box in the special page
- MediaWiki:ConvertUploadToArticle
- MediaWiki:ArticleTitlePlaceholder
