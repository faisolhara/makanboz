<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});

Route::get('/base', function (Request $request) {
	return base64_encode("1b9564b4-6206-48df-ad99-434c5bcdb1f8:091e7b3b-ffe3-42b7-be70-db2db6e1dcfd");
});

Route::post('/photo_base', function (Request $request) {
	// var_dump($request->file('p_photo'));
	var_dump(base64_encode(file_get_contents($_FILES['p_photo']['tmp_name'])));
	exit();
	return base64_encode($request->file('p_photo'));
});

Route::get('/test', function () {
	dd(\DB::table('otp_request')->get());
	return view('welcome');
});

Route::post('/zzz_get_user', 'ZzzGetUserController@index');

//BERANDA
Route::post('/cart_chat_badge', 'CartChatBadgeController@index');
Route::post('/notification_badge', 'NotificationBadgeController@index');
Route::post('/promo_list', 'PromoListController@index');
Route::post('/product_popular', 'ProductPopularController@index');
Route::post('/product_recommendation', 'ProductRecomendationController@index');
Route::post('/user_notification_click', 'UserNotificationClickController@index');

//DAFTAR PRODUK & PENJUAL
Route::post('/search_shop_list', 'SearchShopListController@index');
Route::post('/search_product_list', 'SearchProductListController@index');

//KERANJANGKU
Route::post('/shopping_cart_list', 'ShoppingCartListController@index');
Route::post('/shopping_cart_del', 'ShoppingCartDelController@index');
Route::post('/date_validation', 'DateValidationController@index');

//CHAT LIST
Route::post('/buyer_chat_list', 'BuyerChatListController@index');
Route::post('/seller_chat_list', 'BuyerChatListController@index');
Route::post('/chat_delete', 'ChatDeleteController@index');
Route::post('/chat_read', 'ChatReadController@index');

//CHAT DETAIL
Route::post('/chat_detail', 'ChatDetailController@index');
Route::post('/chat_send', 'ChatSendController@index');

//CHECKOUT
Route::post('/location_validation', 'LocationValidationController@index');
Route::post('/default_shop_delivery', 'DefaultShopDeliveryController@index');	
Route::post('/transaction_order', 'TransactionOrderController@index'); //ini belum

//DAFTAR BLOKIR
Route::post('/block_list', 'BlockListController@index'); 
Route::post('/block_user', 'BlockUserController@index'); 

//DAFTAR MENGIKUTI
Route::post('/following_list', 'FollowingListController@index'); 
Route::post('/user_follow', 'UserFollowController@index'); 

//DAFTAR PENGIKUT
Route::post('/follower_list', 'FollowerListController@index');

//DAFTAR PRODUK
Route::post('/search_product', 'SearchPorductController@index');
Route::post('/product_filter_location', 'ProductFilterLocationController@index');
Route::post('/product_filter_category', 'ProductFilterCategoryController@index');
Route::post('/product_filter_review', 'ProductFilterReviewController@index');

//RINCIAN TRANSAKSI
Route::post('/seller_transaction_header', 'SellerTransactionHeaderController@index');
Route::post('/seller_transaction_line', 'SellerTransactionLineController@index');
Route::post('/transaction_status', 'TransactionStatusController@index');
Route::post('/seller_confirmation', 'SellerConfirmationController@index');
Route::post('/seller_return', 'SellerReturnController@index');

//RINCIAN TRANSAKSI
Route::post('/buyer_transaction_header', 'BuyerTransactionHeaderController@index');
Route::post('/buyer_transaction_line', 'BuyerTransactionLineController@index');
Route::post('/buyer_finish', 'BuyerFinishController@index');

//DETAIL PRODUK
Route::post('/product_photo', 'ProductPhotoController@index');
Route::post('/user_favorite_product_status', 'UserFavoriteProductStatusController@index');
Route::post('/product_detail', 'ProductDetailController@index');
Route::post('/user_follow_shop_status', 'ProductFollowShopStatusController@index');
Route::post('/review_product', 'ReviewProductController@index');
Route::post('/review_photo', 'ReviewPhotoController@index');
Route::post('/seller_product', 'SellerProductController@index');
Route::post('/similar_product', 'SimilarProductController@index');
Route::post('/shopping_cart_add', 'ShoppingCartAddController@index');
Route::post('/user_favorite_product', 'ProductFavoriteController@index');

//EMAIL
Route::post('/update_user_data', 'UpdateUserDataController@index');

//FAVORIT SAYA
Route::post('/my_favorite', 'MyFavoriteController@index');

//GANTI PASSWORD
Route::post('/change_password', 'ChangePasswordController@index');

//GRATIS PENGIRIMAN (DEFAULT PENJUAL)
Route::post('/user_data', 'UserDataController@index');

//JASA KIRIM (DEFAULT PENJUAL)
Route::post('/user_delivery_list', 'UserDeliveryListController@index');
Route::post('/user_delivery_save', 'UserDeliverySaveController@index');

//JENIS KELAMIN

//KATEGORI
Route::post('/category_list', 'CategoryListController@index');

//KECAMATAN
Route::post('/district_list', 'DistrictListController@index');

//KETERSEDIAAN
Route::post('/product_available', 'ProductAvailableController@index');

//KODE VERIFIKASI
Route::post('/otp_validation', 'OtpValidationController@index');
Route::post('/otp_request', 'OtpRequestController@index');

//KOTA
Route::post('/city_list', 'CityListController@index');

//LAPORAN PRODUK
Route::post('/product_report_list', 'ProductReportListController@index');
Route::post('/product_report_save', 'ProductReportSaveController@index');

//LOGIN
Route::post('/user_login', 'UserLoginController@index');

//LUPA PASSWORD
Route::post('/forgot_password', 'ForgotPasswordController@index');

//METODE PEMBAYARAN
Route::post('/payment_method', 'PaymentMethodController@index');

//NAMA USAHA

//NOTIFIKASI BOZPAY
Route::post('/notification_bozpay_list', 'NotificationBozpayListController@index');
Route::post('/notification_read', 'NotificationReadController@index');

//NOTIFIKASI PEMBELI
Route::post('/notification_buyer_list', 'NotificationBuyerListController@index');
Route::post('/notification_transaction_image', 'NotificationTransactionImageController@index');

//NOTIFIKASI PENJUAL
Route::post('/notification_seller_list', 'NotificationSellerListController@index');

//NOTIFIKASI UPDATE MAKANBOZ
Route::post('/news_list', 'NewsListController@index');

//NOTIFIKASI
Route::post('/notification_detail_badge', 'NotificationDetailBadgeController@index');
Route::post('/notification_click', 'NotificationClickController@index');
Route::post('/notification_type_click', 'NotificationTypeClickController@index');

//PENARIKAN
Route::post('/bozpay_withdraw_request', 'BozpayWithdrawRequestController@index');
Route::post('/bozpay_point_saldo', 'BozpayPointSaldoController@index');

//PENGATURAN PRIVASI
Route::post('/user_setting', 'UserSettingController@index');
Route::post('/update_user_setting', 'UpdateUserSettingController@index');

//PENGHASILAN SAYA
Route::post('/my_revenue', 'MyRevenueController@index');
Route::post('/my_revenue_list', 'MyRevenueListController@index');

//PENILAIAN SAYA
Route::post('/my_review_avg', 'MyReviewAvgController@index');
Route::post('/my_review_count_all', 'MyReviewCountAllController@index');
Route::post('/my_review_count_note', 'MyReviewCountNoteController@index');
Route::post('/my_review_count_photo', 'MyReviewCountPhotoController@index');
Route::post('/my_review_count_star', 'MyReviewCountStarController@index');
Route::post('/my_review_list', 'MyReviewListController@index');

//PENILAIAN (PRODUK)
Route::post('/product_review_count_all', 'ProductReviewCountAllController@index');
Route::post('/product_review_count_note', 'ProductReviewCountNoteController@index');
Route::post('/product_review_count_photo', 'ProductReviewCountPhotoController@index');
Route::post('/product_review_count_star', 'ProductReviewCountStarController@index');
Route::post('/product_review_list', 'ProductReviewListController@index');

//PENJUALAN SAYA
Route::post('/my_sales_unpaid_list', 'MySalesUnpaidListController@index');
Route::post('/my_sales_confirmation_list', 'MySalesConfirmationListController@index');
Route::post('/my_sales_ready_list', 'MySalesReadyListController@index');
Route::post('/seller_finish_order', 'SellerFinishOrderController@index');
Route::post('/my_sales_finished_list', 'MySalesFinishedController@index');

//PRODUK BARU
Route::post('/product_data', 'ProductDataController@index');
Route::post('/product_save', 'ProductSaveController@index');//belum
Route::post('/product_del', 'ProductDelController@index');

//PROFIL SAYA
Route::post('/my_profile', 'MyProfileController@index');

//PROVINSI
Route::post('/province_list', 'ProvinceListController@index');

//REGISTER
Route::post('/new_register', 'NewRegisterController@index');

//REKENING BARU
Route::post('/user_bank_account_save', 'UserBankAccountSaveController@index');

//REKENING
Route::post('/user_bank_account_list', 'UserBankAccountListController@index');
Route::post('/user_bank_account_del', 'UserBankAccountDelController@index');


//SAYA BELI & JUAL
Route::post('/shop_main', 'ShopMainController@index');
Route::post('/transaction_status_badge', 'TransactionStatusBadgeController@index');

//LIHAT SAYA
Route::post('/shop_product', 'ShopProductController@index');
Route::post('/shop_category', 'ShopCategoryController@index');
Route::post('/change_phone_number', 'ChangePhoneNumberController@index');

//TERAKHIR DILIHAT
Route::post('/my_last_seen', 'MyLastSeenController@index');
Route::post('/my_timeline', 'MyTimeLineController@index');

//TRANSAKSI BOZPAY
Route::post('/bozpay_history', 'BozpayHistoryController@index');

//ALAMAT BARU
Route::post('/postal_code_list', 'PostalCodeListController@index');
Route::post('/user_address_save', 'UserAddressSaveController@index');

//ALAMAT SAYA
Route::post('/user_address_list', 'UserAddressListController@index');
Route::post('/user_address_del', 'UserAddressDelController@index');

//BANK
Route::post('/bank_list', 'BankListController@index');

//BELANJAAN SAYA
Route::post('/my_purchase_unpaid_list', 'MyPurchaseUnpaidListController@index');
Route::post('/my_purchase_confirmation_list', 'MyPurchaseConfirmationListController@index');
Route::post('/my_purchase_ready_list', 'MyPurchaseReadyListController@index');
Route::post('/buyer_finish_order', 'BuyerFinishOrderController@index');
Route::post('/my_purchase_finished_list', 'MyPurchaseFinishedListController@index');

//RINCIAN PENARIKAN
Route::post('/withdraw_detail', 'WithdrawDetailController@index');

//UBAH PIN
Route::post('/change_pin', 'ChangePinController@index');

//SCHEDULE / TIMER
Route::post('/withdraw_transfer', 'WithdrawTransferController@index');
Route::post('/transaction_paid', 'TransactionPaidController@index');

//NILAI PRODUK
Route::post('/buyer_review', 'BuyerReviewController@index');
Route::post('/buyer_review_photo', 'BuyerReviewPhotoController@index');
Route::post('/review_suggestion_list', 'ReviewSuggestionListController@index');

//MENU ADMINISTRATOR
Route::post('/update_calendar', 'UpdateCalendarController@index');
Route::post('/generate_calendar', 'GenerateCalendarController@index');
Route::post('/news_del', 'NewsDelController@index');
Route::post('/news_save', 'NewsSaveController@index');
Route::post('/change_pin_bozpay', 'ChangePinBozpayController@index');
Route::post('/product_publish', 'ProductPublishController@index');


//=============================================================================================


Route::post('/send-email', 'SendEmailController@index');
Route::post('/get-sphinx', 'GetSphinxController@index');



Route::post('/product_photo_save', 'ProductPhotoSaveController@index');
Route::post('/product-delivery-save', 'ProductDeliverySaveController@index');
Route::post('/product-price-save', 'ProductPriceSaveController@index');


Route::post('/transaction-header-save', 'TransactionHeaderSaveController@index');
Route::post('/transaction-line-save', 'TransactionLineSaveController@index');
Route::post('/transaction-delivery-save', 'TransactionDeliverySaveController@index');
Route::post('/unpaid-notification', 'UnpaidNotificationController@index');

Route::post('/buyer-confirmation', 'BuyerConfirmationController@index');
Route::post('/review-photo-save', 'ReviewPhotoSaveController@index');




Route::post('/user_favorite_save', 'ProductReportController@index');





