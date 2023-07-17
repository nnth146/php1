<?php $product = $inputs["product"]; ?>

<td data-label="Product name">
    <?php echo $product["name"] ?>
</td>
<td data-label="SKU">
    <?php echo $product["sku"] ?>
</td>
<td data-label="Price">
    <?php echo "$" . number_format(((float) $product["price"]), 2); ?>
</td>
<td data-label="Feature Image">
    <?php if (!empty($product["feature_image"])): ?>
        <img src="<?php echo $product["feature_image"] ?>" class="ui small image m-auto">
    <?php endif ?>
</td>
<td data-label="Gallery">
    <div class="gallery">
        <?php if (isset($product["gallery"])): ?>
            <?php foreach ($product["gallery"] as $url): ?>
                <img class="ui tiny image" src="<?php echo $url; ?>" alt="No Image">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</td>
<td data-label="Categories">
    <?php if (isset($product["categories"])): ?>
        <?php echo $product["categories"]; ?>
    <?php endif ?>
</td>
<td data-label="Tags">
    <?php if (isset($product["tags"])): ?>
        <?php echo str_replace(',', ', ', $product["tags"]); ?>
    <?php endif ?>
</td>