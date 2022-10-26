define(['jquery'], function ($) {
    return function (config) {
        $(document).on('update-configurable-options', 'body', function (event, data) {
            let productId = data[0];
            if (!productId) return ;
            _cdp365Analytics.items = {
                item_type: "product",
                brand: config.brand,
                id: config.data[productId].id,
                name: config.data[productId].name,
                orginal_price: config.data[productId].original_price,
                price: config.data[productId].price,
                page_url: config.data[productId].page_url,
                image_url: config.data[productId].image_url,
                main_category:  config.data[productId].main_category,
                category_level_1: config.data[productId].category_level_1,
                category_level_2: config.data[productId].category_level_2
            };
        });

        if (typeof(_cdp365Analytics) === 'undefined') {
            _cdp365Analytics = {};
        }
        _cdp365Analytics.items = {
            item_type: "product",
            brand: config.brand,
            id: config.id,
            name: config.name,
            orginal_price: config.original_price,
            price: config.price,
            page_url: config.page_url,
            image_url: config.image_url,
            main_category:  config.main_category,
            category_level_1: config.category_level_1,
            category_level_2: config.category_level_2
        }
    };
});
