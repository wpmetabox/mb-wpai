# WP All Import - Meta Box Integration
MB WP-AI integrates WP All Import with Meta Box which ported from ACF Integration. It creates a section in WP All Import plugin to map Meta Box's fields to import data. Allows you to handle more complex fields and settings.

![MB WP-AI](https://i.imgur.com/eFByM8U.png)

## Before you start
This plugin mimic the way ACF Integration works and under the hood, so it's recommend you to read [ACF Integration documentation](https://www.wpallimport.com/documentation/how-to-import-advanced-custom-fields-acf-from-csv/) first.

Also, you need to deactivate ACF Integration addon if you're using it.

## Start importing fields
MB WPAI will create a section called "Meta Box Add-On" in the WP All Import, drag and drop page. Same as ACF Integration. In this section, you can drag and drop matching fields to map them together.

### Step 1: Create a new import
Navigate to **All Import › New Import** and upload a valid import file. Our plugin will automatically identify the file type, i.e., CSV, Excel, XML, etc.

Next, choose from the drop-down list the post type that has the Advanced Custom Fields attached.

In this example, we're importing Meta Box fields to **Posts**.
![New Import](https://i.imgur.com/Mptx63n.png)

Click **Continue to Step 2** to move on to the next step.

Keep in mind that your Meta Box fields need to be created and defined for posts prior to importing data.

### Step 2: Review the Import File
The **Review Import File** page appears next. Here, you can review and confirm that the import data looks correct:

![Review Import File](https://i.imgur.com/7yGvA1C.png)

The import has detected 3 records to import. To make sure that the data is correct, you can review each record with the gray arrows at the top.

In this step, you can add filers via the **Manage Filtering Options** section, if they are needed.

This example uses an XML. You can also use spreadsheet files.

Once you confirm that the data looks good, click **Continue to Step 3**.

### Step 3: Map the Incoming Data Elements to Meta Box Fields
This brings you to the Drag & Drop interface, which allows you to map the data elements to their respective target fields.

To map the data, drag each desired data element from the panel on the right to its corresponding field on the left.
![Drag & Drop](https://i.imgur.com/xy2pUGm.png)

The **Meta Box Add-On** section is where you define fields to import.

Locate that section by scrolling down and then enable the group to import:

![Meta Box Add-On](https://i.imgur.com/yJhMFkF.png)

You can map each individual Meta Box field as required:

![MB WP-AI](https://i.imgur.com/eFByM8U.png)

You can also concatenate multiple fields into one Meta Box field, or add any static text to the field.

Once all of the relevant data elements are mapped to their target fields, click on **Continue to Step 4** at the bottom to move to the next step.

Before proceeding, make sure that you map all other fields related to the post type or custom post type that you're importing.

### Step 4: Configure the Remaining Settings and Run the Import
The **Import Settings** interface is where you configure the remaining import settings and other options:

Specifically, you need to define a **Unique Identifier** for the import. WP All Import uses the unique identifier to keep track of imported records. To create it, simply click on **Auto-detect**.

You can also define this unique identifier by manually dragging and dropping fields from the right.

![Import Settings](https://i.imgur.com/jOy3pLT.png)

Here you can modify the import behavior if it is run again (to update, delete, or create new records found on the import file). You can set up **Scheduling Options** and **Configure Advanced Settings**. With most imports, there's no need to modify any of these settings.

Click the blue **Continue** button at the end to move to the next step.

### Step 5: Verify That the Meta Box Fields Were Created Correctly
Next, you see the **Confirm & Run** interface. In the **Import Summary** section, you can review the import, what's in the import file, and other import settings.

![Confirm & Run](https://i.imgur.com/U4gK4qK.png)

If everything looks good, click on **Confirm & Run Import** to start the import.
When it completes, you will see the **Import Complete** screen:

![Import Complete](https://i.imgur.com/EuV1GuY.png)

That's it! The import correctly processed the posts and Meta Box fields from this example.
You can review the imported fields by checking any of the imported posts in **Posts › All Posts**.

## Advanced usage

### Concatenate fields
You can concatenate multiple fields into one Meta Box field, or add any static text to the field. This is useful in many situations when you want to combine multiple fields into one field, or add a prefix or suffix to the field.

For example, you have 2 fields: `first_name` and `last_name` and you want to concatenate them into `full_name` field. You can do it by dragging and dropping the fields to the `full_name` field like this:
Or if you have only file name in the import file, but you want to add the full URL to the field, you can do it like this:

![Concatenate fields](https://i.imgur.com/7Ovqkoj.png)

#### Concatenate cloneable fields
Concatenation also works with cloneable fields. For example, you have a cloneable field `photos` and you want to add the full URL to each photo. You can do it like this:

```
https://photo.example.com/{photos/photo}
```

In this case, the plugin will loop through all photos and add the full URL to each photo.
### Cloneable fields

#### Using "+ Add more" button (Fixed Repeater Mode)
The easiest way to import cloneable fields is using the "+ Add more" button. Simply like how you do in editing post, click the button to add more fields and map them to the import file. This way is suitable for small number of fields, or files with a fixed number of columns.
![Using Add more button](https://i.imgur.com/Q9hYVzP.png)

#### Using selector "all" and "nth" in XPath (Variable Repeater Mode)
If you have a large number of fields, or the number of columns in the import file is dynamic, you can leverage XPath syntax to import them. 
This applied for both cloneable and non-cloneable fields, groups and sub-fields.

For example, you have an XML file with the following structure:

```xml
...
<photos>
    <photo>...</photo>
    <photo>...</photo>
    <photo>...</photo>
</photos>
...
```

To import all photos, you can use the following XPath:

```
{photos/photo}
```

For more information about XPath, please read [W3 Schools tutorial](https://www.w3schools.com/xml/xpath_intro.asp).
To check if your XPath is correct, you can use [XPather](http://xpather.com/).

#### Cloneable Data Placements
By default, all cloneable data are imported to the first group. For example, the previous example will import all photos to the first group.
However, you can change the placement of the data by using the following syntax:

```
{photos[.]/photo}
```
Using the "dot" syntax will tell the plugin to move each item to the parent segment. In this case, it will import each photo to a new group.

![Cloneable Data Placements](https://i.imgur.com/ZLEYpxE.png)

#### Concatenate cloneable fields
We can also concatenate cloneable fields. For example, you have an XML file with the following structure:

```xml
...
<photos>
    <photo>cat.jpg</photo>
    <photo>dog.jpg</photo>
</photos>
```

You can add `https://photo.example.com/{photos/photo}` to the `photos` field to get the full URL of the photo.

### Group fields
Working with group fields is similar to other fields. Except that the group fields itself is a container of other fields so we will have a new special field called "For each" to handle how we import data to the group fields.

#### Map fields to group fields
To map fields to group fields, simply drag and drop the fields to the group fields like we do with other fields.

#### Adding cloneable group (Fixed Repeater Mode)
Cloneable group fields work the same as cloneable fields. You can simply the "+ Add more" button to add more groups and map fields to them.

#### For each syntax (Variable Repeater Mode)
As mentioned above, the group fields is a container of other fields. So we need to tell the plugin how to import data to the group fields. This is done by using the "For each" syntax.

![For each syntax](https://i.imgur.com/IZrykr1.png)

For example, you have an XML file with the following structure:

```xml
...
<photos>
    <photo>
        <title>Cat</title>
        <url>cat.jpg</url>
    </photo>
    <photo>
        <title>Dog</title>
        <url>dog.jpg</url>
    </photo>
</photos>
...
```

To import each photo and its data to a new group, you can use the following XPath for **For each** field:

```
{photos/photo}
```

#### Sub-fields scope
In the previous example, we have 2 sub-fields: `title` and `url`. These sub-fields are only available inside the group fields. So we need to tell the plugin to import them to the group fields. This is done by using the "dot" syntax.

For example, to import the `title` field into sub-field, we can use the following XPath:

```
{.title}
```

Similarly, to import the `url` field into sub-field, we can use the following XPath:

```
{.url}
```

Let's put them together for more clarity:

```
For each: {photos/photo}

We import:
Title: {.title}  (same as {photos/photo/title} but scoped to the group)
URL: {.url} (same as {photos/photo/url} but scoped to the group)
```

**Note:**

- If the sub-fields are not inside the group fields, you can use the normal XPath syntax to import them.
- You are not limited to only direct children xml nodes but also any level inside the group fields. For example: `{.meta/orientation/width}` also works.
- You can also use other features like concatenation, cloneable syntax... with sub-fields.

## FAQ
**Does it work with WP All Import free edition?** 
Yes, it does.