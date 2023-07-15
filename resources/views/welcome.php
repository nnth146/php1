<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php require_once "resources/views/partials/public.html"; ?>

    <link rel="stylesheet" href="/php1/resources/css/welcome.css">
    <link rel="stylesheet" href="/php1/resources/css/products.css">
    <link rel="stylesheet" href="/php1/resources/css/properties.css">

    <script type="module" src="/php1/resources/js/welcome.js"></script>
    <script type="module" src="/php1/resources/js/products.js"></script>
    <script type="module" src="/php1/resources/js/properties.js"></script>

    <title>PHP1</title>
</head>

<body>
    <div class="main">
        <div class="title">PHP1</div>
        <form id="filter-form" class="filter">
            <?php if (isset($inputs["page"])): ?>
                <input type="text" name="page" value="<?php echo $inputs["page"]; ?>" hidden>
            <?php endif; ?>
            <div class="grid-2">
                <div class="flex flex--small-gap">
                    <a id="addproduct-btn" href="?action=create" class="ui primary button">Add product</a>
                    <a id="addproperty-btn" href="?action=properties" class="ui button">Add property</a>
                    <div id="sync-btn" class="ui button sync-btn">Sync from Villatheme</div>
                </div>
                <div class="ui icon input">
                    <input type="text" name="search" placeholder="Search product"
                        value="<?php echo $inputs['search'] ?? '' ?>">
                    <i id="search-btn" class="inverted circular search link icon"></i>
                </div>
            </div>
            <div class="flex flex--medium-gap flex--wrap">
                <div class="ui selection dropdown">
                    <input type="hidden" name="orderby" value="<?php echo $inputs['orderby'] ?? 'date' ?>">
                    <i class="dropdown icon"></i>
                    <div class="default text"></div>
                    <div class="menu">
                        <div class="item" data-value="date">Date</div>
                        <div class="item" data-value="name">Product name</div>
                        <div class="item" data-value="price">Price</div>
                    </div>
                </div>
                <div class="ui selection dropdown">
                    <input type="hidden" name="order" value="<?php echo $inputs['order'] ?? 'asc' ?>">
                    <i class="dropdown icon"></i>
                    <div class="default text"></div>
                    <div class="menu">
                        <div class="item" data-value="asc">ASC</div>
                        <div class="item" data-value="desc">DESC</div>
                    </div>
                </div>
                <div class="ui selection dropdown" id="category">
                    <input type="hidden" name="category" value="<?php echo $inputs['category'] ?? '' ?>">
                    <i class="dropdown icon"></i>
                    <div class="default text">Category</div>
                    <div class="menu">
                        <?php foreach ($inputs["categories"] as $category): ?>
                            <div class="item" data-value="<?php echo $category["name"] ?>"><?php echo $category["name"] ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="ui selection dropdown" id="tag">
                    <input type="hidden" name="tag" value="<?php echo $inputs['tag'] ?? '' ?>">
                    <i class="dropdown icon"></i>
                    <div class="default text">Tag</div>
                    <div class="menu">
                        <?php foreach ($inputs["tags"] as $tag): ?>
                            <div class="item" data-value="<?php echo $tag["name"] ?>"><?php echo $tag["name"] ?></div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="ui input">
                    <input type="date" name="datefrom" placeholder="Date From"
                        value="<?php echo $inputs['datefrom'] ?? '' ?>">
                </div>
                <div class="ui input">
                    <input type="date" name="dateto" placeholder="Date To"
                        value="<?php echo $inputs['dateto'] ?? '' ?>">
                </div>
                <div class="ui input">
                    <input type="text" name="pricefrom" placeholder="Price From"
                        value="<?php echo $inputs['pricefrom'] ?? '' ?>">
                </div>
                <div class="ui input">
                    <input type="text" name="priceto" placeholder="Price To"
                        value="<?php echo $inputs['priceto'] ?? '' ?>">
                </div>
                <button id="filter-btn" class="ui button">Filter</button>
            </div>
        </form>

        <div class="scrollable mt-small">
            <table class="ui celled table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Product name</th>
                        <th>SKU</th>
                        <th>Price</th>
                        <th>Feature Image</th>
                        <th>Gallery</th>
                        <th>Categories</th>
                        <th>Tags</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($inputs["products"]) && is_array($inputs["products"])): ?>
                        <?php foreach ($inputs["products"] as $product): ?>
                            <tr>
                                <td data-label="Date">
                                    <?php echo $product["date"] ?>
                                </td>
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
                                            <?php $gallery = explode("|", $product["gallery"]); ?>
                                            <?php foreach ($gallery as $url): ?>
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
                                <td data-label="Action">
                                    <div class="flex flex--gap-4px">
                                        <a id="editproduct-btn" href="<?php echo '?action=edit&id=' . $product["id"] ?>">
                                            <svg class="small" xmlns="http://www.w3.org/2000/svg" height="1em"
                                                viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                                <path
                                                    d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.7 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z" />
                                            </svg>
                                        </a>
                                        <form name="deleteproduct-form"
                                            action="<?php echo "?action=delete" . (empty($inputs["noActionQuery"]) ? $inputs["noActionQuery"] : "&" . $inputs["noActionQuery"]); ?>"
                                            method="post">
                                            <input type="text" name="id" value="<?php echo $product['id'] ?>" hidden>
                                            <div name="delete-btn" class="cursor-pointer">
                                                <svg class="small" xmlns="http://www.w3.org/2000/svg" height="1em"
                                                    viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                                    <path
                                                        d="M135.2 17.7L128 32H32C14.3 32 0 46.3 0 64S14.3 96 32 96H416c17.7 0 32-14.3 32-32s-14.3-32-32-32H320l-7.2-14.3C307.4 6.8 296.3 0 284.2 0H163.8c-12.1 0-23.2 6.8-28.6 17.7zM416 128H32L53.2 467c1.6 25.3 22.6 45 47.9 45H346.9c25.3 0 46.3-19.7 47.9-45L416 128z" />
                                                </svg>
                                            </div>
                                            <div class="ui mini modal">
                                                <div class="header">Notification</div>
                                                <div class="content">
                                                    <p>Are you sure delete product?</p>
                                                </div>
                                                <div class="actions">
                                                    <div class="negative ui button">No</div>
                                                    <button class="positive ui cancel button">Yes</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>



        <?php if ($inputs["links"]): ?>
            <div class="pagination">
                <?php if ($inputs["prevPage"] != $inputs["currentPage"]): ?>
                    <a href="<?php echo "/php1?page=" . $inputs["prevPage"] . (empty($inputs["noPageQuery"]) ? $inputs["noPageQuery"] : "&" . $inputs["noPageQuery"]); ?>"
                        class="pagination__link pagination__link--move">
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em"
                            viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                            <path
                                d="M9.4 233.4c-12.5 12.5-12.5 32.8 0 45.3l160 160c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L109.2 288 416 288c17.7 0 32-14.3 32-32s-14.3-32-32-32l-306.7 0L214.6 118.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0l-160 160z" />
                        </svg>
                    </a>
                <?php endif; ?>
                <?php foreach ($inputs["links"] as $link): ?>
                    <?php $href = "/php1?page=$link" . (empty($inputs['noPageQuery']) ? $inputs['noPageQuery'] : '&' . $inputs['noPageQuery']) ?>
                    <a <?php echo $link != $inputs["currentPage"] ? "href=\"$href\"" : "" ?>
                        class="pagination__link <?php echo $link == $inputs["currentPage"] ? " pagination__link--active" : "" ?>">
                        <?php echo $link ?>
                    </a>
                <?php endforeach ?>
                <?php if ($inputs["nextPage"] != $inputs["currentPage"]): ?>
                    <a href="<?php echo "/php1?page=" . $inputs["nextPage"] . (empty($inputs["noPageQuery"]) ? $inputs["noPageQuery"] : "&" . $inputs["noPageQuery"]); ?>"
                        class="pagination__link pagination__link--move">
                        <svg xmlns="http://www.w3.org/2000/svg" height="1em"
                            viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                            <path
                                d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                        </svg>
                    </a>
                <?php endif; ?>
            </div>
        <?php endif ?>

        <div id="sync-modal" class="ui mini modal">
            <div class="header">Sync Villatheme</div>
            <div class="content">
                <div class="sync__label">
                    <div>Find</div>
                    <div id="find-loader" class="ui active tiny inline loader"></div>
                </div>
                <div id="find-progress" class="ui teal progress">
                    <div class="bar"></div>
                    <div id="find-label" class="label">
                        <div class="progress"></div>
                    </div>
                </div>
                <div class="sync__label">
                    <p>Sync</p>
                    <div id="sync-loader" class="ui tiny inline loader"></div>
                </div>
                <div id="sync-progress" class="ui teal progress">
                    <div class="bar">
                        <div class="progress"></div>
                    </div>
                    <div id="sync-label" class="label"></div>
                </div>
            </div>
            <div class="actions">
                <button id="modal-reset-btn" class="ui approve button">Reset</button>
                <button id="modal-sync-btn" class="ui green approve button">Sync</button>
                <button id="modal-cancel-btn" class="ui cancel button">Cancel</button>
            </div>
        </div>

        <!-- Products -->
        <div id="products-modal" class="ui container large modal"></div>
        <div id="editproducts-modal" class="ui container large modal"></div>
        <div id="properties-modal" class="ui tiny modal"></div>

        <div class="footer"></div>
    </div>
</body>

</html>