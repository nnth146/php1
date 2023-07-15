<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php require_once "resources/views/partials/public.html"; ?>

    <link rel="stylesheet" href="/php1/resources/css/properties.css">

    <title>PHP1</title>
</head>

<body>
    <div class="properties__main">
        <div class="ui header">Create Property</div>
        <div class="flex flex--col flex--medium-gap">
            <form id="properties-form" action="?action=properties" method="post" class="ui form">
                <div class="field">
                    <label>Category</label>
                    <div class="ui input w-full">
                        <input type="text" name="category" placeholder="Enter category...">
                    </div>
                    <div class="properties__input__error">
                        <?php if (isset($inputs["category-error"]) && is_array($inputs["category-error"])): ?>
                            <?php foreach ($inputs["category-error"] as $error): ?>
                                <div>
                                    <?php echo $error ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="field">
                    <label>Tag</label>
                    <div class="ui input w-full">
                        <input type="text" name="tag" placeholder="Enter Tag...">
                    </div>
                    <div class="properties__input__error">
                        <?php if (isset($inputs["tag-error"]) && is_array($inputs["tag-error"])): ?>
                            <?php foreach ($inputs["tag-error"] as $error): ?>
                                <div>
                                    <?php echo $error ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="field">
                    <button class="positive ui button w-fit">Add</button>
                    <a id="back-btn" href="/php1/" class="negative ui button w-fit">Back</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>