Unserialize Post Meta WordPress Plugin
======================================

### Description ###

Loops through posts with specified post meta and extracts that data out of serialized arrays into its own database rows. Meta keys must be specified manually in the php file.

Particularly useful if you stored post meta using WPAlchemy with WPALCHEMY_MODE_ARRAY and need to convert that existing post meta to WPALCHEMY_MODE_EXTRACT.

### Notes ###

1. Change variables to specify the meta key that holds serialized meta values and which key within you would like extracted

		// This is the meta_key within the postmeta table which has a
		// meta_value that contains serialized data
		$serialized_metakey = '_serialized_metakey';
		
		// This is the array key within the serialized data that holds the values
		// you need and will become the new meta_key when the meta is extracted/unserialized
		$extract_metakey = '_extracted_metakey';

2. Activate plugin - the plugin code will run upon activation
3. An admin notice will indicate the plugin's success or failure extracting the post meta
4. Deactivate the plugin
5. Check your database to make sure the desired post meta was extracted