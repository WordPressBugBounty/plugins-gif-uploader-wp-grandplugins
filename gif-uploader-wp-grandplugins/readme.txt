=== WP GIF Uploader - Animated GIF Upload and Thumbnails ===
Tags: gif, animated gif, gif upload, wordpress gif, gif animation
Tested up to: 7.1
Requires at least: 4.5.0
Requires PHP: 7.0
Version: 1.0.5
Stable Tag: 1.0.5
Contributors: GrandPlugins
Author: GrandPlugins
Plugin URI: https://grandplugins.com/product/wp-gif-editor/
Donate link: https://ko-fi.com/grandplugins
Author email: services@grandplugins.com
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Upload animated GIFs and keep the animation in every thumbnail WordPress generates, instead of a still first frame.


== Description ==

Upload an animated GIF to WordPress and the thumbnail, medium and large copies it generates come back as a single still frame. The original still animates; every size WordPress made from it does not.

This plugin generates those subsizes with the animation intact, so a GIF stays a GIF wherever your theme happens to display it.

=== Features ===

* Every subsize WordPress generates from a GIF keeps its animation.
* Works on upload, so there is nothing to configure and nothing to run.
* Uses Imagick where your host provides it, and falls back to a built-in method where it does not.

👉 **[Pro](https://grandplugins.com/product/wp-gif-editor/?utm_source=free&utm_medium=readme&utm_content=gif-uploader-wp-grandplugins&utm_term=pro_header)** | **[Docs](https://grandplugins.com/documentation/wp-gif-editor-plugin/)** 👈


The Pro version goes further. It applies crop, scale, rotate and flip to a GIF from the media edit screen with every frame intact, builds a new GIF out of images you already have, adds image or text watermarks, and lets you decide whether a GIF plays on load, on hover or on click.

=== Pro Features ===
* Create GIF from images.
* Add image and text watermarks to gifs.
* Control the GIF play action [ hover - click ].
* Apply all the edits operations on gif in the media edit page without losing the animation [ crop - rotate - flip - scale ].
* Optimize GIF loading by using first frame image at page load.
* Regenerate animated-subsizes.

== Other Plugins for the Same Job ==

The ones that pair with working on images.

[WP GIF Editor](https://grandplugins.com/product/wp-gif-editor/?utm_source=free&utm_medium=readme&utm_content=gif-uploader-wp-grandplugins&utm_term=pro) &mdash; the Pro version of this plugin. Crop, scale, rotate and flip GIFs with every frame intact, watermark them, build a GIF from images, and load the first frame first so a heavy GIF does not hold up the page.

[Image Sizes Controller](https://grandplugins.com/product/image-sizes-controller/?utm_source=free&utm_medium=readme&utm_content=gif-uploader-wp-grandplugins&utm_term=image-sizes-controller) &mdash; switch off the image sizes your theme registers but never displays. Worth doing here, because every size you keep is another animated copy of every GIF.

[WP Image Converter](https://grandplugins.com/product/wp-image-converter/?utm_source=free&utm_medium=readme&utm_content=gif-uploader-wp-grandplugins&utm_term=wp-image-converter) &mdash; convert a library that is already full of JPEGs and PNGs to modern formats in bulk, keeping the originals.

[WP Images Watermark](https://grandplugins.com/product/wp-images-watermark/?utm_source=free&utm_medium=readme&utm_content=gif-uploader-wp-grandplugins&utm_term=wp-images-watermark) &mdash; put an image or text watermark on what you upload, automatically or on the images you pick.

[Browse everything](https://grandplugins.com/product-category/plugin/?utm_source=free&utm_medium=readme&utm_content=gif-uploader-wp-grandplugins&utm_term=browse_all)

== Installation ==

1. Upload the plugin to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Start uploading GIF images from media and subsizes will keep the animation.
4. The GIF Editor settings live under Media in the admin menu.

== Frequently Asked Questions ==

= Why do my animated GIFs stop moving in WordPress? =

Because you are almost certainly not looking at the GIF you uploaded. WordPress
keeps your original, then generates a thumbnail, medium and large copy of every
image, and the tool it uses to do that reads only the first frame of a GIF. Your
theme displays one of those generated copies, so what you see is a still image.
The original is untouched and still animates.

This plugin generates those copies with every frame intact, so whichever size
your theme picks is still animated.

= Do I have to re-upload the GIFs already in my library? =

Yes, for those existing files. The plugin works at upload time, so anything
added before you activated it still has still-frame subsizes. Re-uploading a GIF
regenerates them properly. The Pro version can regenerate animated subsizes for
images you already have, without re-uploading.

= Does it need Imagick? =

No. It uses Imagick when your host provides it, because that is the faster path,
and falls back to a built-in method when it does not. You do not have to
configure which one is used.

= Will this make my pages slower? =

An animated GIF is a large file, and keeping it animated at every size keeps it
large. That is the trade. Two things help: reduce how many image sizes your
theme registers, so there are fewer copies in the first place, and load the
first frame as a still image until the page has finished loading, which is a Pro
feature.

= Does it work with GIFs in the block editor and page builders? =

Yes. The plugin changes how the image sizes are generated, not how they are
displayed, so anything that renders a WordPress image works normally.

== Changelog ==

= 1.0.5 =
* Added: prompts on the media screens that read your own library - how many GIFs you have, how many are over 1 MB, and how many animated copies each upload turns into. Dismissible, and quiet for a month once dismissed.
* Fixed: five strings were tagged with a text domain the plugin does not register, so they could never be translated.
* Fixed: the Pro notice on the attachment screen now escapes its output.
* Fixed: the Plugin URI in this readme pointed at a page that no longer exists.
* Changed: tested up to WordPress 7.1.
* Changed: Requires PHP is now 7.0, matching the rest of our plugins. It said 5.4 in this readme and 5.6 in the plugin, and 5.6 has been unsupported since 2018.
* Added: a Frequently Asked Questions section, answering why GIFs stop animating in WordPress.
* Changed: added the short description, which was missing, and replaced the list of thirteen other plugins with four that are actually related.

= 1.0.4 =
* Tested up to WordPress 6.9.

= 1.0.3 =
* Added: Imagick support for generating GIF subsizes.

= 1.0.2 =
* Compatibility updates.
