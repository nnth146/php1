<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php require_once "resources/views/partials/public.html"; ?>

    <link rel="stylesheet" href="/php1/resources/css/products.css">

    <script type="module" src="/php1/resources/js/products.js"></script>

    <title>PHP1</title>
</head>

<body>
    <div class="main">
        <div class="ui header">
            <?php echo $inputs["header"] ?>
        </div>
        <form action="<?php echo !isset($inputs['id']) ? '?action=create' : ('?action=edit&id=' . $inputs['id']) ?>"
            class="ui form" method="post" enctype="multipart/form-data">
            <div class="field">
                <label>Product name</label>
                <div class="ui input">
                    <input type="text" name="name" id="name" placeholder="Enter name..."
                        value="<?php echo $inputs["name"] ?? '' ?>">
                </div>
                <div class="input__error">
                    <?php if (isset($inputs["name-error"]) && is_array($inputs["name-error"])): ?>
                        <?php foreach ($inputs["name-error"] as $error): ?>
                            <div>
                                <?php echo $error ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="two fields">
                <div class="field">
                    <label>SKU</label>
                    <div class="ui input">
                        <input type="text" name="sku" id="sku" placeholder="Enter sku..."
                            value="<?php echo $inputs["sku"] ?? '' ?>">
                    </div>
                    <div class="input__error">
                        <?php if (isset($inputs["sku-error"]) && is_array($inputs["sku-error"])): ?>
                            <?php foreach ($inputs["sku-error"] as $error): ?>
                                <div>
                                    <?php echo $error ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="field">
                    <label>Price ($)</label>
                    <div class="ui input">
                        <input type="text" name="price" id="price" placeholder="Enter name..."
                            value="<?php echo $inputs["price"] ?? '' ?>">
                    </div>
                    <div class="input__error">
                        <?php if (isset($inputs["price-error"]) && is_array($inputs["price-error"])): ?>
                            <?php foreach ($inputs["price-error"] as $error): ?>
                                <div>
                                    <?php echo $error ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="field">
                <label>Category</label>
                <select class="ui fluid dropdown" multiple name="category[]" id="category">
                    <?php if (isset($inputs["categories"]) && is_array($inputs["categories"])): ?>
                        <option value="">Choose Category</option>
                        <?php foreach ($inputs["categories"] as $category): ?>
                            <option value="<?php echo $category["name"]; ?>" <?php echo inArray($inputs['category'] ?? [], $category['name']) ? 'selected' : '' ?>>
                                <?php echo $category["name"]; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="field">
                <label>Tag</label>
                <select class="ui fluid dropdown" multiple="" name="tag[]" id="tag">
                    <option value="">Choose Tag</option>
                    <?php if (isset($inputs["tags"]) && is_array($inputs["tags"])): ?>
                        <?php foreach ($inputs["tags"] as $tag): ?>
                            <option value="<?php echo $tag["name"]; ?>" <?php echo inArray($inputs['tag'] ?? [], $tag['name']) ? 'selected' : '' ?>>
                                <?php echo $tag["name"]; ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            <div class="two fields">
                <div class="field">
                    <label>Feature Image</label>
                    <div class="ui action input">
                        <input id="feature_image-name" type="text" placeholder="Upload feature image" readonly>
                        <label class="ui button">
                            Uploads
                            <input name="feature_image" id="feature_image" type="file" hidden>
                        </label>
                    </div>
                    <div class="input__error">
                        <?php if (isset($inputs["feature_image-error"]) && is_array($inputs["feature_image-error"])): ?>
                            <?php foreach ($inputs["feature_image-error"] as $error): ?>
                                <div>
                                    <?php echo $error ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="preview" id="feature_image-preview">
                        <?php if (!empty($inputs["feature_image"])): ?>
                            <img src="<?php echo "/php1/" . $inputs["feature_image"] ?>" class="ui medium image">
                            <input type="text" name="old-feature-image" value="void" hidden checked>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="field">
                    <label>Gallery</label>
                    <div class="ui action input">
                        <input id="gallery-name" type="text" placeholder="Upload gallery" readonly>
                        <label class="ui button">
                            Uploads
                            <input name="gallery[]" id="gallery" type="file" multiple hidden>
                        </label>
                    </div>
                    <div class="input__error">
                        <?php if (isset($inputs["gallery-error"]) && is_array($inputs["gallery-error"])): ?>
                            <?php foreach ($inputs["gallery-error"] as $error): ?>
                                <div>
                                    <?php echo $error ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="preview" id="gallery-preview">
                        <?php if (isset($inputs["gallery"]) && is_array($inputs["gallery"])): ?>
                            <?php foreach ($inputs["gallery"] as $url): ?>
                                <img class="ui small image" src="<?php echo "/php1/" . $url; ?>">
                            <?php endforeach; ?>
                            <input type="text" name="old-gallery-image" value="void" hidden checked>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="field">
                <button class="positive ui button">Accept</button>
                <a href="/php1" class="negative ui button">Cancel</a>
            </div>
        </form>
    </div>
</body>

</html>