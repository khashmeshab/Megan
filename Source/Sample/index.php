<?php
	require_once('../Megan/Megan.php');
	
	// Create a new instance of the Megan class.
	$Megan=new Megan('sample.mgn');
	
	// Set two top-level labels. These labels are defined as "Global" in the template and are usable in all of its sub-sections.
	$Megan->BlogTitle='Sample Blog';
	$Megan->BlogOwner='Megan User';
	
	// Assign this global label used in an embedded file.
	$Megan->TextAlign='center';
	
	// Assign this global label used in a dynamically included file.
	$Megan->Background='#aaaaff';
	
	// Create a new "Article" section and set its local labels.
	$Article=&$Megan->NewSection('Article');
	$Article->ArticleTitle='Hello World!';
	$Article->Article='This is the first article of this sample blog';
	
	// Create a new "Comment" sub-section for the "Article" section and set its local labels.
	$Comment=&$Article->NewSection('Comment');
	$Comment->Sender='First Visitor';
	$Comment->Comment='What a useful article.';

	// Create another "Comment" sub-section.
	$Comment=&$Article->NewSection('Comment');
	$Comment->Sender='Second Visitor';
	$Comment->Comment='Thank you for this article.';

	// Create another "Article" section.
	$Article=&$Megan->NewSection('Article');
	$Article->ArticleTitle='Good Bye World!';
	$Article->Article='This is the last article of this sample blog';
	
	// Create a new "Comment" section for the new "Article" section.
	$Comment=&$Article->NewSection('Comment');
	$Comment->Sender='Last Visitor';
	$Comment->Comment='I miss you.';

	// Process the template and display the result.
	$Megan->Generate();
?>