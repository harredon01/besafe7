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
    mix.sass('safire/style.scss', 'public/css/main3.css')
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
    'controllers/bookingctrl.js',
    'controllers/sourcesctrl.js',
    'controllers/sitemapctrl.js',
    'controllers/addressctrl.js',
    'controllers/routesctrl.js',
    'controllers/ordersctrl.js',
    'controllers/exportsctrl.js',
    'controllers/paymentsctrl.js',
    'controllers/lonchisctrl.js',
    'controllers/groupsctrl.js',
    'controllers/menuctrl.js',
    'controllers/merchantsctrl.js',
    'controllers/merchantsctrl.js',
    'controllers/reportsctrl.js',
    'controllers/leadsctrl.js',
    'controllers/deliveriesctrl.js',
    'controllers/admin/store/admin-store-productsctrl.js',
    'controllers/admin/store/admin-store-variantsctrl.js',
    'controllers/admin/store/admin-store-merchantsctrl.js',
    'controllers/admin/store/admin-store-categoriesctrl.js',
    'controllers/foodaddressesctrl.js',
    'controllers/foodmessagesctrl.js',
    'controllers/zonesctrl.js',
    'controllers/zonespubctrl.js',
    'services/map.js',
    'services/booking.js',
    'services/merchants.js',
    'services/categories.js',
    'services/cart.js',
    'services/modals.js',
    'services/mapDash.js',
    'services/users.js',
    'services/location.js',
    'services/products.js',
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
    'services/leads.js',
    
    ], 'public/js/');
    mix.sass("scss/main.scss", 'public/css/main.css');
    mix.sass(["lonchis/main.scss",
        "lonchis/_fonts.scss",
        "lonchis/_scss-helpers.scss",
        "lonchis/helpers/_variables.scss",
        "lonchis/helpers/_media-query.scss",
        "lonchis/helpers/_mixins.scss",
        "lonchis/_reset.scss",
        "lonchis/_common.scss",
        "lonchis/common/_color.scss",
        "lonchis/common/_spacings.scss",
        "lonchis/common/_sliders.scss",
        "lonchis/common/_tabs.scss",
        "lonchis/common/_typography.scss",
        "lonchis/common/_buttons.scss",
        "lonchis/common/_modal.scss",
        "lonchis/common/_images.scss",
        "lonchis/common/_forms.scss",
        "lonchis/_sections.scss",
        "lonchis/sections/_header.scss",
        "lonchis/sections/_menu.scss",
        "lonchis/sections/_hero-area.scss",
        "lonchis/sections/_bredcrumb.scss",
        "lonchis/sections/_policy-section.scss",
        "lonchis/sections/_category.scss",
        "lonchis/sections/_comment.scss",
        "lonchis/sections/_product.scss",
        "lonchis/sections/_sidebars.scss",
        "lonchis/sections/_footer.scss",
        "lonchis/_pages.scss",
        "lonchis/pages/_shop.scss",
        "lonchis/pages/_product-details.scss",
        "lonchis/pages/_checkout.scss",
        "lonchis/pages/_compare.scss",
        "lonchis/pages/_contact.scss",
        "lonchis/pages/_blog.scss",
        "lonchis/pages/_cart.scss",
        "lonchis/pages/_my-account.scss",
        "lonchis/pages/_login-register.scss",
        "lonchis/pages/_about.scss",
        "lonchis/pages/_faq.scss",
        "lonchis/pages/_404.scss",
        "lonchis/pages/_portfolio.scss",
        "lonchis/pages/_service.scss",
        "lonchis/pages/_booking.scss",
        "lonchis/_theme-color-2.scss",
        "lonchis/_theme-color-3.scss",
        "lonchis/_theme-color-4.scss"], 'public/css/main2.css');
});
