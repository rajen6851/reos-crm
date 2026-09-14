# 📘 Saniso E-commerce & Botble CMS Master Developer Handbook

> **Documentation Scope**: Full Technical Deep-Dive into Saniso E-Commerce Codebase, Botble CMS Architecture, Database Schemas, Core Engines (Cart, Checkout, Products, Orders, Quotes), Themes & Shortcodes Catalog, and Complete 7-Layer Execution Flows for ALL Pages.

---

## 🔄 7-Layer Execution Flows (Every Page: Start -> Route -> Controller -> Model -> Migration/Table -> Helper -> End View)

---

### 1. Home Page Flow 🏠
1. **Start View / Event**: User visits domain root `/` (e.g. `https://saniso.com/`).
2. **Route File**: `platform/packages/page/routes/web.php` or `platform/packages/theme/routes/public.php` (`GET /`).
3. **Controller & Method**: `Botble\Page\Http\Controllers\PublicController@getPage`.
4. **Model(s)**: `Botble\Page\Models\Page`, `Botble\Theme\Models\ThemeOptions`.
5. **DB Migration / Table**: `pages` table (`platform/packages/page/database/migrations/2016_09_02_065301_create_pages_table.php`).
6. **Helper & Facades**: `BaseHelper::getHomepageUrl()`, `Theme::partial()`, `Shortcode::compile()`.
7. **End View**: Layout [`platform/themes/saniso/layouts/default.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/layouts/default.blade.php) rendering shortcode partials ([`sliders.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/sliders.blade.php), [`featured-products.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/ecommerce/featured-products.blade.php), [`flash-sale.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/ecommerce/flash-sale.blade.php)).

---

### 2. Shop & Product Listing Flow 🛍️
1. **Start View / Event**: User clicks "Shop" in menu or visits `/products`.
2. **Route File**: [`platform/plugins/ecommerce/routes/product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/product.php) (`GET /products`).
3. **Controller & Method**: [`PublicProductController@getProducts`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicProductController.php#L63).
4. **Model(s)**: [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php), [`ProductCategory.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ProductCategory.php), [`Brand.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Brand.php).
5. **DB Migration / Table**: `ec_products`, `ec_product_categories`, `ec_brands` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)).
6. **Helper & Facades**: `get_products()` in [`helpers/products.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/helpers/products.php), `format_price()`, `EcommerceHelper::viewPath('products')`.
7. **End View**: [`products.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/products.blade.php) using [`product-item.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/ecommerce/product-item.blade.php).

---

### 3. Single Product Detail Page Flow 📄
1. **Start View / Event**: User clicks product card link (`/products/{slug}`).
2. **Route File**: [`platform/plugins/ecommerce/routes/product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/product.php).
3. **Controller & Method**: [`PublicProductController@getProduct`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicProductController.php#L125).
4. **Model(s)**: [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php), [`ProductVariation.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ProductVariation.php), [`Review.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Review.php), [`GlobalOption.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/GlobalOption.php).
5. **DB Migration / Table**: `ec_products`, `ec_product_variations`, `ec_reviews`, `ec_options` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)).
6. **Helper & Facades**: `render_product_swatches()`, `render_product_options()`, `RvMedia::getImageUrl()`, `get_cart_cross_sale_products()`.
7. **End View**: [`product.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/product.blade.php) using [`product-cart-form.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/ecommerce/product-cart-form.blade.php).

---

### 4. Cart Flow 🛒
1. **Start View / Event**: User clicks "Add to Cart" button in [`product-cart-form.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/ecommerce/product-cart-form.blade.php).
2. **Route File**: [`platform/plugins/ecommerce/routes/cart.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/cart.php) (`POST /cart/add-to-cart`).
3. **Controller & Method**: [`PublicCartController@store`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCartController.php#L82) & [`PublicCartController@index`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCartController.php#L49).
4. **Model(s)**: [`Cart.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Cart.php), [`AbandonedCart.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/AbandonedCart.php).
5. **DB Migration / Table**: `ec_cart` ([`2020_03_05_041139_create_ecommerce_tables.php#L263`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php#L263)) & `ec_abandoned_carts` ([`2025_07_15_090809_create_ec_abandoned_carts_table.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2025_07_15_090809_create_ec_abandoned_carts_table.php)).
6. **Helper & Facades**: `Cart::instance('cart')`, `OrderHelper::handleAddCart()`, `HandleApplyPromotionsService`, `HandleApplyCouponService`.
7. **End View**: [`cart.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/cart.blade.php).

---

### 5. Checkout & Order Placement Flow 💳
1. **Start View / Event**: User clicks "Proceed to Checkout" on [`cart.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/cart.blade.php).
2. **Route File**: [`platform/plugins/ecommerce/routes/checkout.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/checkout.php) (`GET /checkout/{token}` & `POST /checkout/process`).
3. **Controller & Method**: [`PublicCheckoutController@getInformation`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCheckoutController.php) & [`PublicCheckoutController@postCheckout`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCheckoutController.php).
4. **Model(s)**: [`Order.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Order.php), [`OrderProduct.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/OrderProduct.php), [`OrderAddress.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/OrderAddress.php), [`Invoice.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Invoice.php), [`Shipment.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Shipment.php).
5. **DB Migration / Table**: `ec_orders`, `ec_order_product`, `ec_order_addresses` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)) & `ec_invoices` ([`2022_10_12_041517_create_invoices_table.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2022_10_12_041517_create_invoices_table.php)).
6. **Helper & Facades**: `OrderHelper::createOrder()`, `InvoiceHelper::makeInvoice()`, `HandleShippingFeeService`, `HandlePaymentService`.
7. **End View**: Order Success Page (`checkout/success.blade.php`).

---

### 6. Customer Account & Dashboard Flow 👤
1. **Start View / Event**: User clicks "My Account" or visits `/customer/overview`.
2. **Route File**: [`platform/plugins/ecommerce/routes/customer.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/customer.php).
3. **Controller & Method**: `Botble\Ecommerce\Http\Controllers\Customers\PublicController@getOverview`.
4. **Model(s)**: [`Customer.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Customer.php), [`Order.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Order.php), [`Address.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Address.php).
5. **DB Migration / Table**: `ec_customers`, `ec_customer_addresses` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)).
6. **Helper & Facades**: `auth('customer')->user()`, `get_customer_avatar()`.
7. **End View**: `platform/themes/saniso/views/ecommerce/customers/overview.blade.php`.

---

### 7. Order Tracking Page Flow 🚚
1. **Start View / Event**: User submits Order Code & Email on `/orders/tracking`.
2. **Route File**: [`platform/plugins/ecommerce/routes/customer.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/customer.php).
3. **Controller & Method**: [`PublicEcommerceController@getTrackOrder`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicEcommerceController.php).
4. **Model(s)**: [`Order.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Order.php), [`Shipment.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Shipment.php), [`ShipmentHistory.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ShipmentHistory.php).
5. **DB Migration / Table**: `ec_orders`, `ec_shipments`, `ec_shipment_histories`.
6. **Helper & Facades**: `OrderHelper::getOrderSessionToken()`.
7. **End View**: [`order-tracking.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/order-tracking.blade.php).

---

### 8. Wishlist & Compare Flow ❤️
1. **Start View / Event**: User clicks Heart / Compare Icon on product card.
2. **Route File**: [`routes/wishlist.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/wishlist.php) & [`routes/compare.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/compare.php).
3. **Controller & Method**: [`WishlistController@store`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/WishlistController.php) & [`CompareController@store`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/CompareController.php).
4. **Model(s)**: [`Wishlist.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Wishlist.php), [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php).
5. **DB Migration / Table**: `ec_wishlists` ([`2024_05_07_073153_improve_table_wishlist.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2024_05_07_073153_improve_table_wishlist.php)).
6. **Helper & Facades**: `Theme::partial('ecommerce.product-loop-buttons')`.
7. **End View**: [`wishlist.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/wishlist.blade.php) & [`compare.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/compare.blade.php).

---

### 9. Header Live Autocomplete Search Flow 🔍
1. **Start View / Event**: User types search string into header search bar.
2. **Route File**: [`routes/base.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/base.php) (`GET /ajax/search-products`).
3. **Controller & Method**: [`PublicAjaxController@getSearchProducts`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicAjaxController.php).
4. **Model(s)**: [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php), [`ProductCategory.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ProductCategory.php).
5. **DB Migration / Table**: `ec_products`, `ec_product_categories`.
6. **Helper & Facades**: `get_products()`, `format_price()`, `RvMedia::getImageUrl()`.
7. **End View**: Ajax JSON Dropdown HTML & [`search.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/search.blade.php).

---

### 10. B2B Quote Request / Project List Flow 📋
1. **Start View / Event**: Product price is `$0.00` $\rightarrow$ User clicks "Request Quote" or "Add to Project List" button.
2. **Route File**: `platform/plugins/quote-request/routes/web.php`.
3. **Controller & Method**: `Botble\QuoteRequest\Http\Controllers\PublicController@store`.
4. **Model(s)**: `Botble\QuoteRequest\Models\ProjectList`, `Botble\QuoteRequest\Models\ProjectListItem`.
5. **DB Migration / Table**: `quote_requests`, `project_lists`.
6. **Helper & Facades**: `add_shortcode('project-request-form')` in [`functions/shortcodes.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/functions/shortcodes.php).
7. **End View**: [`project-request-form.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/project-request-form.blade.php).

---

## 🏛️ Directory Structure & Module Responsibilities

```
c:\xampp\htdocs\ecom-saniso\
├── app/                        # Standard Laravel Application Directory
├── platform/                   # BOTBLE CORE ARCHITECTURE ROOT
│   ├── packages/               # Core Packages (Theme, Shortcode, Media, SEO, Revision)
│   ├── plugins/                # Modular Business Extensions
│   │   ├── ecommerce/          # CORE E-COMMERCE ENGINE (Products, Cart, Checkout, Orders)
│   │   ├── marketplace/        # Multi-vendor Marketplace plugin
│   │   ├── quote-request/      # B2B Project List & Quote Request plugin
│   │   └── simple-slider/      # Hero Banner Slider Manager
│   └── themes/                 # FRONTEND VISUAL LAYER
│       └── saniso/             # Active Site Theme (Views, Partials, Shortcodes, Assets)
```


# 📘 Saniso E-commerce & Botble CMS Master Developer Handbook

> **Documentation Scope**: Full Technical Deep-Dive into Saniso E-Commerce Codebase, Botble CMS Architecture, Database Schemas, Core Engines (Cart, Checkout, Products, Orders, Quotes), Themes & Shortcodes Catalog, and Complete 7-Layer Execution Flows for ALL Pages.

---

## 🔄 7-Layer Execution Flows (Every Page: Start -> Route -> Controller -> Model -> Migration/Table -> Helper -> End View)

---

### 1. Home Page Flow 🏠
1. **Start View / Event**: User visits domain root `/` (e.g. `https://saniso.com/`).
2. **Route File**: `platform/packages/page/routes/web.php` or `platform/packages/theme/routes/public.php` (`GET /`).
3. **Controller & Method**: `Botble\Page\Http\Controllers\PublicController@getPage`.
4. **Model(s)**: `Botble\Page\Models\Page`, `Botble\Theme\Models\ThemeOptions`.
5. **DB Migration / Table**: `pages` table (`platform/packages/page/database/migrations/2016_09_02_065301_create_pages_table.php`).
6. **Helper & Facades**: `BaseHelper::getHomepageUrl()`, `Theme::partial()`, `Shortcode::compile()`.
7. **End View**: Layout [`platform/themes/saniso/layouts/default.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/layouts/default.blade.php) rendering shortcode partials ([`sliders.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/sliders.blade.php), [`featured-products.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/ecommerce/featured-products.blade.php), [`flash-sale.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/ecommerce/flash-sale.blade.php)).

---

### 2. Shop & Product Listing Flow 🛍️
1. **Start View / Event**: User clicks "Shop" in menu or visits `/products`.
2. **Route File**: [`platform/plugins/ecommerce/routes/product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/product.php) (`GET /products`).
3. **Controller & Method**: [`PublicProductController@getProducts`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicProductController.php#L63).
4. **Model(s)**: [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php), [`ProductCategory.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ProductCategory.php), [`Brand.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Brand.php).
5. **DB Migration / Table**: `ec_products`, `ec_product_categories`, `ec_brands` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)).
6. **Helper & Facades**: `get_products()` in [`helpers/products.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/helpers/products.php), `format_price()`, `EcommerceHelper::viewPath('products')`.
7. **End View**: [`products.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/products.blade.php) using [`product-item.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/ecommerce/product-item.blade.php).

---

### 3. Single Product Detail Page Flow 📄
1. **Start View / Event**: User clicks product card link (`/products/{slug}`).
2. **Route File**: [`platform/plugins/ecommerce/routes/product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/product.php).
3. **Controller & Method**: [`PublicProductController@getProduct`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicProductController.php#L125).
4. **Model(s)**: [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php), [`ProductVariation.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ProductVariation.php), [`Review.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Review.php), [`GlobalOption.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/GlobalOption.php).
5. **DB Migration / Table**: `ec_products`, `ec_product_variations`, `ec_reviews`, `ec_options` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)).
6. **Helper & Facades**: `render_product_swatches()`, `render_product_options()`, `RvMedia::getImageUrl()`, `get_cart_cross_sale_products()`.
7. **End View**: [`product.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/product.blade.php) using [`product-cart-form.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/ecommerce/product-cart-form.blade.php).

---

### 4. Cart Flow 🛒
1. **Start View / Event**: User clicks "Add to Cart" button in [`product-cart-form.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/ecommerce/product-cart-form.blade.php).
2. **Route File**: [`platform/plugins/ecommerce/routes/cart.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/cart.php) (`POST /cart/add-to-cart`).
3. **Controller & Method**: [`PublicCartController@store`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCartController.php#L82) & [`PublicCartController@index`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCartController.php#L49).
4. **Model(s)**: [`Cart.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Cart.php), [`AbandonedCart.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/AbandonedCart.php).
5. **DB Migration / Table**: `ec_cart` ([`2020_03_05_041139_create_ecommerce_tables.php#L263`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php#L263)) & `ec_abandoned_carts` ([`2025_07_15_090809_create_ec_abandoned_carts_table.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2025_07_15_090809_create_ec_abandoned_carts_table.php)).
6. **Helper & Facades**: `Cart::instance('cart')`, `OrderHelper::handleAddCart()`, `HandleApplyPromotionsService`, `HandleApplyCouponService`.
7. **End View**: [`cart.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/cart.blade.php).

---

### 5. Checkout & Order Placement Flow 💳
1. **Start View / Event**: User clicks "Proceed to Checkout" on [`cart.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/cart.blade.php).
2. **Route File**: [`platform/plugins/ecommerce/routes/checkout.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/checkout.php) (`GET /checkout/{token}` & `POST /checkout/process`).
3. **Controller & Method**: [`PublicCheckoutController@getInformation`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCheckoutController.php) & [`PublicCheckoutController@postCheckout`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicCheckoutController.php).
4. **Model(s)**: [`Order.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Order.php), [`OrderProduct.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/OrderProduct.php), [`OrderAddress.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/OrderAddress.php), [`Invoice.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Invoice.php), [`Shipment.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Shipment.php).
5. **DB Migration / Table**: `ec_orders`, `ec_order_product`, `ec_order_addresses` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)) & `ec_invoices` ([`2022_10_12_041517_create_invoices_table.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2022_10_12_041517_create_invoices_table.php)).
6. **Helper & Facades**: `OrderHelper::createOrder()`, `InvoiceHelper::makeInvoice()`, `HandleShippingFeeService`, `HandlePaymentService`.
7. **End View**: Order Success Page (`checkout/success.blade.php`).

---

### 6. Customer Account & Dashboard Flow 👤
1. **Start View / Event**: User clicks "My Account" or visits `/customer/overview`.
2. **Route File**: [`platform/plugins/ecommerce/routes/customer.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/customer.php).
3. **Controller & Method**: `Botble\Ecommerce\Http\Controllers\Customers\PublicController@getOverview`.
4. **Model(s)**: [`Customer.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Customer.php), [`Order.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Order.php), [`Address.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Address.php).
5. **DB Migration / Table**: `ec_customers`, `ec_customer_addresses` ([`2020_03_05_041139_create_ecommerce_tables.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2020_03_05_041139_create_ecommerce_tables.php)).
6. **Helper & Facades**: `auth('customer')->user()`, `get_customer_avatar()`.
7. **End View**: `platform/themes/saniso/views/ecommerce/customers/overview.blade.php`.

---

### 7. Order Tracking Page Flow 🚚
1. **Start View / Event**: User submits Order Code & Email on `/orders/tracking`.
2. **Route File**: [`platform/plugins/ecommerce/routes/customer.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/customer.php).
3. **Controller & Method**: [`PublicEcommerceController@getTrackOrder`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicEcommerceController.php).
4. **Model(s)**: [`Order.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Order.php), [`Shipment.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Shipment.php), [`ShipmentHistory.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ShipmentHistory.php).
5. **DB Migration / Table**: `ec_orders`, `ec_shipments`, `ec_shipment_histories`.
6. **Helper & Facades**: `OrderHelper::getOrderSessionToken()`.
7. **End View**: [`order-tracking.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/order-tracking.blade.php).

---

### 8. Wishlist & Compare Flow ❤️
1. **Start View / Event**: User clicks Heart / Compare Icon on product card.
2. **Route File**: [`routes/wishlist.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/wishlist.php) & [`routes/compare.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/compare.php).
3. **Controller & Method**: [`WishlistController@store`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/WishlistController.php) & [`CompareController@store`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/CompareController.php).
4. **Model(s)**: [`Wishlist.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Wishlist.php), [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php).
5. **DB Migration / Table**: `ec_wishlists` ([`2024_05_07_073153_improve_table_wishlist.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/database/migrations/2024_05_07_073153_improve_table_wishlist.php)).
6. **Helper & Facades**: `Theme::partial('ecommerce.product-loop-buttons')`.
7. **End View**: [`wishlist.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/wishlist.blade.php) & [`compare.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/compare.blade.php).

---

### 9. Header Live Autocomplete Search Flow 🔍
1. **Start View / Event**: User types search string into header search bar.
2. **Route File**: [`routes/base.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/routes/base.php) (`GET /ajax/search-products`).
3. **Controller & Method**: [`PublicAjaxController@getSearchProducts`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Http/Controllers/Fronts/PublicAjaxController.php).
4. **Model(s)**: [`Product.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/Product.php), [`ProductCategory.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/plugins/ecommerce/src/Models/ProductCategory.php).
5. **DB Migration / Table**: `ec_products`, `ec_product_categories`.
6. **Helper & Facades**: `get_products()`, `format_price()`, `RvMedia::getImageUrl()`.
7. **End View**: Ajax JSON Dropdown HTML & [`search.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/views/ecommerce/search.blade.php).

---

### 10. B2B Quote Request / Project List Flow 📋
1. **Start View / Event**: Product price is `$0.00` $\rightarrow$ User clicks "Request Quote" or "Add to Project List" button.
2. **Route File**: `platform/plugins/quote-request/routes/web.php`.
3. **Controller & Method**: `Botble\QuoteRequest\Http\Controllers\PublicController@store`.
4. **Model(s)**: `Botble\QuoteRequest\Models\ProjectList`, `Botble\QuoteRequest\Models\ProjectListItem`.
5. **DB Migration / Table**: `quote_requests`, `project_lists`.
6. **Helper & Facades**: `add_shortcode('project-request-form')` in [`functions/shortcodes.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/functions/shortcodes.php).
7. **End View**: [`project-request-form.blade.php`](file:///c:/xampp/htdocs/ecom-saniso/platform/themes/saniso/partials/shortcodes/project-request-form.blade.php).

---

## 🏛️ Directory Structure & Module Responsibilities

```
c:\xampp\htdocs\ecom-saniso\
├── app/                        # Standard Laravel Application Directory
├── platform/                   # BOTBLE CORE ARCHITECTURE ROOT
│   ├── packages/               # Core Packages (Theme, Shortcode, Media, SEO, Revision)
│   ├── plugins/                # Modular Business Extensions
│   │   ├── ecommerce/          # CORE E-COMMERCE ENGINE (Products, Cart, Checkout, Orders)
│   │   ├── marketplace/        # Multi-vendor Marketplace plugin
│   │   ├── quote-request/      # B2B Project List & Quote Request plugin
│   │   └── simple-slider/      # Hero Banner Slider Manager
│   └── themes/                 # FRONTEND VISUAL LAYER
│       └── saniso/             # Active Site Theme (Views, Partials, Shortcodes, Assets)
```
