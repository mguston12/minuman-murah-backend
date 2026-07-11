<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResendVerificationEmailController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\CategoryBlogController;
use App\Http\Controllers\CategoryProductController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\TaxoTypeController;
use App\Http\Controllers\TaxoListController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\ProductImageUploadController;
use App\Http\Controllers\ProductPriceController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\AttributeController;
use App\Http\Controllers\AttributeValueController;
use App\Http\Controllers\Auth\CmsForgotPasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\BrandProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MainBannerController;
use App\Http\Controllers\MidtransWebhookController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PointController;
use App\Http\Controllers\XenditWebhookController;
use App\Http\Controllers\PopupBannerController;
use App\Http\Controllers\ShippingAddressController;
use App\Http\Controllers\ShippingController;
use App\Http\Controllers\VoucherController;
use App\Http\Controllers\ProductGroupController;
use App\Http\Controllers\ProductReviewController;
use App\Http\Controllers\ProductSubGroupController;
use App\Http\Controllers\Public\PublicMainBannerController;
use App\Http\Controllers\Public\PublicProductGroupController;
use App\Http\Controllers\Public\PublicProductSubGroupController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/verify-email/{token}', [\App\Http\Controllers\Auth\VerifyEmailController::class, 'verify']);
    Route::post('/resend-verification-email', [ResendVerificationEmailController::class, 'resend']);
    Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
    Route::post('/reset-password', [ResetPasswordController::class, 'reset']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::get('/me', [LoginController::class, 'me']);
    });
});

Route::prefix('cms/auth')->group(function () {
    Route::post('/forgot-password', [CmsForgotPasswordController::class, 'sendResetLink']);
});
// Public blog routes (no auth required)
Route::prefix('blogs')->group(function () {
    Route::get('/', [BlogController::class, 'index']);
    Route::get('/active', [BlogController::class, 'getActive']);
    Route::get('/hot-news', [BlogController::class, 'getHotNews']);
    Route::get('/filter', [BlogController::class, 'filter']);
});

// Public category blogs routes (no auth required)
Route::prefix('category-blogs')->group(function () {
    Route::get('/', [CategoryBlogController::class, 'index']);
    Route::get('/active', [CategoryBlogController::class, 'getActive']);
    Route::get('/slug/{id}', [CategoryBlogController::class, 'showBySlug']);
});

// Public config routes (no auth required for public configs like logo, store name, etc)
Route::prefix('public-configs')->group(function () {
    Route::get('/{key}', [ConfigController::class, 'showPublic']);
});

// Public product routes (no auth required)
Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{slug}', [ProductController::class, 'show']);
    Route::get('/{slug}/related', [ProductController::class, 'related']);
});

// Public shipping routes (RajaOngkir API - no auth required)
Route::prefix('shipping')->group(function () {
    Route::get('/provinces', [ShippingController::class, 'getProvinces']);
    Route::get('/cities', [ShippingController::class, 'getCities']);
    Route::get('/districts', [ShippingController::class, 'getDistricts']);
    Route::get('/sub-districts', [ShippingController::class, 'getSubDistricts']);
    Route::post('/cost', [ShippingController::class, 'getCost']);
});

Route::prefix('vouchers')->group(function () {
    Route::get('/', [VoucherController::class, 'index']);
    Route::get('/{id}', [VoucherController::class, 'show'])->whereNumber('id');
});
Route::post('/vouchers/applicable', [VoucherController::class, 'applicable']);
Route::post('/vouchers/validate', [VoucherController::class, 'validateVoucher']);

// Public brand routes (no auth required for active brands)
Route::prefix('brands')->group(function () {
    Route::get('/active', [BrandController::class, 'getActive']);
});

// Public store routes (no auth required)
Route::prefix('public/stores')->group(function () {
    Route::get('/', [StoreController::class, 'getPublicActiveStores']);
});

// Public taxonomy lists routes (no auth required for categories/collections)
Route::prefix('taxo-lists')->group(function () {
    Route::get('/type/{type}', [TaxoListController::class, 'getByType']);
    Route::get('/parent/{parentId}', [TaxoListController::class, 'getByParent']);
    Route::get('/roots', [TaxoListController::class, 'getRoots']);
});

Route::group(['prefix' => '/public'], function () {
    // Public product groups routes (no auth required - display on home)
    Route::prefix('product-groups')->group(function () {
        Route::get('/', [PublicProductGroupController::class, 'index']);
        Route::get('/{id}', [PublicProductGroupController::class, 'show']);
        Route::get('/key/{key}', [PublicProductGroupController::class, 'showByKey']);

        Route::prefix('/{groupId}/sub-groups')->group(function () {
            Route::get('/', [PublicProductSubGroupController::class, 'index']);
            Route::get('/{subGroupId}', [PublicProductSubGroupController::class, 'show']);
        });
    });
});

// Public product reviews routes no auth required
Route::prefix('products')->group(function () {
    Route::get('/{productId}/reviews', [ProductReviewController::class, 'index']);
});

Route::prefix('public/main-banners')->group(function () {
    Route::get('/', [PublicMainBannerController::class, 'index']);
});

Route::prefix('popup-banners')->group(function () {
    Route::get('/random', [PopupBannerController::class, 'getRandomActive']);
});

// Checkout routes (can be public or authenticated)
Route::prefix('checkout')->group(function () {
    Route::post('/create', [CheckoutController::class, 'create']);
});

// Payment routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', [CheckoutController::class, 'create']);
    Route::post('/payments/groups', [PaymentController::class, 'createMidtransSnapForOrders']);
    Route::post('/orders/{order}/pay/midtrans', [PaymentController::class, 'createMidtransSnap']);
    Route::post('/orders/{order}/pay/xendit', [PaymentController::class, 'createXenditInvoice']);
});
Route::post('/midtrans/webhook', [MidtransWebhookController::class, 'handle']);
Route::post('/xendit/webhook', [XenditWebhookController::class, 'handle']);
Route::get('/payment/midtrans/config', function () {
    $settings = config('settings');

    return response()->json([
        'client_key' => $settings['midtrans_client_key'] ?? null,
        'is_production' => filter_var($settings['midtrans_is_production'] ?? false, FILTER_VALIDATE_BOOLEAN),
    ]);
});

// Dashboard routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/dashboard/summary', [DashboardController::class, 'summary']);
});

// Order routes (authenticated only)
Route::middleware('auth:sanctum')
    ->prefix('orders')
    ->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::get('/order-number/{orderNumber}', [OrderController::class, 'showByOrderNumber']);
        Route::put('/{id}/payment-status', [OrderController::class, 'updatePaymentStatus']);
        Route::put('/{id}/status', [OrderController::class, 'updateStatus'])->middleware('permission:orders.update');
        Route::post('/{id}/cancel', [OrderController::class, 'cancel']);
        Route::post('/{id}/complete', [OrderController::class, 'complete']);
        Route::post('/{id}/confirm-payment', [OrderController::class, 'confirmPayment']);
    });

// Point routes (authenticated only)
Route::middleware('auth:sanctum')
    ->prefix('points')
    ->group(function () {
        Route::get('/', [PointController::class, 'index']);
        Route::get('/transactions', [PointController::class, 'transactions']);
    });

Route::middleware('auth:sanctum')->group(function () {
    // Profile routes (users can manage their own profile)
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'updateProfile']);
        Route::post('/update-password', [ProfileController::class, 'updatePassword']);
        Route::post('/reset-password/{userId}', [ProfileController::class, 'resetPassword'])->middleware('permission:users.update');
    });

    // Shipping Address routes
    Route::prefix('shipping-addresses')->group(function () {
        Route::get('/', [ShippingAddressController::class, 'index']);
        Route::post('/', [ShippingAddressController::class, 'store']);
        Route::get('/{id}', [ShippingAddressController::class, 'show']);
        Route::put('/{id}', [ShippingAddressController::class, 'update']);
        Route::delete('/{id}', [ShippingAddressController::class, 'destroy']);
    });

    // Category Products routes with permissions
    Route::prefix('category-products')->group(function () {
        Route::get('/', [CategoryProductController::class, 'index'])->middleware('permission:products.read');
        Route::get('/product/{productId}', [CategoryProductController::class, 'getByProduct'])->middleware('permission:products.read');
        Route::post('/', [CategoryProductController::class, 'store'])->middleware('permission:products.create');
        Route::post('/attach', [CategoryProductController::class, 'attachCategories'])->middleware('permission:products.update');
        Route::post('/detach', [CategoryProductController::class, 'detachCategories'])->middleware('permission:products.update');
        Route::get('/{id}', [CategoryProductController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [CategoryProductController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [CategoryProductController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Brand Products routes with permissions
    Route::prefix('brand-products')->group(function () {
        Route::get('/', [BrandProductController::class, 'index'])->middleware('permission:products.read');
        Route::get('/product/{productId}', [BrandProductController::class, 'getByProduct'])->middleware('permission:products.read');
        Route::post('/', [BrandProductController::class, 'store'])->middleware('permission:products.create');
        Route::post('/attach', [BrandProductController::class, 'attachBrands'])->middleware('permission:products.update');
        Route::post('/detach', [BrandProductController::class, 'detachBrands'])->middleware('permission:products.update');
        Route::get('/{id}', [BrandProductController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [BrandProductController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [BrandProductController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Taxonomy Types CRUD routes with permissions
    Route::prefix('taxo-types')->group(function () {
        Route::get('/', [TaxoTypeController::class, 'index'])->middleware('permission:products.read');
        Route::post('/', [TaxoTypeController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [TaxoTypeController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [TaxoTypeController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [TaxoTypeController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Taxonomy Lists CRUD routes with permissions (admin only)
    Route::prefix('taxo-lists')->group(function () {
        Route::get('/', [TaxoListController::class, 'index'])->middleware('permission:products.read');
        Route::post('/', [TaxoListController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [TaxoListController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [TaxoListController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [TaxoListController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Products CRUD routes with permissions (admin only)
    Route::prefix('products')->group(function () {
        Route::post('/', [ProductController::class, 'store'])->middleware('permission:products.create');
        Route::put('/{id}', [ProductController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Vouchers CRUD routes with permissions (admin only)
    Route::prefix('vouchers')->group(function () {
        Route::get('/all', [VoucherController::class, 'all'])->middleware('permission:vouchers.read');
        Route::get('/admin/{id}', [VoucherController::class, 'adminShow'])->middleware('permission:vouchers.read');
        Route::post('/', [VoucherController::class, 'store'])->middleware('permission:vouchers.create');
        Route::put('/{id}', [VoucherController::class, 'update'])->middleware('permission:vouchers.update');
        Route::delete('/{id}', [VoucherController::class, 'destroy'])->middleware('permission:vouchers.delete');
    });
    Route::get('/categories/top', [VoucherController::class, 'topCategories']);

    // Attributes routes with permissions
    Route::prefix('attributes')->group(function () {
        Route::get('/', [AttributeController::class, 'index'])->middleware('permission:products.read');
        Route::get('/active', [AttributeController::class, 'getActive'])->middleware('permission:products.read');
        Route::post('/', [AttributeController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [AttributeController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [AttributeController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [AttributeController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Attribute Values routes with permissions
    Route::prefix('attribute-values')->group(function () {
        Route::get('/', [AttributeValueController::class, 'index'])->middleware('permission:products.read');
        Route::get('/attribute/{attributeId}', [AttributeValueController::class, 'getByAttribute'])->middleware('permission:products.read');
        Route::post('/', [AttributeValueController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [AttributeValueController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [AttributeValueController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [AttributeValueController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Product Attributes routes with permissions
    Route::prefix('product-attributes')->group(function () {
        Route::get('/product/{productId}', [\App\Http\Controllers\ProductAttributeController::class, 'getByProduct'])->middleware('permission:products.read');
        Route::post('/product/{productId}/attach', [\App\Http\Controllers\ProductAttributeController::class, 'attachAttributes'])->middleware('permission:products.update');
        Route::delete('/product/{productId}/attribute/{attributeId}', [\App\Http\Controllers\ProductAttributeController::class, 'detachAttribute'])->middleware('permission:products.update');
    });

    // Product Variants routes with permissions
    Route::prefix('product-variants')->group(function () {
        Route::get('/', [ProductVariantController::class, 'index'])->middleware('permission:products.read');
        Route::get('/product/{productId}', [ProductVariantController::class, 'getByProduct'])->middleware('permission:products.read');
        Route::get('/product/{productId}/active', [ProductVariantController::class, 'getActiveByProduct'])->middleware('permission:products.read');
        Route::post('/', [ProductVariantController::class, 'store'])->middleware('permission:products.create');
        Route::post('/upload-image', [ProductVariantController::class, 'uploadVariantImage'])->middleware('permission:products.create');
        Route::get('/{id}', [ProductVariantController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [ProductVariantController::class, 'update'])->middleware('permission:products.update');
        Route::post('/{id}/update-stock', [ProductVariantController::class, 'updateStock'])->middleware('permission:products.update');
        Route::delete('/{id}', [ProductVariantController::class, 'destroy'])->middleware('permission:products.delete');

        // Store Stock Management routes
        Route::get('/{id}/store-stocks', [ProductVariantController::class, 'getStoreStocks'])->middleware('permission:products.read');
        Route::post('/{id}/store-stocks', [ProductVariantController::class, 'createOrUpdateStoreStock'])->middleware('permission:products.update');
        Route::delete('/{id}/store-stocks/{storeId}', [ProductVariantController::class, 'deleteStoreStock'])->middleware('permission:products.update');
    });

    // Product Images routes with permissions
    Route::prefix('product-images')->group(function () {
        Route::get('/', [ProductImageController::class, 'index'])->middleware('permission:products.read');
        Route::get('/product/{productId}', [ProductImageController::class, 'getByProduct'])->middleware('permission:products.read');
        Route::post('/', [ProductImageController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [ProductImageController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [ProductImageController::class, 'update'])->middleware('permission:products.update');
        Route::post('/{id}/set-featured', [ProductImageController::class, 'setFeatured'])->middleware('permission:products.update');
        Route::delete('/{id}', [ProductImageController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Product Image Upload routes (file upload) with permissions
    Route::prefix('product-images-upload')->group(function () {
        Route::post('/single', [ProductImageUploadController::class, 'uploadImage'])->middleware('permission:products.create');
        Route::post('/multiple', [ProductImageUploadController::class, 'uploadMultipleImages'])->middleware('permission:products.create');
        Route::delete('/{imageId}', [ProductImageUploadController::class, 'deleteImage'])->middleware('permission:products.delete');
    });

    // Product Prices routes with permissions
    Route::prefix('product-prices')->group(function () {
        Route::get('/', [ProductPriceController::class, 'index'])->middleware('permission:products.read');
        Route::get('/product/{productId}', [ProductPriceController::class, 'getByProduct'])->middleware('permission:products.read');
        Route::post('/', [ProductPriceController::class, 'store'])->middleware('permission:products.create');
        Route::get('/{id}', [ProductPriceController::class, 'show'])->middleware('permission:products.read');
        Route::put('/{id}', [ProductPriceController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [ProductPriceController::class, 'destroy'])->middleware('permission:products.delete');
    });

    // Clients CRUD routes with permissions
    Route::prefix('clients')->group(function () {
        Route::get('/', [ClientController::class, 'index'])->middleware('permission:clients.read');
        Route::get('/all', [ClientController::class, 'all'])->middleware('permission:clients.read');
        Route::post('/', [ClientController::class, 'store'])->middleware('permission:clients.create');
        Route::get('/{id}', [ClientController::class, 'show'])->middleware('permission:clients.read');
        Route::put('/{id}', [ClientController::class, 'update'])->middleware('permission:clients.update');
        Route::delete('/{id}', [ClientController::class, 'destroy'])->middleware('permission:clients.delete');
    });

    // Stores CRUD routes with permissions
    Route::prefix('stores')->group(function () {
        Route::get('/', [StoreController::class, 'index'])->middleware('permission:stores.read');
        Route::get('/all', [StoreController::class, 'all'])->middleware('permission:stores.read');
        Route::post('/', [StoreController::class, 'store'])->middleware('permission:stores.create');
        Route::get('/{id}', [StoreController::class, 'show'])->middleware('permission:stores.read');
        Route::put('/{id}', [StoreController::class, 'update'])->middleware('permission:stores.update');
        Route::delete('/{id}', [StoreController::class, 'destroy'])->middleware('permission:stores.delete');
    });

    // Brands CRUD routes with permissions
    Route::prefix('brands')->group(function () {
        Route::get('/', [BrandController::class, 'index'])->middleware('permission:brands.read');
        Route::get('/all', [BrandController::class, 'all'])->middleware('permission:brands.read');
        Route::post('/', [BrandController::class, 'store'])->middleware('permission:brands.create');
        Route::get('/{id}', [BrandController::class, 'show'])->middleware('permission:brands.read');
        Route::post('/{id}', [BrandController::class, 'update'])->middleware('permission:brands.update');
        Route::put('/{id}', [BrandController::class, 'update'])->middleware('permission:brands.update');
        Route::delete('/{id}', [BrandController::class, 'destroy'])->middleware('permission:brands.delete');
    });

    // Users CRUD routes with permissions
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index'])->middleware('permission:users.read');
        Route::get('/all', [UserController::class, 'all'])->middleware('permission:users.read');
        Route::post('/', [UserController::class, 'store'])->middleware('permission:users.create');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('permission:users.read');
        Route::put('/{id}', [UserController::class, 'update'])->middleware('permission:users.update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('permission:users.delete');
    });

    // User Management routes (roles & permissions) with permissions
    Route::prefix('user-management')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->middleware('permission:users.read');
        Route::post('/', [UserManagementController::class, 'store'])->middleware('permission:users.create');
        Route::get('/roles/all', [UserManagementController::class, 'getAllRoles'])->middleware('permission:roles.read');

        Route::post('/{id}/assign-role', [UserManagementController::class, 'assignRole'])->middleware('permission:users.update');
        Route::post('/{id}/remove-role', [UserManagementController::class, 'removeRole'])->middleware('permission:users.update');
        Route::post('/{id}/sync-roles', [UserManagementController::class, 'syncRoles'])->middleware('permission:users.update');
        Route::post('/{id}/verify-email', [UserManagementController::class, 'verifyEmail'])->middleware('permission:users.update');
        Route::get('/{id}/permissions', [UserManagementController::class, 'getUserPermissions'])->middleware('permission:users.read');

        Route::get('/{id}', [UserManagementController::class, 'show'])->middleware('permission:users.read');
        Route::put('/{id}', [UserManagementController::class, 'update'])->middleware('permission:users.update');
        Route::delete('/{id}', [UserManagementController::class, 'destroy'])->middleware('permission:users.delete');
    });

    // Blogs CRUD routes with permissions
    Route::prefix('blogs')->group(function () {
        Route::get('/all', [BlogController::class, 'all'])->middleware('permission:blogs.read');
        Route::get('/category/{categoryId}', [BlogController::class, 'getByCategory'])->middleware('permission:blogs.read');
        Route::get('/slug/{slug}', [BlogController::class, 'showBySlug'])->middleware('permission:blogs.read');
        Route::post('/', [BlogController::class, 'store'])->middleware('permission:blogs.create');
        Route::get('/{id}', [BlogController::class, 'show'])->middleware('permission:blogs.read');
        // POST for file uploads (multipart/form-data), PUT for JSON updates
        Route::match(['put', 'post'], '/{id}', [BlogController::class, 'update'])->middleware('permission:blogs.update');
        Route::delete('/{id}', [BlogController::class, 'destroy'])->middleware('permission:blogs.delete');
    });

    // Category Blogs CRUD routes with permissions
    Route::prefix('category-blogs')->group(function () {
        Route::post('/', [CategoryBlogController::class, 'store'])->middleware('permission:blogs.create');
        Route::get('/{id}', [CategoryBlogController::class, 'show'])->middleware('permission:blogs.read');
        Route::put('/{id}', [CategoryBlogController::class, 'update'])->middleware('permission:blogs.update');
        Route::delete('/{id}', [CategoryBlogController::class, 'destroy'])->middleware('permission:blogs.delete');
    });

    // Roles management routes with permissions
    Route::prefix('roles')->group(function () {
        Route::get('/', [RoleController::class, 'index'])->middleware('permission:roles.read');
        Route::get('/search', [RoleController::class, 'search'])->middleware('permission:roles.read');
        Route::post('/', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::get('/{id}', [RoleController::class, 'show'])->middleware('permission:roles.read');
        Route::get('/{id}/permissions', [RoleController::class, 'getPermissions'])->middleware('permission:roles.read');
        Route::put('/{id}', [RoleController::class, 'update'])->middleware('permission:roles.update');
        Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware('permission:roles.delete');
    });

    // Permissions management routes with permissions
    Route::prefix('permissions')->group(function () {
        Route::get('/', [PermissionController::class, 'index'])->middleware('permission:roles.read');
        Route::get('/all', [PermissionController::class, 'all'])->middleware('permission:roles.read');
        Route::get('/search', [PermissionController::class, 'search'])->middleware('permission:roles.read');
        Route::get('/grouped', [PermissionController::class, 'groupedByModule'])->middleware('permission:roles.read');
        Route::get('/module/{module}', [PermissionController::class, 'byModule'])->middleware('permission:roles.read');
        Route::post('/', [PermissionController::class, 'store'])->middleware('permission:roles.create');
        Route::get('/{id}', [PermissionController::class, 'show'])->middleware('permission:roles.read');
        Route::put('/{id}', [PermissionController::class, 'update'])->middleware('permission:roles.update');
        Route::delete('/{id}', [PermissionController::class, 'destroy'])->middleware('permission:roles.delete');
    });

    // Configurations Management Routes (unified config system) with permissions
    Route::prefix('configs')->group(function () {
        Route::get('/', [ConfigController::class, 'index'])->middleware('permission:configs.read');
        Route::post('/', [ConfigController::class, 'store'])->middleware('permission:configs.create');
        Route::get('/{key}', [ConfigController::class, 'show'])->middleware('permission:configs.read');
        // POST for file uploads (multipart/form-data), PUT for JSON updates
        Route::post('/{key}', [ConfigController::class, 'update'])->middleware('permission:configs.update');
        Route::put('/{key}', [ConfigController::class, 'update'])->middleware('permission:configs.update');
        Route::delete('/{key}', [ConfigController::class, 'destroy'])->middleware('permission:configs.delete');
    });

    // Product Groups CRUD routes with permissions
    Route::prefix('product-groups')->group(function () {
        Route::get('/', [ProductGroupController::class, 'index'])->middleware('permission:products.read');
        Route::post('/', [ProductGroupController::class, 'store'])->middleware('permission:products.create');
        Route::put('/{id}', [ProductGroupController::class, 'update'])->middleware('permission:products.update');
        Route::delete('/{id}', [ProductGroupController::class, 'destroy'])->middleware('permission:products.delete');

        Route::get('/', [ProductGroupController::class, 'index']);
        Route::get('/{id}', [ProductGroupController::class, 'show']);
        Route::get('/key/{key}', [ProductGroupController::class, 'showByKey']);

        // Sub-groups nested routes
        Route::prefix('/{groupId}/sub-groups')->group(function () {
            Route::post('/', [ProductSubGroupController::class, 'store'])->middleware('permission:products.create');
            Route::put('/{subGroupId}', [ProductSubGroupController::class, 'update'])->middleware('permission:products.update');
            Route::delete('/{subGroupId}', [ProductSubGroupController::class, 'destroy'])->middleware('permission:products.delete');
            Route::get('/', [ProductSubGroupController::class, 'index']);
            Route::get('/{subGroupId}', [ProductSubGroupController::class, 'show']);

            // Products management in sub-groups
            Route::post('/{subGroupId}/products', [ProductSubGroupController::class, 'addProducts'])->middleware('permission:products.update');
            Route::delete('/{subGroupId}/products/{productId}', [ProductSubGroupController::class, 'removeProduct'])->middleware('permission:products.update');
        });
    });

    Route::prefix('main-banners')->group(function () {
        Route::get('/', [MainBannerController::class, 'index'])->middleware('permission:main-banners.read');
        Route::get('/{id}', [MainBannerController::class, 'show'])->middleware('permission:main-banners.read');
        Route::post('', [MainBannerController::class, 'store'])->middleware('permission:main-banners.create');
        Route::put('/{id}', [MainBannerController::class, 'update'])->middleware('permission:main-banners.update');
        Route::delete('/{id}', [MainBannerController::class, 'destroy'])->middleware('permission:main-banners.delete');
    });

    // Popup Baner CRUD routes with permissions
    Route::prefix('popup-banners')->group(function () {
        Route::get('/', [PopupBannerController::class, 'index'])->middleware('permission:popup-banners.read');
        Route::post('/', [PopupBannerController::class, 'store'])->middleware('permission:popup-banners.create');
        Route::get('/{id}', [PopupBannerController::class, 'show'])->middleware('permission:popup-banners.read');
        Route::put('/{id}', [PopupBannerController::class, 'update'])->middleware('permission:popup-banners.update');
        Route::delete('/{id}', [PopupBannerController::class, 'destroy'])->middleware('permission:popup-banners.delete');
    });

    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/items', [CartController::class, 'store']);
        Route::put('/items/{variantId}', [CartController::class, 'update']);
        Route::delete('/items/{variantId}', [CartController::class, 'destroy']);
        Route::delete('/', [CartController::class, 'clear']);
    });

    // Product Reviews routes with authentication
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/products/{productId}/reviews', [ProductReviewController::class, 'store']);
        Route::get('/user/reviews', [ProductReviewController::class, 'myReviews']);
        Route::put('/reviews/{reviewId}', [ProductReviewController::class, 'update']);
    });
});
