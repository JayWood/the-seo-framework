=== The SEO Framework ===
Contributors: Cybr
Donate link: https://theseoframework.com/donate/
Tags: open graph, description, automatic, generate, generator, title, breadcrumbs, ogtype, meta, metadata, search, engine, optimization, seo, framework, canonical, redirect, bbpress, twitter, facebook, google, bing, yahoo, jetpack, genesis, woocommerce, multisite, robots, icon, cpt, custom, post, types, pages, taxonomy, tag, sitemap, sitemaps, screenreader, rtl, feed
Requires at least: 3.6.0
Tested up to: 4.5.0
Stable tag: 2.5.2.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

The SEO Framework makes sure your Website's SEO is always up-to-date without any configuration needed. It also has support for extending.

== Description ==

= The SEO Framework =

**The lightning fast all in one automated SEO optimization plugin for WordPress**

> <strong>This plugin strongly helps you create better SEO value for your content.</strong><br>
> But at the end of the day, it all depends on how entertaining or well-constructed your content or product is.
>
> No SEO plugin does the magic thing to be found instantly. But doing it right helps a lot.<br>
> The SEO Framework helps you doing it right. Give it a try!
>
> The Default Settings are recommended in the SEO Settings page. If you know what you're doing, go ahead and change them! Each option is also documented.

= What this plugin does, in a few lines =
* Automatically configures SEO for every page, post, taxonomy and term.
* Allows you to adjust the SEO globally.
* Allows you to adjust the SEO for every applicable page.
* Shows you how to improve your SEO with a beautiful SEO bar for each supported Post, Page and Taxonomy.
* Helps your pages get ranked distinctively through various Metatag and scripting techniques.
* Helps your pages get shared more beautiful through Facebook, Twitter and other social sites.
* Allows plugin authors to easily extend this plugin.
* Supports custom post types, like WooCommerce and bbPress.
* Automatically upgrades itself from Genesis SEO.
* Allows for easy SEO plugin switch using a tool.

*Read **Transferring SEO Content using SEO Data Transporter** below for SEO plugin transitioning instructions.*

= Unbranded, Free and Professional =
This plugin is unbranded! This even means that we don't even put the name "The SEO Framework" anywhere within the WordPress interface.
Like as if this plugin is part of WordPress. No ads, no nags.
The small HTML comment can be easily disabled with the use of a filter.

Nobody has to know about the tools you've used to create your or someone else's website. A clean interface, for everyone.

= Numbers don't lie =
Optimizing SEO is a fundamental process for any website. So we try to be non-intrusive with The SEO Framework.
The SEO Framework is byte and process optimized on PHP level, with each update the optimization is improved when possible.

* This plugin is written with massive and busy (multi-)sites in mind.
* This plugin is 197% to 867% faster compared to other popular SEO plugins.
* This plugin consumes 177% to 260% fewer server resources than other popular SEO plugins.
* 15% fewer database interactions (numbers may vary on this one depending on plugin compatibility).
* 100% fewer advertisements. Let's keep it that way.

*Numbers may vary per installation and version.*

= Completely pluggable =
The SEO Framework also features pluggable functions. All functions are active and can be called within the WordPress Loop.
This allows other developers to extend the plugin wherever needed.
We have also provided an API documentation located at [The SEO Framework API Docs](http://theseoframework.com/docs/api/).

= Still not convinced? Let's dive deeper =

**This plugin automatically generates:**

* Description, with anti-spam techniques.
* Title, with super-fast 'wrong themes' support (so no buffer rewriting!).
* Various Open Graph tags.
* Special Open Graph description, which organically integrates with the Facebook and Twitter snippets
* Extended Open Graph Images support, including image manipulation.
* Canonical, with full WPMUdev Domain Mapping, subdomain and HTTPS support to prevent duplicated content.
* Schema.org LD+Json script that adds extended search support for Google Search and Chrome.
* Schema.org LD+Json script for Knowledge Graph (Personal/Business site relations, name and logo).
* Advanced Schema.org LD+Json script for Breadcrumbs (just like the visual one) which extends page relation support in Google Search.
* Schema.org LD+Json script to show the correct site name in Google Breadcrumbs.
* Publishing and editing dates, accurate to the day.
* Link relationships, with full WPMUdev Domain Mapping and HTTPS support.
* Various Facebook and Twitter Meta tags.
* Simple Sitemap with Pages, Posts and Custom Post Types (CPT), which listens to the in-post settings.
* Feed excerpts and backlinks to prevent content scraping.

**This plugin goes further, behind the screens it:**

* Prevents canonical errors with categories, pages, subdomains and multisite domain mapping.
* Disables 404 pages and empty categories from being indexed, even if they don't send a 404 response.
* Automatically notifies Google, Bing and Yahoo on Post or Page update when sitemaps are enabled.

**This plugin allows you to manually set these values for each post, page, supported CPT and term:**

* Title
* Description
* Canonical URL
* Robots (nofollow, noindex, noarchive)
* Redirect, with MultiSite spam filter (Post/Page/CPT only)
* Local on-site search settings (Post/Page/CPT only)

**This plugin allows you to adjust various site settings:**

* Title and Description Separators
* Title Additions Location
* Auto Description Output
* Robots for Archives
* Robots for the whole site
* Home Page Description, Title, Tagline and various other options
* Facebook Social integration
* Twitter Social integration
* Open Graph Meta output
* Shortlink tag output
* Post publishing time output
* Link relationships
* Google/Bing/Pinterest Webmaster verification
* Google Knowledge Graph
* Sitemap intergration
* Robots.txt
* Feed content output
* And much more

**This plugin helps you to create better content, at a glance. By showing you:**

* If the title is too long, too short and/or automatically generated.
* If the description is too long, too short and/or automatically generated.
* If the description uses some words too often.
* If the page is indexed, redirected, followed and/or archived.
* If your website is publicly accessible.

**We call this The SEO Bar. Check out the [Screenshots](https://wordpress.org/plugins/autodescription/screenshots/#plugin-info) to see how it helps you!**

> This plugin is fully compatible with the [Domain Mapping plugin by WPMUdev](https://premium.wpmudev.org/project/domain-mapping/) and the [Domain Mapping plugin by Donncha](https://wordpress.org/plugins/wordpress-mu-domain-mapping/).<br>
> This plugin is now also compatible with all kinds of custom post types.<br>
> This will **prevent canonical errors**. This way your site will always be correctly indexed, no matter what you use!<br>
>
> This plugin is also completely ad-free and has a WordPress integrated clean layout. As per WordPress.org plugin guidelines and standards.

= Caching =

This plugin's code is highly optimized on PHP-level and uses variable, object and transient caching. This means that there's little extra page load time from this plugin, even with more Meta tags used.
A caching plugin isn't even needed for this plugin as you won't notice a difference, however it's supported wherever best suited.

**If you use object caching:**
The output will be stored for each page, if you've edited a page the page output Meta will stay the same until the object cache expires. So be sure to clear your object cache or wait until it expires.

**Supported Caches:**

* Server-level Opcode (optimized).
* Staticvar functions (prevents running code twice or more).
* Staticvar class (instead of globals, prevents constructors running multiple times).
* Objects for database calls.
* Transients for process intensive operations or persisting communication with front-and back end.
* CDN for Open Graph and Twitter images.
* HTML and script Minification caching as well as Database caching are also supported.

= Compatibility =

**Basics:**

* Full internationalization support through WordPress.org.
* Extended Multibyte support (CJK).
* Full Right to Left (RTL) support.
* Color vision deficiency accessibility.
* Screen-reader accessibility.
* Admin screen: Posts, Pages, Taxonomies, Terms, Custom Post Types.
* Front-end: Every page, post, taxonomy, term, custom post type, search request, 404, etc.
* MultiSite, this plugin is in fact built upon one.
* Detection of robots.txt and sitemap.xml files.
* Detection of theme Title "doing it right" (or wrong).

**Plugins:**

* W3 Total Cache, WP Super Cache, Batcache, etc.
* WooCommerce: Shop Page, Products, Product Breadcrumbs, Product Galleries, Product Categories and Product Tags.
* Custom Post Types, (all kinds of plugins) with automatic integration.
* WPMUdev and Donncha's Domain Mapping with full HTTPS support.
* WPMUdev Avatars for og:image and twitter:image if no other image is found.
* bbPress: Forums, Topics, Replies.
* BuddyPress profiles.
* Ultimate Member profiles.
* AnsPress Questions, Profiles and Pages, also Canonical errors have been fixed.
* StudioPress SEO Data Transporter for Posts and Pages.
* WPML, URL's, sitemap and per-page/post SEO settings. (The full and automated compatibility is being discussed with WPML.)
* qTranslate X, URL's, limited sitemap and per-page/post SEO settings (through shortcodes by set by qTranslate X).
* Jetpack modules: Custom Content Types (Testimonials, Portfolio), Infinite Scroll, Photon.
* Most popular SEO plugins, let's not get in each other's way.
* Many, many other plugins, yet to confirm.

**Themes:**

* All themes.
* Special extended support for Genesis & Genesis SEO. This plugin takes all Post, Page, Category and Tag SEO values from Genesis and uses them within The SEO Framework Options. The easiest upgrade!

If you have other popular SEO plugins activated, this plugin will automatically prevent SEO mistakes by deactivating itself on almost every part.
It will however output robots metadata, LD+Json and og:image, among various other meta data which are bound to social media.

= Transferring SEO data using SEO Data Transporter =

Because this plugin was initially written to extend the Genesis SEO, it uses the same option name values. This makes transferring from Genesis SEO to The SEO Framework work automatically.

> If you didn't use Genesis SEO previously, Nathan Rice (StudioPress) has created an awesome plugin for your needs to transfer your SEO data.
>
> Get the [SEO Data Transporter from WordPress.org](https://wordpress.org/plugins/seo-data-transporter/).
>
> Usage:<br>
> 1. Install and activate SEO Data Transporter.<br>
> 2. Go to the <strong>SEO Data Transporter menu within Tools</strong>.<br>
> 3. Select your <strong>previous SEO plugin</strong> within the first dropdown menu.<br>
> 4. Select <strong>Genesis</strong> within the second dropdown menu.<br>
> 5. Click <strong>Analyze</strong> for extra information about the data transport.<br>
> 6. Click <strong>Convert</strong> to convert the data.
>
> The SEO Framework now uses the same data from the new Genesis SEO settings on Posts, Pages and Taxonomies.

= About the sitemap =

The sitemap generated with The SEO Framework is sufficient for Search Engines to find Posts, Pages and supported Custom Post Types throughout your website.
It also listens to the noindex settings on each of the items.
If you however require a more expanded sitemap, feel free to activate a dedicated sitemap plugin. The SEO Framework will automatically deactivate its sitemap functionality when another (known) sitemap plugin is found.
If it is not automatically detected and no notification has been provided on the Sitemap Settings, feel free to open a support ticket and it will be addressed carefully.

The Breadcrumb script generated by this plugin on posts will also make sure Google easily finds related categories which aren't included within the sitemap of this plugin.

= Other notes =

*Genesis SEO will be disabled upon activating this plugin. This plugin takes over and extends Genesis SEO.*

***The Automatic Description Generation will work with any installation, but it will exclude shortcodes. This means that if you use shortcodes or a page builder, be sure to enter your custom description or the description will fall short.***

***The home page tagline settings won't have any effect on the title output if your theme's title output is not written according to the WordPress standards, which luckily are enforced strongly on new WordPress.org themes since recently.***

> <strong>Check out the "[Other Notes](https://wordpress.org/plugins/autodescription/other_notes/#Other-Notes)" tab for the API documentation.</strong>

*I'm aware that radio buttons lose their input when you drag the metaboxes around. This issue is fixed since WordPress 4.5.0 (alpha and later).*
*But not to worry: Your previous setting will be returned on save. So it's like nothing happened.*

== Installation ==

1. Install The SEO Framework either via the WordPress.org plugin directory, or by uploading the files to your server.
1. Either Network Activate this plugin or activate it on a single site.
1. That's it!

1. Let the plugin automatically work or fine-tune each page with the metaboxes beneath the content or on the taxonomy pages.
1. Adjust the SEO settings through the SEO settings page if desired. Red checkboxes are rather left unchecked. Green checkboxes are default enabled.

== Screenshots ==

1. This plugin shows you what you can improve, at a glance. With full color vision deficiency support.
2. Hover over any of the SEO Bar's items to see how you can improve the page's SEO. Red is bad, orange is okay, green is good. Blue is situational.
3. The dynamic Post/Page SEO settings Metabox. This box is also neatly implemented in Categories and Tags.
4. The SEO Settings Page. With over 70 settings, you are in full control. Using the Default Settings and filling in the Knowledge Graph Settings is recommended to do.

== Frequently Asked Questions ==

= Is The SEO Framework Free? =

Absolutely! It will stay free as well, without ads or nags!

= I have a feature request, I've found a bug, a plugin is incompatible... =

Please visit [the support forums](https://wordpress.org/support/plugin/autodescription) and kindly tell me about it. I try to get back to you within 48 hours. :)

= I am a developer, how can I help? =

The SEO Framework is currently a one-man project. However, any input is greatly appreciated and everything will be considered.
Please leave feature requests in the Support Forums and I will talk you through the process of implementing it if necessary.

= I'm not a developer, how can I help? =

A way of donating is available through the donation link on the plugin website.
However, you can also greatly help by telling your friends about this plugin :).

= I want to remove or change stuff, but I can't find an option! =

The SEO Framework is very pluggable on many fields. Please refer to the [Other Notes](https://wordpress.org/plugins/autodescription/other_notes/).
Please note that a free plugin is underway which will allow you to change all filters from the dashboard. No ETA yet.

= No ads! Why? =

Nope, no ads! No nags! No links! Never!
Why? Because I hate them, probably more than you do.
I also don't want to taint your website from the inside, like many popular plugins do.
Read more about this on the [Plugin Guidelines, Section 7](https://wordpress.org/plugins/about/guidelines/).

***But how do you make a living?***

Currently, The SEO Framework is non-profit.
This plugin was first released to the public in March 15th, 2015. From there it has grown, from 179 lines of code, to more than 17100 lines.
With over 600,000 characters of code written, this plugin is absolutely a piece of art in my mind.
And that's what it should stay, (functional) art.
I trust that people are good at heart and will tell their friends and family about the things they enjoy the most, what they're excited about, what they find beneficial or even beautiful.

With The SEO Framework I try to achieve exactly that. It's made with <3.

= Does this plugin collect my data? =

Absolutely not! Read more about this on the [Plugin Guidelines, Section 7](https://wordpress.org/plugins/about/guidelines/).

= Premium version? =

Nope! Only premium extensions. These are being developed.

= If a premium extensions is released, what will happen to this plugin? =

This plugin is built to be an all-in-one SEO solution, so:

1. No advertisements about the premium extensions will be placed within this plugin.
1. No features will be removed or replaced for premium-only features.
1. The premium extensions will most likely only be used for big-business SEO. Which are very difficult to develop and which will confuse most users anyway.

= I've heard about an extension manager, what's that? =

Currently it's not available. When it is, it will allow you to download and activate extensions for The SEO Framework. It will support both multisite and single-site and the registration will be based on the Akismet plugin.

= The sitemap doesn't contain categories, images, news, etc. is this OK? =

This is not a problem. Search Engines love crawling WordPress because its structure is consistent and well known.
If a visitor can't find a page, why would a Search Engine? Don't rely on your sitemap, but on your content and website's useability.

= What's does the application/ld+json script do? =

The LD+Json scripts are Search Engine helpers which tell Search Engines how to connect and index the site. They tell the Search Engine if your site contains an internal search engine, what sites you're socially connected to and what page structure you're using.

= The (home page) title is different from the og:title, or doesn't do what I want or told it to. =

The theme you're using is using outdated standards and is therefore doing it wrong. Inform your theme author about this issue.

Give the theme author these two links: https://codex.wordpress.org/Title_Tag https://make.wordpress.org/themes/2015/08/25/title-tag-support-now-required/

If you know your way around PHP, you can speed up this process by replacing the `<title>some code here</title>` code with `<title><?php wp_title('') ?></title>` within the `header.php` file of the theme you're using.

= The meta data is not being updated, and I'm using a caching plugin. =

All The SEO Framework's metadata is put into Object cache when a caching plugin is available. The descriptions are put into Transients. Please be sure to clear your cache.
If you're using W3 Total Cache you might be interested in [this free plugin](https://wordpress.org/plugins/w3tc-auto-pilot/) to do it for you.

= Ugh, I don't want anyone to know I'm using The SEO Framework! =

Aww :(
Oh well, here's the filter you need to remove the HTML tags saying where the Meta tags are coming from:
`add_filter( 'the_seo_framework_indicator', '__return_false' );`

= I'm fine with The SEO Framework, but not with you! =

Well then! D: We got off on the wrong foot, I guess..
If you wish to remove only my name from your HTML code, here's the filter:
`add_filter( 'sybre_waaijer_<3', '__return_false' );`

= I want to transport SEO data from other plugins to The SEO Framework, how do I do this? =

Please refer to this small guide: [SEO Data Migration](http://theseoframework.com/docs/seo-data-migration/).
Transporting Terms and Taxonomies SEO data isn't supported.

== Changelog ==

= 2.6.0 - Pragmatic Foundation =

/
** TODO TESTING CHECKLIST **
/
* Custom Post Type Archives.
* Descriptions are what they said to be.
* AutoDescriptions ending with '.', ',', '!' and '?'.
* Breadcrumb validation on posts and products with multiple categories.
* Schema sitename
* Hundreds of filters... ugh :).
* The SEO Bar colors, in many situations.
* Title lengths.
* Description lengths.
* Post Author SEO settings.
* Pre-"The SEO Framework" Genesis data.

**Preamble:**

* This is a dot version bump update, which is done so as the core code has been changed drastically. **Thirteen new classes** have been added to maintain structured code, including many more function to fetch data easily and consitently.
* With hundreds of changes, I had to find a new way to present this update in an understandable manner. So here goes!

**Summarized:**
/
* With over 160 notable changes, I bring you a new Pragmatic Foundation.
* At long last, something that was planned for almost half a year, **Author SEO** has finally been included, this affects all posts when set up.
* As the issue of the incorrect title length has finally been found, this update glorifies its plugin's **title counter** once more.
* Also, many minor **translation improvements** have been put in place on many places. And **WPML compatibility** has received a rework, now all canonical URL's in the sitemap and front-end are always correct.
* A new script has been added on the front-page. This will make sure the Breadcrumb homepage name will be correct in the Search Engine Results Page.
* Most importantly, this update allows you to be better informed about your website's index status, through the much improved SEO bar.
* The breadcrumb script has been expanded to work on posts with multiple and nested categories. These scripts now also work on WooCommerce products. So don't be surprised if you suddenly have all kinds of scripts in the header! These scripts help Google better understand your website.
* And for developers, with the code expanding rapidly, this update brings new light to the code by **reorganizing the code into dedicated classes** including major refactorization.

**Feature highlights:**
/
* Author Twitter and Facebook connections for posts.
* WooCommerce schema.org breadcrumbs.
* Intelligently nested schema.org breadcrumbs.
* Definite schema.org website name.
* Better Automated Description sentence endings.
* TODO New Archive title prefix options.
* Yandex Sitemap pinging support.
* Extended Title Fix extension support.
* Improved performance, TODO more efficient cache key generation.
* TODO New Date output options.
* TODO Canonical scheme protocol options.
* Many linguistic improvements, with more flow in the SEO Bar.
* Improved editorial translations.
* TODO Smarter and a more vibrant SEO bar, with many more conditional checks.
* Personalized error handling for developers.
* More than 150 new functions for developers.

**SEO Tip of the Update:**
/
* TODO

**Anouncements:**

* I'm announcing a new plugin extension for The SEO Framework! [Title Fix - The SEO Framework](https://wordpress.org/plugins/the-seo-framework-title-fix/).
* This update ensures extra compatiblity with the Title Fix plugin, this will add back removed title features for if the theme is doing it wrong and when the Title Fix plugin is active.

**About: Plugin progression & help wanted:**

* This dear project has taken me over 2500 hours to perfectionize and maintain. This update alone has cost me over 160 hours to complete.
* I really want to keep this project free. In fact, Author SEO was actually planned to be a premium extension.
* I also want to turn this project into my full-time job, even if it's free from monetization and/or premium content.
* And I will keep my promises, always: This plugin will stay free of ads, (self-)reference and this plugin will continue to be maintained.
* All with all, this means I can't do it without your help!
* Please consider making a donation. The link can be found on the WordPress.org plugin page. Or [here](https://theseoframework.com/?p=572).
* Thank you so much for your support!

**About: Changes in Changelog**

* I love to push many changes at once every update, as I'm only happy when everything works. If I find a bug, I'll be sure to fix it!
* So to clean up the massive changelogs, detailed information on updates are put aside and are visible on the plugin's website.
* With each update, I try to find better ways of presenting information and I try to minimize confusion.
* Putting everything in categorized lists is one way of presenting information, to erase sentence flow and to prevent misunderstanding.

**For everyone - About Author SEO:**
/
* TODO When a post has an author assigned, and the author SEO has been filled in, the post will obtain the Author information.
* TODO If there's no Author information set, or the default Social Meta Settings will be used.

**For everyone - About Canonical SEO:**
/
* TODO A new setting has been added to adjust the preffered URL scheme of your website.
* TODO This new setting influences which page Search Engines index. Default (and recommended) is Automatic.
* TODO This new setting doesn't affect scheme redirection of pages and posts, which should be done using `.htaccess`.

**For everyone - About Schema Markup:**
/
* New schema markup has been added, this helps Search Engines better understand your website.
* Breadcrumbs have been expanded, to support nested categories and multiple categories. Now you can see multiple breadcrumb scripts to help Search Engines better understand your website's structure.
* Breadcrumbs scripts now also work on WooCommerce products, enjoy!
* Note: If you use PHP 5.3 or later, the nested scripts provide a more consitent (yet negligible) structure.

**For everyone - About translations:**
/
* Objective translations for grammatically gender noun types like "this post" (male in Dutch) and "this page" (genderless in Dutch) within sentences which are fetched dynamically (like "Product" and "ProductTag" for WooCommerce) couldn't be translated correctly.
* Therefore, I've exchanged these types of sentences without losing understanding of those. TODO by adding plural forms of such.
* Small changes within translations happen over time and I try to reduce it when to only when nessecary, as this is an ongoing project you can expect continous improvements wherever possible. Translating WordPress and its plugins are a team effort :).
* Other small changes include conversion of WordPress slang to real English. Like "Paged" to "Paginated".
* Over time, inconsitencies have been created with the language used within this plugin. If you still find any, please notify me through the support forums and I'll address them.
* Thanks @pidengmor for many (over 30) linguistic improvements, they're really appreciated! Thank you so much for your time!
* I also want to make a big shout out to all the other translators who have contributed to this plugin! <3

**For developers - About class changes:**
/
* This plugin should always be extended with the use of filters, actions or with the use of the Settings API.
* This plugin relies on a God class, which has been put into a cached function `the_seo_framework()`.
* Interacting with the class functions statically could lead to duplicated constructor output and fatal errors.
* Therefore it's always implied and recommended to use `the_seo_framework()` function if you need to interact with any of the functions.
* If done correctly, whichever update comes in the future won't break the site(s).

**For developers - About the new functions:**
/
* TODO In order to maintain a stable future, all dynamic generation output that depends on a setting are put in functions.
* TODO This also counts for filters. To prevent and fix miscalculations.

**For developers - Performance, improved:**

* I've tested out some yet undiscovered ground on PHP while doing millions of iterations in the benchmark.
* These benchmarks can be found in the aptly named `benchmark.php` file.
* What I found is that an if-statement looks passes if the value is a boolean. So converting any value to a boolean will improve its speed.
* I also found that strict false checks are faster than using an exclamation mark. E.g. `false === $thing` is faster than `! $thing`, as the latter flips the boolean, which requires processing power.
* These methodologic improvements have put into effect throughout the whole plugin, with this you will notice an extremely minor performance improvement. But every little bit matters in a framework.
* Because this plugin has grown massively in size in this update, the memory use has been increased by a negligible 500kiB.
* The netto outcome of this plugin's performance in this update is about 0%, this is because many more items are being rendered, even though overall performance has been improved.

**For developers - Refactoring classes:**

* The classes `AutoDescription_DoingItRight`, `AutoDescription_Generate_Description`, `AutoDescription_Generate_Url`, `AutoDescription_Generate_Ldjson`, and `AutoDescription_Generate_Title` have been greatly refactored to improve performance and maintainability.
* All initialization functions have maintained their initial behaviour.

**Detailed log:**

***There are a lot more changes in this update which did not make it to this page. Please refer to [the detailed changelog](https://theseoframework.com/?p=xxx).***

**Many, many other - both minor and major - changes did not make it to the detailed log. For example, when a new function has been added to check for a state, the use of it goes throughout the plugin, effectively added many changes.**

*What do you think of this change? Let me know on [Slack](https://wordpress.slack.com/messages/@cybr/) (no support questions there, please)!*

**About: Support and Social Requests**

* I've noticed a great increase in both friend/connection requests and support questions through Facebook and LinkedIn. Please note that these pages are for friends and (past-)colleagues only.
* Please do not send me a mail through the contact form on `theseoframework.com` unless explicitely asked for or referred to. This form is for personal and/or data sensitive support only. I will reply, but my time is better spent otherwise, see next two points.
* Please refer to the [Support Forums](https://wordpress.org/support/plugin/autodescription) for all your support questions, I'd be glad to help you out!
* This way, everyone can benefit from these support questions.

**For everyone:**
/
* **Added:**
	/
	* TODO Author SEO!
	* TODO Author SEO can only be updated by Authors (self), Editors (self) and Admins (all).
	* TODO Canonical SEO!
	* TODO Canonical SEO scheme options, default "automated".
	* TODO Archive title prefix options!
	* LD+Json Schema.org website name and URL header markup on the front-page. This should change the `example.com > category > subcategory` output in Google to `Example Site > category > subcategory`. See [this page](https://developers.google.com/structured-data/site-name) for more info.
	* TODO (Filter default true?) Description option to remove the Blogname and title when excerpt is set (when excerpt is supported).
	* TODO Timing dropdown options for the sitemap. Now you can select how the time is output. Default Date + Time for new installations, data for old.
	* TODO Article modified time output can now be adjusted, just like the sitemap timing options.
	* TODO Per page title additions options (reverse of global settings with doing it right listener).
	* Removal of the three dots after the description if the excerpt ends with a dot, question mark or exclamation point.
	* Removal commas if the excerpt ends with one in the automated description.
	* Extra compatibility for when the theme is doing it wrong, for when the Title Fix extension plugin has been used.
	* Article Modified Time now also works for WooCommerce products. TODO test and compare
	* TODO Headway compatibility. Done by removing of the SEO features and their output to prevent SEO conflict when filters are used.
	* Yandex sitemap pinging support. TODO test and var_dump
	* Lowered pinging response time to 3s from 5s, to reduce max script time to 12s from 20s.
	* The SEO Bar now has a Double Title check (will appear red). This will make sure that you can see where the copy of SEO data went wrong.
	* The SEO Bar now also checks for global category and tag indexing, following and archiving options on the applicable pages.
	* The SEO Bar now has breaks in the description at various places, to impact behavior through glances.
	* The SEO Bar Indexing notice turns red if Indexing has been enabled, yet the blog isn't set to public.
	* The SEO Bar Following notice turns yellow if Following has been disabled, yet the blog isn't set to public.
	* The SEO Bar Archiving notice turns yellow if Archiving has been enabled, yet the blog isn't set to public.
	* The SEO Bar Categories and Tags Robots options now reflect in the SEO bar.
	* TODO The SEO Bar and the character counters have received an extra sub-green color, for when the lengths are between falling between okay and good.
	* Non-executive `index.php` files in folders which contain readable files, to prevent indexing of such.
	* WooCommerce breadcrumb support! TODO test nested
	* Nested post categories now also have a breadcrumb script. Multiple even, when applicable.
	* TODO Twitter card plugin detection and subtle notification of such.
	* TODO Neatly styled sitemaps, I hope you like it!
* **Changed:**
	/
	* TODO New Plugin Logo!
	* TODO New Plugin Banner, now this one can be shared peacefully through Facebook (which crops and centers the image).
	* Description "good" detection length range has been extended to 137 minimum instead of 145, to eliminate over-optimization.
	* LD+Json markup now uses double quotes instead of single.
	* LD+Json Sitelinks Search Box script now excludes the Alternative Name, as it's optional and non-configurable (yet).
	* The SEO Bar T/D and G letters have a space removed between them to make it a little more appealing on smaller screens.
* **Updated:**
	/
	* Several sentences to have a better English structure to what they do.
	* TODO .POT translation file.
* **Reworked:**
	/
	* URL generation.
	* WPML URL generation, it's now much more consistent and will now also work with custom language settings. It will now also show the correct URL in admin at all times, moreover, it will with subdomains too.
	* WPML shortlink URL now also redirect correctly when visited in special scenarios.
	* Massively improved LD-json script generation time.
* **Improved:**
	/
	* SEO Bar hover balloon translations, **"but"** now can't show up twice, and is instead replaced with **"and"**. E.g. "But the blog isn't set to public. And there are no posts..."
	* Translations with multiple variable strings can now safely be translated and switched around.
	* TODO The canonical URL now also allows page pagination.
	* LD+Json transient is also flushed on change within the SEO Settings page when the home page is a blog.
	* Robots tag generation time.
	* Removed break (`<br />`) closing tag throughout the plugin as we're not using XHTML in WordPress.
* **Fixed:**
	/
	* Added back Genesis schema.org `<head>` markup indicator on the home page.
	* Post type support check was run throughout the WordPress admin dashboard, now it only checks if there are actually posts to check.
	* WPML query args canonical pagination links.
	* Plausible description cache conflict when the home page has been switched from page to blog in the SEO settings page.
	* WPML URL base structure check was done wrongfully, the canonical URL's are now fixed for multilingual pages.
	* Incorrect title counter length on all posts and pages when the home page title tagline has been removed. This issue was first encountered in version 2.5.0.
	* The removal of title additions now correctly reflect on the title counter length when JavaScript is disabled.
	* The removal of title additions are now also reflecting its setting on the placeholders within categories and tags.
	* Some deprecated functions gave a fatal error or warning, this has been resolved.
	* TODO When Reading Settings' Feed Options are set to summary, the backlink is still shown when enabled.
	* TODO When Reading Settings' Feed Options are set to summary, the excerpt generation is disabled.
* **Removed:**
	* Shortlink URL from home page, as it's quite useless there.
	* Page navigation confirmation warning when deleting post.

**For developers:**
/
* **Added:**
	/
	* New filters! See **Filter Notes** below.
	* More than 150 new functions to make this plugin more maintainable.
	* TODO The complete LD+Json output can now be disabled through a single filter.
	* `AutoDescription_Core` class, this class replaced `AutoDescription_Init` from being the latest class.
	* `AutoDescription_Generate_Description` class.
	* `AutoDescription_Generate_Title` class.
	* `AutoDescription_Generate_Url` class.
	* `AutoDescription_Generate_Ldjson` class.
	* `AutoDescription_Generate_Image` class.
	* `AutoDescription_Generate_Author` class.
	* TODO `AutoDescription_Author` class.
	* TODO `AutoDescription_PostInfo` class.
	* `AutoDescription_Compat` class.
	* `AutoDescription_TermInfo` class.
	* `AutoDescription_Debug` class.
	* `AutoDescription_Query` class.
	* `AutoDescription_Admin_Init::localize_admin_javascript()` function.
	* `AutoDescription_Generate_Title::get_the_real_archive_title()` function, which also works in the admin area and has a term object argument and outputs no HTML, effectively speading the whole plugin up on archive pages.
	* `AutoDescription_Detect::current_theme_supports_title_tag()` function, returns cached true if theme supports title tag.
	* Customized error handlers.
	* `THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS` boolean constant listener to disable transients.
	* Extra `$post_id` arguments, see **Filter notes** for more information below.
	* TODO `AutoDescription_Core::get_the_real_ID()` now always returns 0 on Archives instead of the Archive ID. Use `AutoDescription_Core::get_the_real_archive_ID()` instead.
* **Changed:**
	/
	* `AutoDescription_Core::post_type_support()` now has an array argument parameter.
	* `AutoDescription_Core::get_the_real_ID()` now returns 0 instead of false if no ID is found.
* **Updated:**
	/
	* JS files and cache.
	* CSS files and cache.
* **Reworked:**
	/
	* Class structure and order.
	* `AutoDescription_DoingItRight` and all its contents.
	* `AutoDescription_Generate_Description` and all its contents.
	* TODO `AutoDescription_Transients::generate_cache_key()`, it's much faster now.
	* TODO `AutoDescription_Generate_Url::the_url` function, by splitting it into multiple functions, again.
	* `AutoDescription_Generate_Url` and all its contents.
	* `AutoDescription_Generate_Ldjson` and all its contents.
	* `AutoDescription_Generate_Title` and all its contents.
	* Functions have been put in their respective aptly named classes where applicable.
	* Debugging has been modified to clean up the code greatly.
* **Improved:**
	/
	* Hundreds of type optimization checks in if-statements, not only making it more readable, but also two to twenty time less taxing on the CPU per optimization (count the Herz!).
	* Cached the SEO bar translations.
	* render.class.php filters can now return empty string to disable its output.
	* `The_SEO_Framework_Load::call_function()` consumes less memory.
	* `the_seo_framework_knowledgegraph_settings_tabs` filter now has an argument parameter.
	* `the_seo_framework_sitemaps_settings_tabs` filter now has an argument parameter.
	* `the_seo_framework_social_settings_tabs` filter now has an argument parameter.
	* `the_seo_framework_add_blogname_to_title` filter now has effect throughout the plugin.
	* `the_seo_framework_canonical_force_scheme` filter now works on all URL's generated by this plugin and has now an `$scheme` argument which passes the current used scheme.
	* WordPress Query detection.
	* Blog page and Home Page query detection.
	* When the paged URL's filter is used, the then useless url's aren't rendered.
	* Benchmarks have shown that an array flip to use an isset match only benefits huge arrays very little and only when you're certain the result is at the end of the array. Otherwise, it's a drastic performance decrease. Therefore `$this->is_array()` calls have been set back to the default PHP behaviour.
* **Fixed:**
	/
	* `the_seo_framework_dot_version` now checks for four dot versions if applicable.
	* `AutoDescription_Core::get_the_real_ID()` won't return the latest post ID anymore on taxonomial achives.
	* `AutoDescription_Generate_Image::get_image()` now returns something when the third parameter is set to false.
* **Deprecated:**
	/
	* `AutoDescription_Detect::current_theme_supports()`, use core `current_theme_supports` instead.
	* Second parameter for `AutoDescription_Generate_Url::the_url()`, use $args['id'] instead.
	* `AutoDescription_Debug::echo_debug_information()` function, replaced by get `AutoDescription_Debug::get_debug_information()`.
* **Removed:**
	/
	* Open Graph plugins check from Canonical URL output, these are unrelated.
	* Filter/Constant/Action change PHP comments from 2.3.0 to clean up code.
	* Transient for title doing it right is now not anymore set if the theme is doing it right to reduce extra database calls. This caused the transient sometimes to be true regardless.
* **Other:**
	* Cleaned up code, massively.
* **Filter Notes:**
	/
	* **New:**
		/
		* `(string) the_seo_framework_shortlink_output`
		* `(string) the_seo_framework_robots_output`
		* `(string) the_seo_framework_paged_url_output`
		* `(string) the_seo_framework_ldjson_scripts`
		* `(bool) the_seo_framework_json_name_output`
		* TODO `(string) the_seo_framework_pre_add_title`
		* TODO `(string) the_seo_Framework_pro_add_title`
	* **Altered:**
		/
		* `(string) the_seo_framework_og_image_after_featured`, added `$post_id` parameter.
		* `(string) the_seo_framework_og_image_after_header`, added `$post_id` parameter.
		* `(string) the_seo_framework_description_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_ogdescription_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_oglocale_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_ogtitle_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_ogtype_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_ogimage_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_ogsitename_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_twittercard_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_twittersite_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_twittercreator_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_twittertitle_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_twitterdescription_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_twitterimage_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_facebookauthor_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_facebookpublisher_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_facebookappid_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_publishedtime_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_modifiedtime_output`, added `$post_id` parameter.
		* `(bool) the_seo_framework_output_canonical`, added `$post_id` parameter.
		* `(string) the_seo_framework_ldjson_scripts`, added `$post_id` parameter.
		* `(string) the_seo_framework_googlesite_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_bingsite_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_pintsite_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_robots_meta`, added `$post_id` parameter.
		* `(string) the_seo_framework_shortlink_output`, added `$post_id` parameter.
		* `(string) the_seo_framework_paged_url_output`, added `$post_id` parameter.
		* `(array) the_seo_framework_robots_settings_tabs`, added `$args` parameter.
		* `(array) the_seo_framework_social_settings_tabs`, added `$args` parameter.
		* `(array) the_seo_framework_knowledgegraph_settings_tabs`, added `$args` parameter.
		* `(array) the_seo_framework_sitemaps_settings_tabs`, added `$args` parameter.
* **Constant Notes:**
	/
	* **New:**
		/
		* `(bool) THE_SEO_FRAMEWORK_DISABLE_TRANSIENTS`. Note: Does not disable transients for pinging search engines
		* `(bool) THE_SEO_FRAMEWORK_PROFILE`. Profiles the plugin (in the future, maybe).
		* `(bool) THE_SEO_FRAMEWORK_PROFILE_SPECIFIC`. Profiles the plugin more specifically (in the future, maybe).
* **Notes:**
	/
	* I marked a few functions with `@access private`. These functions can change behaviour at any time and should never be used in extension plugins, even though publically accessible.
	* TODO Next improvement also counts "for everyone"?, split it?
	* TODO Improved: When settings are hidden because of theme or plugin (in)compatibilities, or because a settings box is removed through a filter or action, the previous value is maintained on save.

= Full changelog =

**The full changelog can be found [here](http://theseoframework.com/?cat=3).**

== Other Notes ==

= Filters =

= Add any of the filters to your theme's functions.php or a plugin to change this plugin's output. =

Learn about them here: [The SEO Framework filters](http://theseoframework.com/docs/api/filters/)

= Constants =

= Overwrite any of these constants in your theme's functions.php or a plugin to change this plugin's output by simply defining the constants. =

View them here: [The SEO Framework constants](http://theseoframework.com/docs/api/constants/)

= Actions =

= Use any of these actions to add your own output. =

They are found here: [The SEO Framework actions](http://theseoframework.com/docs/api/actions/)

= Settings API =

= Add settings to and interact with The SEO Framework. =

Read how to here: [The SEO Framework Settings API](http://theseoframework.com/docs/api/settings/)

= Beta Version =

= Stay updated with the latest version before it's released? =

If there's a beta, it will be available [on Github](https://github.com/sybrew/the-seo-framework). Please note that changes there might not reflect the final outcome of the full version release.
