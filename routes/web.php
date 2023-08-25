<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
	return Redirect::to('/admin/dashboard');
});

Route::get('admin/login', 'Auth\LoginController@index');
Route::post('admin/logincheck', 'Auth\LoginController@authenticate');

Route::group(['prefix' => '/admin', 'middleware' => 'auth'], function () {

	Route::get('/', function () {
		return Redirect::to('admin/dashboard');
	});


	Route::get('/logout', 'Admin\DashboardController@getLogout');
	Route::get('/dashboard', 'Admin\DashboardController@index');
	Route::get('/analytics', 'Admin\DashboardController@siteAnalytics');

	//	Category Routes
	Route::get('/categories', 'Admin\CategoryController@index')->name('category.index')->middleware('permission:' . config('perm.listCategory'));
	Route::get('/category/create', 'Admin\CategoryController@create')->middleware('permission:' . config('perm.addCategory'));
	Route::get('/category/edit/{id}', 'Admin\CategoryController@edit')->name('category.edit')->middleware('permission:' . config('perm.editCategory'));
	Route::get('/category/delete/{id}', 'Admin\CategoryController@delete')->name('category.destroy')->middleware('permission:' . config('perm.deleteCategory'));
	Route::post('/category/savecategory', 'Admin\CategoryController@save')->middleware('permission:' . config('perm.addCategory') . '|' . config('perm.editCategory'));
	Route::post('/getParentCategory', 'Admin\CategoryController@getParentCategory');

	//      Sub-Category Routes
	Route::get('/category/subcategories/{parentId}', 'Admin\CategoryController@subCategoriesListing')->name('get.child.category')->middleware('permission:' . config('perm.listSubCategory'));
	Route::get('/category/subcategory/{parentId}/addsubcategory', 'Admin\CategoryController@addSubcategory')->middleware('permission:' . config('perm.addSubCategory'));
	Route::post('/category/subcategory/{parentId}/savesubcategory', 'Admin\CategoryController@saveSubcategory')->middleware('permission:' . config('perm.addSubCategory') . '|' . config('perm.editSubCategory'));
	Route::get('/category/subcategory/{parentId}/editsubcategory/{editId}', 'Admin\CategoryController@editSubcategory')->middleware('permission:' . config('perm.editSubCategory'));
	Route::get('/category/subcategory/{parentId}/deletesubcategory/{editId}', 'Admin\CategoryController@deleteSubcategory')->middleware('permission:' . config('perm.deleteSubCategory'));

	//      Email Templates Routes
	Route::get('/templates', 'Admin\EmailTemplatesController@index')->middleware('permission:' . config('perm.listEmailTemplate'));
	Route::get('/addtemplate', 'Admin\EmailTemplatesController@add')->middleware('permission:' . config('perm.addEmailTemplate'));
	Route::get('/edittemplate/{id}', 'Admin\EmailTemplatesController@edit')->middleware('permission:' . config('perm.editEmailTemplate'));
	Route::get('/deletetemplate/{id}', 'Admin\EmailTemplatesController@delete')->middleware('permission:' . config('perm.deleteEmailTemplate'));
	Route::post('/savetemplate', 'Admin\EmailTemplatesController@save')->middleware('permission:' . config('perm.addEmailTemplate') . '|' . config('perm.editEmailTemplate'));


	//	Subscription Routes
	Route::get('/subscriptions', 'Admin\SubscriptionsController@index')->middleware('permission:' . config('perm.listMembershipPlan'));
	Route::get('/addsubscription', 'Admin\SubscriptionsController@add');
	Route::post('/savesubscription', 'Admin\SubscriptionsController@save');
	Route::get('/editsubscription/{id}', 'Admin\SubscriptionsController@edit');
	Route::get('/deletesubscription/{id}', 'Admin\SubscriptionsController@delete');

	//	Users Routes
	Route::any('/users', 'Admin\UserController@index')->middleware('permission:' . config('perm.listUser'));
	Route::get('/adduser', 'Admin\UserController@add')->middleware('permission:' . config('perm.addUser'));
	Route::get('/addagent', 'Admin\UserController@addAgent');
	Route::post('/saveuser', 'Admin\UserController@save')->middleware('permission:' . config('perm.addUser') . '|' . config('perm.editUser'));
	Route::get('/edituser/{id}', 'Admin\UserController@edit')->middleware('permission:' . config('perm.editUser'));
	Route::get('/editagent/{id}', 'Admin\UserController@editAgent');
	Route::get('/deleteuser/{id}', 'Admin\UserController@delete')->middleware('permission:' . config('perm.deleteUser'));
	Route::get('/activeuser/{id}', 'Admin\UserController@active')->middleware('permission:' . config('perm.activateUser'));
	Route::get('/activate/{id}', 'Admin\UserController@setUserActive');
	Route::get('/harddeleteuser/{id}', 'Admin\UserController@hardDelete')->middleware('permission:' . config('perm.hardDeleteUser'));
	Route::post('auto-complete-users', 'Admin\UserController@autoCompleteUser')->name('autoCompleteUser');


	//	User's business Routes
	Route::any('/allentity', 'Admin\BusinessController@getAllBusinesses')->middleware('permission:' . config('perm.listEntity'));
	Route::get('/allentity/{id}', 'Admin\BusinessController@getAllBusinessesByCategory')->name('getAllBusinessesByCategory')->middleware('permission:' . config('perm.listEntity'));
	Route::get('/premiumbusiness', 'Admin\BusinessController@getPremiumBusinesses')->name('entity.premium');
	Route::get('/user/business/{id}', 'Admin\BusinessController@index');
	Route::get('/user/business/add/{id}', 'Admin\BusinessController@add');
	Route::post('/user/business/save', 'Admin\BusinessController@save')->middleware('permission:' . config('perm.addEntity') . '|' . config('perm.editEntity'));
	Route::get('/user/business/edit/{id}', 'Admin\BusinessController@edit')->name('entity.edit')->middleware('permission:' . config('perm.editEntity'));
	Route::get('/user/business/delete/{id}', 'Admin\BusinessController@delete')->name('entity.destroy')->middleware('permission:' . config('perm.deleteEntity'));
	Route::post('/search/subcategory', 'Admin\BusinessController@getSubCategotyById');
	Route::post('/search/businessmetatags', 'Admin\BusinessController@getBusinessMetaTags');
	Route::post('/search/addCategotyHierarchy', 'Admin\BusinessController@addCategotyHierarchy');
	Route::post('/user/business/approved', 'Admin\BusinessController@setStatusBusinessApproved')->middleware('permission:' . config('perm.approveMember'));
	Route::post('/user/business/rejected', 'Admin\BusinessController@businessRejected')->middleware('permission:' . config('perm.rejectMember'));
	Route::post('/remove/businessimage', 'Admin\BusinessController@removeBusinessImage');
	Route::post('/user/business/savebusinessinfo', 'Admin\BusinessController@saveBusinessInfo');
	Route::post('/user/business/savecontactinfo', 'Admin\BusinessController@saveContactInfo');
	Route::post('/user/business/saveworkinghours', 'Admin\BusinessController@saveWorkingHours');
	Route::post('/user/business/savesocialprofiles', 'Admin\BusinessController@saveSocialProfiles');
	Route::post('/user/business/saveOnlineStores', 'Admin\BusinessController@saveOnlineStores')->name('save.online.stores');
	Route::post('/user/business/saveKnowMore', 'Admin\BusinessController@saveKnowMore')->name('save.know.more');
	Route::post('/user/business/saveVideo', 'Admin\BusinessController@saveEntityVideo')->name('save.entity.video');
	Route::post('/user/business/saveCustomDetail', 'Admin\BusinessController@saveCustomDetail')->name('save.custom.detail');
	Route::post('/user/business/saveNearByFilter', 'Admin\BusinessController@saveNearByFilter')->name('save.nearBy.filter');
	Route::post('/user/business/savePublicWebsite', 'Admin\BusinessController@savePublicWebsite');
	Route::post('/user/business/savesocialactivities', 'Admin\BusinessController@saveSocialActivities');
	Route::post('/user/business/savecategryhierarchy', 'Admin\BusinessController@saveCategryHierarchy');

	// Entity routes
	Route::get('/create/entity', 'Admin\BusinessController@createEntity')->name('entity.create')->middleware('permission:' . config('perm.addEntity'));
	Route::get('/show/entity/{id}', 'Admin\BusinessController@show')->name('entity.show')->middleware('permission:' . config('perm.viewEntity'));
	Route::get('/entity/decription/suggestion', 'Admin\BusinessController@getDescSuggestions')->name('entity.desc.suggestion');
	Route::get('/entity-claim-request', 'Admin\EntityClaimController@index')->name('entity.claim.request')->middleware('permission:' . config('perm.listClaim'));
	Route::post('/entity-claim-request/update/{id}', 'Admin\EntityClaimController@update')->name('entity.claim.update');
	Route::get('/entity/reports', 'Admin\BusinessController@getEntityReports')->name('entity.reports');
	Route::get('/entity/reports/view/{id}', 'Admin\BusinessController@getEntityReport')->name('entity.view.report');

	/** 
	 * @date: 21st Aug, 2018
	 * Bulk approve/reject feature is added. Requested by Mr. Mahipal.
	 */
	Route::post('/user/business/bulk-approved', 'Admin\BusinessController@bulkStatusBusinessApproved')->middleware('permission:' . config('perm.approveMember'));
	Route::post('/user/business/bulk-rejected', 'Admin\BusinessController@bulkStatusBusinessRejected')->middleware('permission:' . config('perm.rejectMember'));

	//      NewsLetters Routes
	Route::get('/newsletter', 'Admin\NewsletterController@index')->middleware('permission:' . config('perm.listNewsletter'));
	Route::get('/newsletter/create', 'Admin\NewsletterController@create')->middleware('permission:' . config('perm.addNewsletter'));
	Route::get('/newsletter/edit/{id}', 'Admin\NewsletterController@edit')->middleware('permission:' . config('perm.editNewsletter'));
	Route::get('/newsletter/delete/{id}', 'Admin\NewsletterController@delete')->middleware('permission:' . config('perm.deleteNewsletter'));
	Route::post('/newsletter/save', 'Admin\NewsletterController@save')->middleware('permission:' . config('perm.addNewsletter') . '|' . config('perm.editNewsletter'));
	Route::get('/newsletter/savesend/{id}', 'Admin\NewsletterController@updateNotifySubscriberStatus')->middleware('permission:' . config('perm.sendNewsletter'));

	//	User's business service Routes
	Route::get('/user/business/service/{id}', 'Admin\ServiceController@index')->middleware('permission:' . config('perm.manageService'));
	Route::get('/user/business/service/add/{id}', 'Admin\ServiceController@add')->middleware('permission:' . config('perm.addService'));
	Route::post('/user/business/service/save', 'Admin\ServiceController@save')->middleware('permission:' . config('perm.editService') . '|' . config('perm.addService'));
	Route::get('/user/business/service/edit/{id}', 'Admin\ServiceController@edit')->middleware('permission:' . config('perm.editService'));
	Route::get('/user/business/service/delete/{id}', 'Admin\ServiceController@delete')->middleware('permission:' . config('perm.deleteService'));
	Route::post('/remove/serviceimage', 'Admin\ServiceController@removeServiceImage');

	//	User's business product Routes
	Route::get('/user/business/product/{id}', 'Admin\ProductController@index')->middleware('permission:' . config('perm.manageProduct'));
	Route::get('/user/business/product/add/{id}', 'Admin\ProductController@add')->middleware('permission:' . config('perm.addProduct'));
	Route::post('/user/business/product/save', 'Admin\ProductController@save')->middleware('permission:' . config('perm.editProduct') . '|' . config('perm.addProduct'));
	Route::get('/user/business/product/edit/{id}', 'Admin\ProductController@edit')->middleware('permission:' . config('perm.editProduct'));
	Route::get('/user/business/product/delete/{id}', 'Admin\ProductController@delete')->middleware('permission:' . config('perm.deleteProduct'));
	Route::post('/remove/productimage', 'Admin\ProductController@removeProductImage');

	//	User's business owner Routes
	Route::get('/user/business/owner/{id}', 'Admin\OwnerController@index')->middleware('permission:' . config('perm.manageOwner'));
	Route::get('/user/business/owner/add/{id}', 'Admin\OwnerController@add')->middleware('permission:' . config('perm.addOwner'));
	Route::post('/user/business/owner/save', 'Admin\OwnerController@save')->middleware('permission:' . config('perm.editOwner') . '|' . config('perm.addOwner'));
	Route::get('/user/business/owner/edit/{id}', 'Admin\OwnerController@edit')->middleware('permission:' . config('perm.editOwner'));
	Route::get('/user/business/owner/delete/{id}', 'Admin\OwnerController@delete')->middleware('permission:' . config('perm.deleteOwner'));

	//	User's business Membership plan Routes
	Route::get('/user/business/membership/{id}', 'Admin\MembershipController@index')->middleware('permission:' . config('perm.manageMembership'));
	Route::get('/user/business/membership/add/{id}', 'Admin\MembershipController@add')->middleware('permission:' . config('perm.addMembership'));
	Route::post('/user/business/membership/save', 'Admin\MembershipController@save')->middleware('permission:' . config('perm.editMembership') . '|' . config('perm.addMembership'));
	Route::get('/user/business/membership/edit/{id}', 'Admin\MembershipController@edit')->middleware('permission:' . config('perm.editMembership'));
	Route::get('/user/business/membership/delete/{id}', 'Admin\MembershipController@delete')->middleware('permission:' . config('perm.deleteMembership'));
	Route::post('/user/business/membership/status', 'Admin\MembershipController@status')->name('membership.status')->middleware('permission:' . config('perm.updateStatusMembership'));


	//      Investment Ideas Routes
	Route::get('/investmentideas', 'Admin\InvestmentController@index');
	Route::get('/investmentideas/add', 'Admin\InvestmentController@add');
	Route::post('/investmentideas/save', 'Admin\InvestmentController@save');
	Route::get('/investmentideas/edit/{id}', 'Admin\InvestmentController@edit');
	Route::get('/investmentideas/delete/{id}', 'Admin\InvestmentController@delete');

	//     	Trending Services Routes
	Route::get('/getAllTrendingServices', 'Admin\CategoryController@getAllTrendingServices')->name('getAllTrendingServices')->middleware('permission:' . config('perm.listTrendingService'));
	Route::post('/updateTrendingService', 'Admin\CategoryController@updateTrendingService')->middleware('permission:' . config('perm.updateStatusTrendingService'));

	//      Trending Category Routes
	Route::get('/getAllTrendingCategory', 'Admin\CategoryController@getAllTrendingCategory')->name('getAllTrendingCategory')->middleware('permission:' . config('perm.listTrending'));
	Route::post('/updateTrendingCategory', 'Admin\CategoryController@updateTrendingCategory')->middleware('permission:' . config('perm.updateStatusTrending'));

	//      Promoted Businesses Routes
	Route::get('/getAllPromotedBusinesses', 'Admin\BusinessController@getAllPromotedBusinesses')->middleware('permission:' . config('perm.listPromotedEntity'));
	Route::post('/updatePromotedBusinesses', 'Admin\BusinessController@updatePromotedBusinesses');

	Route::get('/notifications', 'Admin\DashboardController@notifications');
	Route::post('/notificationsave', 'Admin\DashboardController@notificationsave');
	Route::post('/send/notification', 'Admin\DashboardController@sendNotification');
	Route::get('/send/{type}/notification', 'Admin\DashboardController@sendNotification');
	Route::get('/getPushNotification', 'Admin\DashboardController@getPushNotification');
	Route::post('/sendPushNotification', 'Admin\DashboardController@sendPushNotification');
	Route::get('/notificationdelete/{id}', 'Admin\DashboardController@notificationdelete');

	//	    Agent Routes
	Route::get('agents', 'Admin\AgentController@index')->middleware('permission:' . config('perm.listRepresentativeRequests'));
	Route::get('agentrequest/{id}', 'Admin\AgentController@agentRequest');
	Route::post('rejectagentrequest', 'Admin\AgentController@rejectAgentRequest')->name('rejectAgentRequest');
	Route::get('agents/rejected', 'Admin\AgentController@rejectedIndex')->name('listRejecteRequest');

	//      Country Routes
	Route::get('/country', 'Admin\CountryController@index')->middleware('permission:' . config('perm.listCountry'));
	Route::get('/addcountry', 'Admin\CountryController@add')->middleware('permission:' . config('perm.addCountry'));
	Route::get('/editcountry/{id}', 'Admin\CountryController@edit')->middleware('permission:' . config('perm.editCountry') . '|' . config('perm.addCountry'));
	Route::get('/deletecountry/{id}', 'Admin\CountryController@delete')->middleware('permission:' . config('perm.deleteCountry'));
	Route::post('/savecountry', 'Admin\CountryController@save')->middleware('permission:' . config('perm.editCountry'));

	//      State Routes
	Route::get('/state', 'Admin\StateController@index')->name('state.index')->middleware('permission:' . config('perm.listState'));
	Route::get('/addstate', 'Admin\StateController@add')->middleware('permission:' . config('perm.addState'));
	Route::get('/editstate/{id}', 'Admin\StateController@edit')->name('state.edit')->middleware('permission:' . config('perm.editState'));
	Route::delete('/deletestate/{id}', 'Admin\StateController@delete')->name('state.destroy')->middleware('permission:' . config('perm.deleteState'));
	Route::post('/savestate', 'Admin\StateController@save')->middleware('permission:' . config('perm.editState') . '|' . config('perm.addState'));

	Route::post('/getState', 'Admin\StateController@getState');
	Route::post('/getStateList', 'Admin\StateController@getStateList');
	Route::post('/getCity', 'Admin\CityController@getCity');
	Route::post('/getCityList', 'Admin\CityController@getCityList');

	//      City Routes
	Route::get('/city', 'Admin\CityController@index')->name('city.index')->middleware('permission:' . config('perm.listCity'));
	Route::get('/addcity', 'Admin\CityController@add')->middleware('permission:' . config('perm.addCity'));
	Route::get('/editcity/{id}', 'Admin\CityController@edit')->name('city.edit')->middleware('permission:' . config('perm.editCity'));
	Route::delete('/deletecity/{id}', 'Admin\CityController@delete')->name('city.destroy')->middleware('permission:' . config('perm.deleteCity'));
	Route::post('/savecity', 'Admin\CityController@save')->middleware('permission:' . config('perm.editCity') . '|' . config('perm.addCity'));

	// Membership request Route
	Route::any('/membershiprequest', 'Admin\SubscriptionsController@membershipRequest')->name('membershipRequest')->middleware(['permission:' . config('perm.listMemberReq')]);
	Route::get('/membershipapprove/{id}/{status}', 'Admin\SubscriptionsController@membershipApprove')->name('membership.update.status')->middleware(['permission:' . config('perm.approveMemberReq')]);
	Route::post('/membershipreject', 'Admin\SubscriptionsController@membershipReject')->middleware(['permission:' . config('perm.rejectMemberReq')]);

	// CMS template Routes
	Route::get('/cms', 'Admin\CmsController@index')->middleware('permission:' . config('perm.listCms'));
	Route::get('/addcms', 'Admin\CmsController@add')->middleware('permission:' . config('perm.addCms'));
	Route::get('/editcms/{id}', 'Admin\CmsController@edit')->middleware('permission:' . config('perm.editCms'));
	Route::get('/deletecms/{id}', 'Admin\CmsController@delete')->middleware('permission:' . config('perm.deleteCms'));
	Route::post('/savecms', 'Admin\CmsController@save')->middleware('permission:' . config('perm.editCms') . '|' . config('perm.addCms'));
	Route::get('/searchterm', 'Admin\CmsController@getSearchTerm')->middleware(['permission:' . config('perm.listSearchTerm')]);

	// Branding Routes
	Route::get('/branding', 'Admin\CmsController@brandingImage')->middleware('permission:' . config('perm.listBranding'));
	Route::post('/savebranding', 'Admin\CmsController@savebrandingImage')->middleware('permission:' . config('perm.editBranding'));
	Route::post('/brandingsave', 'Admin\CmsController@brandingsave');
	Route::get('/deletebranding', 'Admin\CmsController@deletebrandingImage')->middleware('permission:' . config('perm.deleteBranding'));

	// OTP routes
	Route::get('/otp', 'Admin\UserController@getOtpList')->name("otp.list")->middleware('permission:' . config('perm.listOTP'));
	Route::get('/editotp/{id}', 'Admin\UserController@editOtp')->middleware('permission:' . config('perm.editOTP'));
	Route::get('/sendotp/{id}/{type}', 'Admin\UserController@sendOtp')->middleware('permission:' . config('perm.sendOTP'));
	Route::post('/saveotp', 'Admin\UserController@saveOtp')->middleware('permission:' . config('perm.editOTP'));
	Route::delete('/delete-otp/{id}', 'Admin\UserController@deleteOtp')->name('otp.destroy')->middleware('permission:' . config('perm.deleteOTP'));

	//Business AutoComplete API for Branding page.	
	Route::post('/auto-complete-business', 'Admin\CmsController@autoCompleteBusiness');

	//Market place (Advertisement) Admin section
	Route::get('/advertisement', 'Admin\AdvertisementController@index')->middleware('permission:' . config('perm.listMarketplaceAds'));
	Route::post('/advertisement', 'Admin\AdvertisementController@index');
	Route::get('/advertisement/edit/{id}', 'Admin\AdvertisementController@edit')->middleware('permission:' . config('perm.editMarketplaceAds'));
	Route::post('/advertisement/approved', 'Admin\AdvertisementController@updateAdvertisingToApproved')->middleware('permission:' . config('perm.approveMarketplaceAds'));
	Route::post('/advertisement/rejected', 'Admin\AdvertisementController@updateAdvertisingToRejected')->middleware('permission:' . config('perm.rejectMarketplaceAds'));
	Route::post('/advertisement/saveAdvertisementInfo', 'Admin\AdvertisementController@saveAdvertisementInfo');
	Route::post('/advertisement/saveContactInfo', 'Admin\AdvertisementController@saveContactInfo');
	Route::post('/advertisement/saveVideoLinks', 'Admin\AdvertisementController@saveVideoLinks');
	Route::post('/advertisement/saveCategory', 'Admin\AdvertisementController@saveCategory');
	Route::get('/advertisement/remove/{id}', 'Admin\AdvertisementController@removeAdvertisement')->middleware('permission:' . config('perm.deleteMarketplaceAds'));
	Route::get('/advertisement/restore/{id}', 'Admin\AdvertisementController@restoreAdvertisement')->middleware('permission:' . config('perm.restoreMarketplaceAds'));

	// Payment Transaction Admin Section ROutes
	Route::get('/transactions', 'Admin\PaymentTransactionsController@index')->middleware('permission:' . config('perm.listPaymentTransaction'));
	Route::post('/transactions', 'Admin\PaymentTransactionsController@index');

	Route::get('/csvimport', 'Admin\CsvImportController@index')->middleware('permission:' . config('perm.csvImport'));
	Route::post('/csvimport', 'Admin\CsvImportController@import')->middleware('permission:' . config('perm.csvImport'));
	// Public Website
	Route::get('/allpublicwebsite', 'Admin\PublicWebsiteController@index')->name('publicwebsite.list')->middleware('permission:' . config('perm.listPublicWebsite'));
	Route::get('/allpublicwebsite/add/{id}', 'Admin\PublicWebsiteController@create')->name('publicwebsite.create')->middleware('permission:' . config('perm.addPublicWebsite'));
	Route::post('/allpublicwebsite/save/{id}', 'Admin\PublicWebsiteController@store')->name('publicwebsite.store');
	Route::get('/allpublicwebsite/edit/{id}', 'Admin\PublicWebsiteController@edit')->name('publicwebsite.edit')->middleware('permission:' . config('perm.editPublicWebsite'));
	Route::post('/allpublicwebsite/update/{id}', 'Admin\PublicWebsiteController@update')->name('publicwebsite.update')->middleware('permission:' . config('perm.editPublicWebsite'));
	Route::get('/allpublicwebsite/remove/{id}', 'Admin\PublicWebsiteController@destroy')->name('publicwebsite.destroy')->middleware('permission:' . config('perm.deletePublicWebsite'));
	Route::post('/allpublicwebsite/status', 'Admin\PublicWebsiteController@status')->name('PublicWebsiteController.status')->middleware('permission:' . config('perm.updateStatusPublicWebsite'));

	Route::get('/all-public-inquiry', 'Admin\PublicWebsiteController@getInquiry')->name('public.inquiry')->middleware('permission:' . config('perm.listPublicWebsiteInquiry'));
	Route::get('/all-public-review', 'Admin\PublicWebsiteController@getReviews')->name('public.review')->middleware('permission:' . config('perm.listPublicWebsiteReview'));

	//Public Website Tetemplets
	Route::get('/allpublicwebsitetetemplets', 'Admin\PublicWebsiTetempletsController@index')->name('PublicWebsiteTetemplets.list')->middleware('permission:' . config('perm.listPublicWebsiteTemplate'));
	Route::get('/allpublicwebsitetetemplets/add', 'Admin\PublicWebsiTetempletsController@create')->name('PublicWebsiteTetemplets.create')->middleware('permission:' . config('perm.addPublicWebsiteTemplate'));
	Route::post('/allpublicwebsitetetemplets/save', 'Admin\PublicWebsiTetempletsController@store')->name('PublicWebsiteTetemplets.store');
	Route::get('/allpublicwebsitetetemplets/edit/{id}', 'Admin\PublicWebsiTetempletsController@edit')->name('PublicWebsiteTetemplets.edit')->middleware('permission:' . config('perm.editPublicWebsiteTemplate'));
	Route::post('/allpublicwebsitetetemplets/update/{id}', 'Admin\PublicWebsiTetempletsController@update')->name('PublicWebsiteTetemplets.update');
	Route::get('/allpublicwebsitetetemplets/remove/{id}', 'Admin\PublicWebsiTetempletsController@destroy')->name('PublicWebsiteTetemplets.destroy')->middleware('permission:' . config('perm.deletePublicWebsiteTemplate'));
	Route::post('/allpublicwebsitetetemplets/status', 'Admin\PublicWebsiTetempletsController@status')->name('PublicWebsiteTetemplets.status')->middleware('permission:' . config('perm.updateStatusPublicWebsiteTemplate'));

	Route::get('/allpublicwebsitetetemplets/theme', 'Admin\PublicWebsiTetempletsController@getTheme')->name('PublicWebsiteTetemplets.getTheme');

	//Public Website Plans
	Route::get('/allpublicwebsiteplans', 'Admin\PublicWebsitePlansController@index')->name('PublicWebsiteplans.list')->middleware('permission:' . config('perm.listPublicWebsitePlan'));
	Route::get('/allpublicwebsiteplans/add', 'Admin\PublicWebsitePlansController@create')->name('PublicWebsiteplans.create')->middleware('permission:' . config('perm.addPublicWebsitePlan'));
	Route::post('/allpublicwebsiteplans/save', 'Admin\PublicWebsitePlansController@store')->name('PublicWebsiteplans.store');
	Route::get('/allpublicwebsiteplans/edit/{id}', 'Admin\PublicWebsitePlansController@edit')->name('PublicWebsiteplans.edit')->middleware('permission:' . config('perm.editPublicWebsitePlan'));
	Route::post('/allpublicwebsiteplans/update/{id}', 'Admin\PublicWebsitePlansController@update')->name('PublicWebsiteplans.update');
	Route::get('/allpublicwebsiteplans/remove/{id}', 'Admin\PublicWebsitePlansController@destroy')->name('PublicWebsiteplans.destroy')->middleware('permission:' . config('perm.deletePublicWebsitePlan'));
	Route::post('/allpublicwebsiteplans/status', 'Admin\PublicWebsitePlansController@status')->name('PublicWebsitePlansController.status')->middleware('permission:' . config('perm.updateStatusPublicWebsitePlan'));

	//Public Website Payments
	Route::get('/allpublicwebsitepayments', 'Admin\PublicWebsitePaymentsController@index')->name('PublicWebsitepayments.list')->middleware('permission:' . config('perm.listPublicWebsitePayments'));
	Route::get('/allpublicwebsitepayments/add', 'Admin\PublicWebsitePaymentsController@create')->name('PublicWebsitepayments.create')->middleware('permission:' . config('perm.addPublicWebsitePayments'));
	Route::post('/allpublicwebsitepayments/save', 'Admin\PublicWebsitePaymentsController@store')->name('PublicWebsitepayments.save');
	Route::get('/allpublicwebsitepayments/edit/{id}', 'Admin\PublicWebsitePaymentsController@edit')->name('PublicWebsitepayments.edit')->middleware('permission:' . config('perm.editPublicWebsitePayments'));
	Route::post('/allpublicwebsitepayments/update/{id}', 'Admin\PublicWebsitePaymentsController@update')->name('PublicWebsitepayments.update');
	Route::get('/allpublicwebsitepayments/remove/{id}', 'Admin\PublicWebsitePaymentsController@destroy')->name('PublicWebsitepayments.destroy')->middleware('permission:' . config('perm.deletePublicWebsitePayments'));
	Route::get('/settings', 'Admin\SettingsController@index')->name('settings.index')->middleware('permission:' . config('perm.listSetting'));
	Route::post('/settings/save', 'Admin\SettingsController@save')->name('settings.save')->middleware('permission:' . config('perm.editSetting'));

	Route::resource('advisor', 'Admin\AdvisorController');
	Route::resource('businessBranding', 'Admin\BusinessBrandingController')->except('show');
	Route::resource('businessBrandingInquiry', 'Admin\BusinessBrandingInquiryController')->except('show');

	Route::resource('/publicPost', 'Admin\PublicPostController');
	Route::resource('/plasmaDonor', 'Admin\PlasmaDonorController');
	Route::resource('/sendMail', 'Admin\SendMailController');
	Route::resource('/onlineStore', 'Admin\OnlineStoreController');
	Route::resource('/notification', 'Admin\NotificationController');
	Route::get('/getUserForSendNotification', 'Admin\NotificationController@getUserForSendNotification')->name('getUserForSendNotification');
	Route::resource('/assetType', 'Admin\AssetTypeController');
	Route::get('/subAssetType/{id}', 'Admin\AssetTypeController@getSubAsset')->name('getSubAssetTypeByAsset');
	Route::get('/create/subAssetType/{parent}', 'Admin\AssetTypeController@create')->name('subAssetType.create');
	Route::get('/report', 'Admin\ReportController@index')->name('report.index');
	Route::get('/report/{id}', 'Admin\ReportController@show')->name('report.show');
	Route::delete('/report/{id}', 'Admin\ReportController@destroy')->name('report.destroy');
	Route::resource('/reason', 'Admin\ReasonController');
	Route::resource('/role', 'Admin\RoleController');
	Route::resource('/permission', 'Admin\PermissionController');
	Route::resource('/site', 'Admin\SiteController');
	Route::post('site-auto-complete', 'Admin\SiteController@autoComplete')->name('siteAutoComplete');
	Route::post('site/{site}', 'Admin\SiteController@approveReject')->name('site.approveReject');
	Route::get('site-members/{site_id}', 'Admin\SiteController@getMembers')->name('site.getMembers');
	Route::post('site-members-delete/{site_id}/{user_id}', 'Admin\SiteController@deleteMembers')->name('site.deleteMembers');
	Route::resource('/location', 'Admin\LocationsController');
	Route::resource('/application', 'Admin\ApplicationsController');
	Route::resource('/calltoaction', 'Admin\CallToActionController');
	Route::resource('/jobs', 'Admin\JobVacancyController');
	Route::resource('/job-apply-list', 'Admin\JobAppliedController');

	Route::get('/autocomplete', 'Admin\JobVacancyController@autocomplete');
	
});
