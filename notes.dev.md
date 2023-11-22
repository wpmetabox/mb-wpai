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

### XPath Guide
To test the xpath, try to use this tool [http://xpather.com/](http://xpather.com/)

Some useful xpath related to the example:
- `casts[1]/cast[*]/name[1]` - Get all cast names of the movies.xml example.
- `casts[1]/cast[*]/name` - Get all cast names of the movies-cast-name-only.xml example.