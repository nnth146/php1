<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php require_once "resources/views/partials/public.html"; ?>

    <link rel="stylesheet" href="/php1/resources/css/products.css">

    <title>PHP1</title>
</head>

<body>
    <form>
        <input type="text" name="action" value="createp" hidden>
        <input type="text" value="id" name="id" hidden>
        <div class="ui input">
            <input type="date" placeholder="Search... ">
        </div>
        <div class="ui selection dropdown">
            <input type="hidden" name="field">
            <i class="dropdown icon"></i>
            <div class="default text">Gender</div>
            <div class="menu">
                <div class="item" data-value="Date">Date</div>
                <div class="item" data-value="Product">Product name</div>
                <div class="item" data-value="ABC">ABC</div>
            </div>
        </div>
        <div class="ui icon input input__search">
            <input type="text" placeholder="Search...">
            <i class="inverted circular search link icon"></i>
        </div>
        <button class="ui button">Submit</button>
    </form>
</body>

</html>