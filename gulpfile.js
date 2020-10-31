                            const elixir = require('laravel-elixir');

require('laravel-elixir-vue-2');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor /resources.
 |
 */

elixir(mix => {
    mix.sass('app.scss')
       .webpack('app.js');
       mix.webpack([
    'app_1.js',
    'constants.js',
    'controllers/userctrl.js',
    'controllers/mapctrl.js',
    'controllers/productctrl.js',
    'controllers/cartctrl.js',
    'controllers/checkoutctrl.js',
    'controllers/accessctrl.js',
    'controllers/sourcesctrl.js',
    'controllers/addressctrl.js',
    'controllers/routesctrl.js',
    'controllers/ordersctrl.js',
    'controllers/exportsctrl.js',
    'controllers/paymentsctrl.js',
    'controllers/groupsctrl.js',
    'controllers/menuctrl.js',
    'controllers/merchantsctrl.js',
    'controllers/deliveriesctrl.js',
    'controllers/admin/store/admin-store-productsctrl.js',
    'controllers/admin/store/admin-store-variantsctrl.js',
    'controllers/admin/store/admin-store-merchantsctrl.js',
    'controllers/admin/store/admin-store-categoriesctrl.js',
    'controllers/foodaddressesctrl.js',
    'controllers/foodmessagesctrl.js',
    'controllers/zonesctrl.js',
    'services/map.js',
    'services/merchants.js',
    'services/categories.js',
    'services/cart.js',
    'services/modals.js',
    'services/mapDash.js',
    'services/users.js',
    'services/location.js',
    'services/products.js',
    'services/checkout.js',
    'services/payu.js',
    'services/billing.js',
    'services/passport.js',
    'services/groups.js',
    'services/address.js',
    'services/routes.js',
    'services/payments.js',
    'services/orders.js',
    'services/product-import.js',
    'services/food.js',
    'services/zones.js',
    
    ], 'public/js/');
    mix.sass(["scss/main.scss",
        "scss/_fonts.scss",
        "scss/_scss-helpers.scss",
        "scss/helpers/_variables.scss",
        "scss/helpers/_media-query.scss",
        "scss/helpers/_mixins.scss",
        "scss/_reset.scss",
        "scss/_common.scss",
        "scss/common/_color.scss",
        "scss/common/_spacings.scss",
        "scss/common/_sliders.scss",
        "scss/common/_tabs.scss",
        "scss/common/_typography.scss",
        "scss/common/_buttons.scss",
        "scss/common/_modal.scss",
        "scss/common/_images.scss",
        "scss/common/_forms.scss",
        "scss/_sections.scss",
        "scss/sections/_header.scss",
        "scss/sections/_menu.scss",
        "scss/sections/_hero-area.scss",
        "scss/sections/_bredcrumb.scss",
        "scss/sections/_policy-section.scss",
        "scss/sections/_category.scss",
        "scss/sections/_comment.scss",
        "scss/sections/_product.scss",
        "scss/sections/_sidebars.scss",
        "scss/sections/_footer.scss",
        "scss/_pages.scss",
        "scss/pages/_shop.scss",
        "scss/pages/_product-details.scss",
        "scss/pages/_checkout.scss",
        "scss/pages/_compare.scss",
        "scss/pages/_contact.scss",
        "scss/pages/_blog.scss",
        "scss/pages/_cart.scss",
        "scss/pages/_my-account.scss",
        "scss/pages/_login-register.scss",
        "scss/pages/_about.scss",
        "scss/pages/_faq.scss",
        "scss/pages/_404.scss",
        "scss/pages/_portfolio.scss",
        "scss/pages/_service.scss",
        "scss/_theme-color-2.scss",
        "scss/_theme-color-3.scss",
        "scss/_theme-color-4.scss"], 'public/css/main.css');
});
