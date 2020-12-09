angular.module('besafe')
        .service('Products', ['$q', '$http', function ($q, $http) {

                var addRating = function (rating) {

                    var def = $q.defer();

                    $http({
                        method: "post",
                        url: "/api/ratings",
                        data: rating
                    })
                            .success(function (data) {
                                // console.log(data);
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to addRating");
                            });

                    return def.promise;
                };
                var addFavorite = function (data) {

                    var def = $q.defer();

                    $http({
                        method: "post",
                        url: "/api/favorites",
                        data: data
                    })
                            .success(function (data) {
                                // console.log(data);
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to addRating");
                            });

                    return def.promise;
                };
                var deleteFavorite = function (data) {

                    var def = $q.defer();

                    $http({
                        method: "post",
                        url: "/api/favorites/delete",
                        data: data
                    })
                            .success(function (data) {
                                // console.log(data);
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to addRating");
                            });

                    return def.promise;
                };
                var checkFavorite = function (rating) {

                    var def = $q.defer();

                    $http({
                        method: "post",
                        url: "/api/products/favorites",
                        data: rating
                    })
                            .success(function (data) {
                                // console.log(data);
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to addRating");
                            });

                    return def.promise;
                };
                var getProductsMerchant = function (data) {
                    var def = $q.defer();
                    $http({
                        method: "GET",
                        url: "/api/merchants/products",
                        params: data
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to getProductsMerchant");
                            });
                    return def.promise;
                };
                var searchProducts = function (data) {
                    var def = $q.defer();
                    $http({
                        method: "GET",
                        url: "/api/products/search",
                        params: data
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                            .error(function () {
                                def.reject("Failed to searchProducts");
                            });
                    return def.promise;
                };
                var buildProduct = function (container, merchant) {
                    let productInfo = {};

                    productInfo.id = container.product_id;
                    productInfo.name = container.prod_name;
                    productInfo.description = container.prod_desc;
                    productInfo.isActive = container.isActive;
                    productInfo.slug = container.slug;
                    productInfo.description_more = false;
                    productInfo.more = false;
                    productInfo.type = container.type;
                    productInfo.merchant_description_more = false;
                    if (merchant) {
                        productInfo.merchant_name = merchant.merchant_name;
                        productInfo.merchant_id = merchant.merchant_id;
                        productInfo.merchant_description = merchant.merchant_description;
                        productInfo.merchant_attributes = merchant.merchant_attributes;
                        productInfo.src = merchant.merchant_icon;

                        productInfo.merchant_type = merchant.merchant_type;
                    }

                    productInfo.inCart = false;
                    if (container.is_on_sale) {

                        productInfo.onsale = true;
                        productInfo.subtotal = productInfo.price;
                        productInfo.exsubtotal = productInfo.exprice;
                    } else {
                        productInfo.subtotal = productInfo.price;
                        productInfo.onsale = false;
                    }
                    productInfo = updateProductVisual(container, productInfo);
                    productInfo.item_id = null;
                    productInfo.amount = container.min_quantity;
                    productInfo.imgs = [];
                    productInfo.variants = [];
                    return productInfo;
                };
                var createVariant = function (container) {
                    let variant = {};
                            console.log("Variant", container);
                    //        console.log("Variant", container.id)
                    variant.id = container.id;
                    variant.description = container.description;
                    variant.type = container.type;
                    if (container.attributes.length > 0) {
                        variant.attributes = JSON.parse(container.attributes);
                        if (variant.attributes.buyers) {
                            variant.unitPrice = variant.price / variant.attributes.buyers;
                        } else {
                            variant.unitPrice = variant.price;
                        }
                    } else {
                        variant.attributes = "";
                    }
                    if (container.is_on_sale) {
                        variant.exprice = container.price;
                        variant.price = container.sale;
                    } else {
                        variant.price = container.price;
                    }
                    variant.is_on_sale = container.is_on_sale;
                    variant.min_quantity = container.min_quantity;
                    return variant;
                }
                var getCategory = function (variant, arrayCategories) {
                    for (let i in arrayCategories) {
                        if (arrayCategories[i].id == variant['category_id']) {
                            return arrayCategories[i];
                        }
                    }
                    let activeCategory = {
                        "name": variant['category_name'],
                        "id": variant['category_id'],
                        "description": variant['category_description'],
                        "products": [],
                        "more": false
                    }
                    arrayCategories.push(activeCategory);
                    return activeCategory
                }
                var getProduct = function (variant, arrayCategories, merchant) {
                    for (let i in arrayCategories) {
                        for (let j in arrayCategories[i].products) {
                            if (arrayCategories[i].products[j].id == variant['product_id']) {
                                return arrayCategories[i].products[j];
                            }
                        }
                    }
                    let productInfo = buildProduct(variant, merchant);
                    return productInfo;
                }

                var buildProductInformation = function (items) {
                    if (items['products_variants'].length > 0) {
                        let resultsCategory = [];
                        let processedVariants = [];
                        for (let i = 0; i < items['products_variants'].length; i++) {
                            if (processedVariants.includes(items['products_variants'][i].id)) {
                                continue;
                            } else {
                                processedVariants.push(items['products_variants'][i].id);
                            }

                            let category = getCategory(items['products_variants'][i], resultsCategory);
                            console.log("Category found", category);
                            let product = getProduct(items['products_variants'][i], resultsCategory, items['merchant_products'][0]);
                            console.log("product found", product);
                            if (!containsObject(product, category.products)) {
                                category.products.push(product);
                            }
                            let variant = createVariant(items['products_variants'][i]);
                            if (variant.price < product.price) {
                                product = updateProductVisual(variant, product);
                            }
                            if (!containsObject(variant, product.variants)) {
                                product.variants.push(variant);
                            }
                        }
                        for (let j in items['products_files']) {
                            for (let i = 0; i < resultsCategory.length; i++) {
                                for (let k in resultsCategory[i].products) {
                                    let imgInfo = {};
                                    if (items['products_files'][j].trigger_id == resultsCategory[i].products[k].id) {
                                        imgInfo.file = items['products_files'][j].file;
                                        if(resultsCategory[i].products[k].imgs.length == 0){
                                            resultsCategory[i].products[k].src = imgInfo.file;
                                        }
                                        resultsCategory[i].products[k].imgs.push(imgInfo);
                                        break;
                                    }
                                }

                            }
                        }
                        console.log('resultbuildCat', resultsCategory);
                        return resultsCategory;
                    }
                    return null;
                }
                var containsObject = function (obj, list) {
                    var x;
                    for (x in list) {
                        if (list[x].id == obj.id) {
                            return true;
                        }
                    }
                    return false;
                }

                var updateProductVisual = function (container, productInfo) {
                    if (container.is_on_sale) {
                        productInfo.price = container.price;
                        productInfo.onsale = true;
                        productInfo.exprice = container.exprice;
                    } else {
                        productInfo.price = container.price;
                        productInfo.onsale = false;
                    }

                    productInfo.variant_id = container.id;
                    if (container.attributes) {
                        let attributes = container.attributes;
                        if (attributes.buyers) {
                            productInfo.unitPrice = productInfo.price / attributes.buyers;
                            productInfo.unitLunches = attributes.buyers;
                        } else {
                            productInfo.unitPrice = productInfo.price;
                            productInfo.unitLunches = 1;
                        }

                    } else {
                        productInfo.unitPrice = productInfo.price;
                        productInfo.unitLunches = 1;
                    }
                    //        console.log("Update Prod Vis3", productInfo);
                    return productInfo;
                }

                return {
                    addRating:addRating,
                    deleteFavorite:deleteFavorite,
                    checkFavorite:checkFavorite,
                    addFavorite:addFavorite,
                    searchProducts:searchProducts,
                    getProductsMerchant: getProductsMerchant,
                    buildProductInformation: buildProductInformation
                };
            }])