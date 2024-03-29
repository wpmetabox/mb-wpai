# Development

## Project Structure
- actions: File based actions hooks, registered by the plugin. The file name is the hook name and the function in the file is the callback.
- classes: Helper classes.
- config: Do nothing, but we keep it to match with the original plugin.
- controllers: Controllers to handle plugin logic.
- filters: Same as actions but applied for filters.
- helpers: Helper functions.
- i18n
- models: Unlike its name, it's actually handle logic like `import`, `parse` data which passed from the controllers, think of them like "Services" or "Repositories" in MVC project.
- src: Register metaboxes and fields logics, like Text field which parse and import Text.
- static: Static assets.
- vendor: Composer vendor.
- views: Views in the dashboard.

## Data Flow
There are two things that we need to handle: rendering views and handling post data.

### Rendering Views
1. From `PMAI_Plugin` class, we register file based hooks in `actions` and `filters` folders.
1. The `pmxi_extend_options_custom_fields.php` file will be used to register the corresponding controller `PMAI_Admin_Import`.
1. The `PMAI_Admin_Import` will prepare data and render the meta boxes in the dashboard.
1. During rendering the view, the JS will call to `wp_ajax_get_meta_boxes.php` to render the fields.

### Handling Post Data
1. When submitting data, `WP All Import` plugin will call all of their "addons". In our case, it will call `PMAI_Plugin` class. Please note that we can't rename it because it only call classes in their list.
1. The `PMAI_Import_Record::parse()` will be called from the WP All Import.
1. The `PMAI_Import_Record::import()` will be called from the WP All Import.

### Testing
The `tests` folder contains some xml files and meta boxes to test the plugin.
It's recommended to use WP CLI to test the output as it's faster than using the dashboard. For example:

```bash
wp all-import run 66 --force-run
```

To make the import run after processed. We can clear the posts table and run the import again.

**PLEASE DO NOT RUN THIS COMMAND IN PRODUCTION SITE.**

```bash
wp db query "TRUNCATE TABLE xp_posts; TRUNCATE TABLE xp_pmxi_posts;"
```

### XPath Guide
To test the xpath, try using either the tool [http://xpather.com/](http://xpather.com/) or [https://extendsclass.com/xpath-tester.html](https://extendsclass.com/xpath-tester.html)

Some useful xpath related to the example:
- `casts[1]/cast/name[1]` - Get first cast name.
- `casts[1]/cast/name[5]` - Get 5th cast name.
- `casts[1]/cast/name` - Get all cast names.
- `casts[1]/cast[.]/name` - Get all cast names but put each cast name to the parent node.
