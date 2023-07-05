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
    <div class="main">
        <div class="ui header">Create Property</div>
        <div class="flex flex--col flex--medium-gap">
            <form action="" method="post" class="ui form">
                <div class="field">
                    <label>Category</label>
                    <div class="ui action input w-full">
                        <input type="text" placeholder="Enter category...">
                        <button class="positive ui button">Add</button>
                    </div>
                    <div class="input__error"></div>
                </div>

            </form>
            <form action="" method="post" class="ui form">
                <div class="field">
                    <label>Tag</label>
                    <div class="ui action input w-full">
                        <input type="text" placeholder="Enter Tag...">
                        <button class="positive ui button">Add</button>
                    </div>
                    <div class="input__error"></div>
                </div>
            </form>
            <a href="/php1/" class="negative ui button w-fit">Back</a>
        </div>
    </div>
</body>

</html>