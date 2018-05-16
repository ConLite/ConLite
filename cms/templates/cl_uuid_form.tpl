<form method="post" action="">
    <fieldset>
        <legend>{$legend}</legend>
        <p>{$description}</p>
        <label for="pluginfoldername">Plugin Folder Name:</label>
        <input id="pluginfoldername" type="text" name="pluginfoldername" />
        <label for="plugincopyright">Plugin Copyright:</label>
        <input id="plugincopyright" type="text" name="plugincopyright" />
        <input type="submit" />
        {if $uuid_generated}
            <p>Generated UUID is: <strong style="padding: 4px;background-color: #66ffff;">{$uuid_generated}</strong></p>
        {/if}
    </fieldset>
</form>